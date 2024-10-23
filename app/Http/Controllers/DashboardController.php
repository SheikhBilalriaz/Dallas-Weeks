<?php

namespace App\Http\Controllers;

use App\Models\Assigned_Seat;
use App\Models\Global_Permission;
use App\Models\Seat;
use App\Models\Team;
use App\Models\Team_Member;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Redirect the authenticated user to their team dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toDashboard()
    {
        /* Retrieve the currently authenticated user */
        $user = Auth::user();

        /* Retrieve the team where the user is the creator */
        $team = Team::where('creator_id', $user->id)->first();

        /* Check if the session has a team_slug, and if it matches the creator's team slug */
        if (!$team || (session()->has('team_slug') && session('team_slug') == $team->slug)) {
            /* Retrieve the user's teams as a member */
            $teamMemberIds = Team_Member::where('user_id', $user->id)->pluck('team_id');
            $team = Team::whereIn('id', $teamMemberIds)->where('slug', '!=', session('team_slug'))->first();
        }

        /* You could redirect the user to a "No Team" page, or show an error message if $team is still null. */
        if (!$team) {
            return redirect()->route('noTeamPage')->withErrors(['error' => 'No team found for the user.']);
        }

        /* Redirect to the dashboard with the team slug */
        $redirect = redirect()->route('dashboardPage', ['slug' => $team->slug]);

        /* Include errors if present in the session */
        if (session()->has('error')) {
            $redirect->withErrors(['error' => session('errors')->first()]);
        }

        return $redirect;
    }

    /**
     * Display the user's dashboard.
     *
     * @param string $slug The slug of the team.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function dashboard($slug)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Check if the user is the team creator or a member */
            if ($user->id == $team->creator_id) {
                /* If the user is the team creator, retrieve all seats for the team */
                $seats = Seat::where('team_id', $team->id)->get();
            } else {
                /* Retrieve the member and assigned seats for the user */
                $assignedSeatIds = Assigned_Seat::whereIn('member_id', Team_Member::where('user_id', $user->id)
                    ->where('team_id', $team->id)
                    ->pluck('id'))
                    ->pluck('seat_id');
                $seats = Seat::whereIn('id', $assignedSeatIds)->get();
            }

            $team_members = Team_Member::where('user_id', $user->id)->get();
            $teams = Team::whereIn('id', $team_members->pluck('team_id')->toArray())->get();

            /* Prepare data for the view */
            $data = [
                'title' => 'Dashboard - Networked',
                'team' => $team,
                'seats' => $seats,
                'teams' => $teams,
                'error' => session()->has('errors') ? session('errors')->first() : null,
            ];

            /* Return the view with the prepared data */
            return view('dashboard.main', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to login with error message */
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Something went wrong'])
                ->with('team_slug', $slug);
        }
    }
}
