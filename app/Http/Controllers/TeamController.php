<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Team_Member;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

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
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            $members = Team_Member::where('team_id', $team->id)->get();

            /* Prepare data for the view */
            $data = [
                'title' => 'Team Dashboard',
                'team' => $team,
                'members' => $members,
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
}
