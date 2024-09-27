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

        /* If the user is not a creator or the session team slug matches the team slug */
        if (!$team || ($team && session()->has('team_slug') && session('team_slug') == $team->slug)) {

            /* Retrieve all teams where the user is a member */
            $teams = Team::whereIn('id', Team_Member::where('user_id', $user->id)->pluck('team_id'))->get();

            /* Find the first team where the session team slug doesn't match */
            foreach ($teams as $team) {
                /* Break the loop as soon as a team with a different slug is found */
                if (session('team_slug') != $team->slug) {
                    break;
                }
            }
        }

        /* You could redirect the user to a "No Team" page, or show an error message if $team is still null. */
        if (!$team) {
            return redirect()->route('noTeamPage')->withErrors(['error' => 'No team found for the user.']);
        }

        /* Redirect to the dashboard with the team slug */
        $redirect = redirect()->route('dashboardPage', ['slug' => $team->slug]);

        /* Include errors if present in the session */
        if (session()->has('errors')) {
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
            $user = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            $members = Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->get();

            $assigned_seats = Assigned_Seat::whereIn('member_id', $members->pluck('id')->toArray())->get();

            $seats = $user->id == $team->creator_id
                ? Seat::where('team_id', $team->id)->get()
                : Seat::whereIn('id', $assigned_seats->pluck('seat_id')->toArray())->get();

            /* Prepare data for the view */
            $data = [
                'title' => 'Dashboard - Networked',
                'team' => $team,
                'seat' => $seats,
            ];

            /* Include errors if present in the session */
            if (session()->has('errors')) {
                $data['error'] = session('errors')->first();
            }

            /* Return the view with the prepared data */
            return view('dashboard.dashboard_account', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to login with error message */
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.'])
                ->with('team_slug', $slug);
        }
    }
}
