<?php

namespace App\Http\Controllers;

use App\Models\Global_Permission;
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

            /* Retrieve all team IDs where the user is a member */
            $teamIds = Team_Member::where('user_id', $user->id)->pluck('team_id');

            /* Loop through team IDs to find the first matching team */
            foreach ($teamIds as $id) {
                $team = Team::find($id);

                /* Break the loop as soon as a team with a different slug is found */
                if ($team && session('team_slug') != $team->slug) {
                    break;
                }
            }
        }

        //TODO: Handle the case when no team is found (e.g., user is not assigned to any team).
        // You could redirect the user to a "No Team" page, or show an error message if $team is still null.

        /* Redirect to the dashboard with the team slug */
        $redirect = redirect()->route('dashboard', ['slug' => $team->slug]);

        /* Include errors if present in the session */
        if (session()->has('errors')) {
            $redirect->withErrors(['error' => session('errors')->first()]);
        }

        return $redirect;
    }

    /**
     * Display the user's dashboard.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function dashboard($slug)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Prepare data for the view */
            $data = [
                'title' => 'Dashboard - Networked',
                'team' => $team,
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
            return redirect()->route('loginPage')->withErrors(['error' => 'Something went wrong']);
        }
    }
}