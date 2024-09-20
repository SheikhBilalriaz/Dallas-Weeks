<?php

namespace App\Http\Controllers;

use App\Models\Email_Blacklist;
use App\Models\Global_Blacklist;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BlacklistController extends Controller
{
    /**
     * Display the blacklist for the authenticated user.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function blacklist($slug)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Check if the user has permission to manage the global blacklist. */
            if (!session()->has('is_manage_global_blacklist') || !session('is_manage_global_blacklist')) {
                /* Redirect if the user does not have permission to manage the global blacklist. */
                return redirect()->route('dashboardPage')->withErrors(['error' => "You don't have access to blacklist"]);
            }

            $global_blacklist = Global_Blacklist::where('team_id', $team->id)->get();

            $email_blaklist = Email_Blacklist::where('team_id', $team->id)->get();

            /* Prepare data for the view */
            $data = [
                'title' => 'Blacklist - Networked',
                'team' => $team,
                'global_blacklist' => $global_blacklist,
                'email_blaklist' => $email_blaklist,
            ];

            /* Return the view with the prepared data */
            return view('dashboard.blacklist', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::info($e);

            /* Redirect to login with an error message */
            return redirect()->route('loginPage')->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Save the global blacklist items for the authenticated user and team.
     *
     * @param  string  $slug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function saveGlobalBlacklist($slug, Request $request)
    {
        try {
            /* Retrieve the currently authenticated user */
            $creator = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Check if the user has permission to manage the global blacklist. */
            if (!session()->has('is_manage_global_blacklist') || !session('is_manage_global_blacklist')) {
                /* Redirect if the user does not have permission to manage the global blacklist. */
                return redirect()->route('dashboardPage')->withErrors(['error' => "You don't have access to blacklist"]);
            }

            /* Validate request data */
            $validator = Validator::make($request->all(), [
                'global_blacklist_item' => 'required|array|min:1',
                'global_blacklist_item.*' => 'string|max:255',
                'global_blacklist_type' => 'required|string',
                'global_comparison_type' => 'required|string',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)->with('global_blacklist_error', true)->withInput();
            }

            /* Save each blacklist item to the Global_Blacklist table */
            foreach ($request->input('global_blacklist_item') as $item) {
                Global_Blacklist::create([
                    'creator_id' => $creator->id,
                    'team_id' => $team->id,
                    'keyword' => $item,
                    'blacklist_type' => $request->input('global_blacklist_type'),
                    'comparison_type' => $request->input('global_comparison_type'),
                ]);
            }

            /* Redirect back to the global blacklist page */
            return redirect()->route('globalBlacklist', ['slug' => $slug]);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::info($e);

            /* Redirect to login with an error message */
            return redirect()->route('loginPage')->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function saveEmailBlacklist($slug, Request $request)
    {
        try {
            /* Retrieve the currently authenticated user */
            $creator = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Check if the user has permission to manage the global blacklist. */
            if (!session()->has('is_manage_global_blacklist') || !session('is_manage_global_blacklist')) {
                /* Redirect if the user does not have permission to manage the global blacklist. */
                return redirect()->route('dashboardPage')->withErrors(['error' => "You don't have access to blacklist"]);
            }

            /* Validate request data */
            $validator = Validator::make($request->all(), [
                'blacklist_item' => 'required|array|min:1',
                'blacklist_item.*' => 'string|max:255',
                'blacklist_type' => 'required|string',
                'comparison_type' => 'required|string',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            /* Save each blacklist item to the Global_Blacklist table */
            foreach ($request->input('blacklist_item') as $item) {
                Global_Blacklist::create([
                    'creator_id' => $creator->id,
                    'team_id' => $team->id,
                    'keyword' => $item,
                    'blacklist_type' => $request->input('blacklist_type'),
                    'comparison_type' => $request->input('comparison_type'),
                ]);
            }

            /* Redirect back to the global blacklist page */
            return redirect()->route('globalBlacklist', ['slug' => $slug]);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::info($e);

            /* Redirect to login with an error message */
            return redirect()->route('loginPage')->withErrors(['error' => $e->getMessage()]);
        }
    }
}
