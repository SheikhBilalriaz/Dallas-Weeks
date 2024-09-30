<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Team_Member;
use App\Models\User;
use App\Models\Assigned_Seat;
use App\Models\Company_Info;
use App\Models\Global_Permission;
use App\Models\Role;
use App\Models\Seat;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * Retrieve and prepare team members with their associated roles.
     *
     * @return \Illuminate\View\View The view with team and user data.
     */
    public function team($slug)
    {
        try {
            $user = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            $seats = Seat::where('team_id', $team->id)->get();

            foreach ($seats as $seat) {
                $seat->company_info = Company_Info::find($seat->company_info_id);
            }

            $roles = Role::whereIn('team_id', [0, $team->id])->get();

            /* Prepare data for the view */
            $data = [
                'title' => 'Team Dashboard',
                'team' => $team,
                'seats' => $seats,
                'roles' => $roles,
            ];

            /* Return the view with the prepared data */
            return view('dashboard.team', $data);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Return a JSON response with the error message and a 404 status code */
            return redirect()->route('dashboardPage')->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Search the search team members for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  String  $search
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchTeamMember($slug, $search)
    {
        try {
            /* Find the team by slug */
            $team = Team::where('slug', $slug)->first();

            /* Get team members */
            $team_member = Team_Member::where('team_id', $team->id)->get();

            /* Apply search filter if provided */
            if ($search == 'null') {
                $members = User::whereIn('id', $team_member->pluck('user_id')->toArray())->get();
            } else {
                $members = User::whereIn('id', $team_member->pluck('user_id')->toArray())
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')->get();
            }

            /* Check if any team member items were found */
            if ($members->isNotEmpty()) {
                /* Json response with team member */
                return response()->json([
                    'success' => true,
                    'team_member' => $members,
                ]);
            }

            /* Team Member not found */
            return response()->json(['success' => false, 'message' => 'No team member found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Add a team member based on the request data.
     *
     * @param string $slug The team's unique identifier.
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing form data.
     * @return \Illuminate\Http\RedirectResponse The response redirecting to the team page.
     */
    public function inviteTeamMember($slug, Request $request)
    {
        /* Find the team by slug */
        $team = Team::where('slug', $slug)->first();

        /* Validate request data */
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'invite_email' => 'required|email',
            'role' => 'required|string',
            'seats' => 'required|array|min:1',
            'seats.*' => 'string|max:255',
        ]);

        /* Return validation errors if validation fails */
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->with('invite_error', true)
                ->withInput();
        }

        /* Check if the invitee is the current user */
        if ($request->input('invite_email') == auth()->user()->email) {
            return back()->withErrors([
                'invite_email' => 'You cannot assign roles to yourself',
            ])->with('invite_error', true)->withInput();
        }

        /* Remove 'role_' prefix from the role input */
        $role = str_replace('role_', '', $request->input('role'));

        /* Check if user exists by email */
        $user = User::where('email', $request->input('invite_email'))->first();
        $temp_user = null;

        /* Begin a database transaction */
        DB::beginTransaction();

        try {
            /* Create a new user if they do not exist */
            if (!$user) {
                $randomPassword = Str::random(37);
                $temp_user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('invite_email'),
                    'password' => Hash::make($randomPassword),
                    'remember_token' => Str::random(100),
                ]);

                /* Send welcome email with password */
                Mail::to($temp_user->email)->send(new WelcomeMail($temp_user, $randomPassword));
            }
        } catch (\Exception $e) {
            if ($temp_user->id) {
                $temp_user->delete();
            }
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to team with an error message */
            return redirect()->route('teamPage', ['slug' => $slug])->with('invite_error', true)
                ->withErrors(['error' => 'Something went wrong while inviting a team member.']);
        }

        if ($temp_user && $temp_user->id) {
            $user = $temp_user;
        }

        try {
            /* Create a team member record */
            $team_member = Team_Member::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
            ]);

            /* Assign seats to the team member */
            foreach ($request->input('seats') as $seat) {
                $assigned_seats[] = Assigned_Seat::create([
                    'member_id' => $team_member->id,
                    'role_id' => $role,
                    'seat_id' => $seat,
                ]);
            }

            /* Assign additional global permissions if requested */
            $manage_payment_system = null;
            if ($request->input('manage_payment_system')) {
                $manage_payment_system = Global_Permission::create([
                    'name' => 'Manage payment system',
                    'slug' => 'manage_payment_system',
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'access' => 1,
                ]);
            }
            $manage_global_blacklist = null;
            if ($request->input('manage_global_blacklist')) {
                $manage_global_blacklist = Global_Permission::create([
                    'name' => 'Manage global blacklist',
                    'slug' => 'manage_global_blacklist',
                    'user_id' => $user->id,
                    'team_id' => $request->input('team_id'),
                    'access' => 1,
                ]);
            }
            /* Commit the transaction as all operations succeeded */
            DB::commit();
            return redirect()->route('teamPage')->with(['success' => 'Add Team Member Successfully']);
        } catch (\Exception $e) {
            if ($manage_global_blacklist->id) {
                $manage_global_blacklist->delete();
            }
            if ($manage_payment_system->id) {
                $manage_payment_system->delete();
            }
            foreach ($assigned_seats as $seat) {
                if ($seat->id) {
                    $seat->delete();
                }
            }
            if ($team_member->id) {
                $team_member->delete();
            }

            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to team with an error message */
            return redirect()->route('teamPage', ['slug' => $slug])->with('invite_error', true)
                ->withErrors(['error' => 'Something went wrong while inviting a team member.']);
        }
    }
}
