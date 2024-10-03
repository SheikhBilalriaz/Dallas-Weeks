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
use App\Models\Permission;
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
     * @param string $slug The team slug.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse The view with team and user data or a redirect on error.
     */
    public function team($slug)
    {
        try {
            /* Get the authenticated user. */
            $user = Auth::user();

            /* Fetch the team using its slug */
            $team = Team::where('slug', $slug)->first();

            $creator = User::find($team->creator_id);

            /* Start building the query for fetching seats associated with the team. */
            $query = Seat::where('team_id', $team->id);
            if ($user->id != $team->creator_id) {
                $member = Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->first();
                $assigned_seats = Assigned_Seat::where('member_id', $member->id)->get();
                $query->whereIn('id', $assigned_seats->pluck('seat_id')->toArray());
            }

            /* Fetch all the seats based on the query, eager loading to reduce additional queries. */
            $seats = $query->get();

            /* Retrieve the roles associated with the team or default */
            $roles = Role::whereIn('team_id', [0, $team->id])->get();

            /* Fetch all team members */
            $members = Team_Member::where('team_id', $team->id)->get();

            /* Fetch all available permissions */
            $permissions = Permission::all();

            /* Prepare the data for rendering in the view. */
            $data = [
                'title' => 'Team Dashboard',
                'team' => $team,
                'seats' => $seats,
                'roles' => $roles,
                'members' => $members,
                'permissions' => $permissions,
                'creator' => $creator,
            ];

            /* Render the team dashboard view with the prepared data. */
            return view('dashboard.team', $data);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Return a JSON response with the error message and a 404 status code */
            return redirect()->route('dashboardPage', ['slug' => $slug])->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Search the team members for the authenticated user and team based on search input.
     *
     * @param  String  $slug   The unique identifier for the team (team slug).
     * @param  String  $search The search keyword to filter team members by name or email.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response with the team members and creator.
     */
    public function searchTeamMember($slug, $search)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();

            /* Find the team by its slug */
            $team = Team::where('slug', $slug)->first();

            /* Query the creator of the team by their user ID */
            $query = User::where('id', $team->creator_id);

            /* If a search term is provided, apply a filter to search by name or email */
            if ($search != 'null') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'Like', '%' . $search . '%')
                        ->orWhere('email', 'Like', '%' . $search . '%');
                });
            }

            /* Fetch the creator's details after applying the search filter (if any) */
            $creator = $query->first();

            /* Get all members associated with the team */
            $members = Team_Member::where('team_id', $team->id)->get();

            /* Initialize an empty array to store filtered members */
            $filtered_members = [];

            /* Iterate through each team member */
            foreach ($members as $member) {
                /* Query to fetch the user details of the member */
                $query = User::where('id', $member->user_id);

                /* Apply search filter if a search term is provided */
                if ($search != 'null') {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
                }

                /* Fetch the member details */
                $member->member_deatil = $query->first();

                /* If member details are not found, skip this iteration */
                if (!$member->member_deatil) {
                    continue;
                }

                /* Fetch the assigned seats for the current member */
                $assigned_seats = Assigned_Seat::where('member_id', $member->id)->get();

                /* Fetch the roles based on the role IDs associated with the assigned seats */
                $member_role = Role::whereIn('id', $assigned_seats->pluck('role_id')->toArray())->get();
                $member_roles = $member_role->pluck('name')->toArray();

                /* Convert the roles to a comma-separated string or set 'No Role Assigned' if empty */
                $member->member_role = implode(', ', $member_roles) ?: 'No Role Assigned';

                /* Fetch the seats based on the seat IDs associated with the assigned seats */
                $member_seat = Seat::whereIn('id', $assigned_seats->pluck('seat_id')->toArray())->get();

                /* Fetch the company information based on the seat's company info IDs */
                $member_seats = Company_Info::whereIn('id', $member_seat->pluck('company_info_id')->toArray())->get();
                $member_seats = $member_seats->pluck('name')->toArray();

                /* Convert the seats to a comma-separated string or set 'No Seat Assigned' if empty */
                $member->member_seat = implode(', ', $member_seats) ?: 'No Seat Assigned';

                /* Add the member to the filtered members array */
                $filtered_members[] = $member;
            }

            /* If there are any filtered members, return them in the response */
            if (!empty($filtered_members) || !empty($creator)) {
                return response()->json([
                    'success' => true,
                    'team_member' => $filtered_members,
                    'creator' => $creator,
                ]);
            }

            /* Return a response with a failure message if no members are found */
            return response()->json(['success' => false, 'message' => 'No team member found.'], 404);
        } catch (Exception $e) {
            /* Log the error for debugging purposes */
            Log::error($e);

            /* Redirect to the dashboard page with an error message in case of an exception */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong while searching the team.']);
        }
    }

    /**
     * Delete the specified team member for the authenticated user and team.
     *
     * @param  String  $slug  The unique identifier for the team (team slug).
     * @param  Int     $id    The ID of the team member to be deleted.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response indicating success or failure.
     */
    public function deleteTeamMember($slug, $id)
    {
        try {
            /* Find the team by its slug */
            $team = Team::where('slug', $slug)->first();

            /* Find the team member by their ID and ensure they belong to the specified team */
            $member = Team_Member::where('id', $id)->where('team_id', $team->id)->first();

            /* If the team member exists, proceed with deletion */
            if ($member) {
                /* Find the user associated with the team member */
                $user = User::find($member->user_id);

                /* Retrieve all assigned seats related to the team member */
                $assigned_seats = Assigned_Seat::where('member_id', $member->id)->get();

                /* Retrieve global permissions for the user within the specified team */
                $global_permissions = Global_Permission::where('user_id', $user->id)->where('team_id', $team->id)->get();

                /* Delete the global permissions */
                $global_permissions->each->delete();

                /* Delete the assigned seats for the team member */
                $assigned_seats->each->delete();

                /* Finally, delete the team member */
                $member->delete();

                /* Return a success response indicating the team member was deleted */
                return response()->json(['success' => true, 'message' => 'Blacklist item deleted successfully.']);
            }

            /* If the team member was not found, return a 404 response */
            return response()->json(['error' => 'Team member not found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong while deleting the team member.'], 500);
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
        /* Start a database transaction */
        DB::beginTransaction();

        try {
            /* Find the team by slug */
            $team = Team::where('slug', $slug)->first();

            /* Validate request data */
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:191',
                'invite_email' => 'required|email',
                'roles' => 'required|array|min:1',
                'roles.*' => 'string|max:255',
                'seats' => 'required|array|min:1',
                'seats.*' => 'required|array|min:1',
                'seats.*.*' => 'string|max:255',
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

            /* Validate that at least one seat is assigned for each role */
            foreach ($request->input('roles') as $role) {
                if (!isset($request->input('seats')[$role]) || count($request->input('seats')[$role]) <= 0) {
                    return back()->withErrors([
                        'roles' => `No Seat Assigned to '{$role}'.`,
                    ])->with('invite_error', true)->withInput();
                }
            }

            /* Check if roles and seats exist in the database */
            foreach ($request->input('roles') as $role) {
                $role_id = str_replace('role_', '', $role);

                /* Validate the existence of the role */
                if (!Role::where('id', $role_id)->exists()) {
                    return back()->withErrors([
                        'roles' => `Role '{$role}' not found`,
                    ])->with('invite_error', true)->withInput();
                }

                /* Validate the existence of assigned seats for each role */
                foreach ($request->input('seats')[$role] as $seat) {
                    if (!Seat::where('id', $seat)->exists()) {
                        return back()->withErrors([
                            'roles' => `Seat '{$seat}' not found for role '{$role}'`,
                        ])->with('invite_error', true)->withInput();
                    }
                }
            }

            /* Check if the user already exists or create a new user */
            $user = User::where('email', $request->input('invite_email'))->first();
            if (!$user) {
                /* Generate a random password for the new user */
                $randomPassword = Str::random(37);
                $temp_user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('invite_email'),
                    'password' => Hash::make($randomPassword),
                    'remember_token' => Str::random(100),
                ]);

                /* Send welcome email to the new user */
                Mail::to($temp_user->email)->send(new WelcomeMail($temp_user, $randomPassword));
                $user = $temp_user;
            }

            /* Check if the user is already a team member */
            if (Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->exists()) {
                return back()->withErrors([
                    'invite_email' => 'Team Member already existed',
                ])->with('invite_error', true)->withInput();
            }
            $team_member = Team_Member::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
            ]);

            /* Assign roles and seats to the team member */
            foreach ($request->input('roles') as $role) {
                $role_id = str_replace('role_', '', $role);
                foreach ($request->input('seats')[$role] as $seat) {
                    $assigned_seats[] = Assigned_Seat::create([
                        'member_id' => $team_member->id,
                        'role_id' => $role_id,
                        'seat_id' => $seat,
                    ]);
                }
            }

            /* Handle global permissions based on the request input */
            if ($request->input('manage_payment_system')) {
                Global_Permission::create([
                    'name' => 'Manage payment system',
                    'slug' => 'manage_payment_system',
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'access' => 1,
                ]);
            }
            if ($request->input('manage_global_blacklist')) {
                Global_Permission::create([
                    'name' => 'Manage global blacklist',
                    'slug' => 'manage_global_blacklist',
                    'user_id' => $user->id,
                    'team_id' => $request->input('team_id'),
                    'access' => 1,
                ]);
            }

            /* Commit the transaction */
            DB::commit();

            /* Redirect to the team page with a success message */
            return redirect()->route('teamPage')->with(['success' => 'Add Team Member Successfully']);
        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();

            // Log the exception message
            Log::error($e);

            // Redirect back with an error message
            return back()->withErrors(['error' => 'An error occurred while inviting the team member. Please try again.'])
                ->with('invite_error', true)
                ->withInput();
        }
    }
}
