<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Team_Member;
use Exception;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    /**
     * Retrieve and prepare team members with their associated roles.
     *
     * @return \Illuminate\View\View The view with team and user data.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If any required model is not found.
     * @throws \Exception For general errors during execution.
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
}
