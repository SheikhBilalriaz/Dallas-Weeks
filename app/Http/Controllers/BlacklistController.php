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
     * @param String $slug
     * @return \Illuminate\View\View
     */
    public function blacklist($slug)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Retrieve blacklists */
            $global_blacklist = Global_Blacklist::where('team_id', $team->id)->get();
            $email_blacklist = Email_Blacklist::where('team_id', $team->id)->get();

            /* Prepare data for the view */
            $data = [
                'title' => 'Blacklist - Networked',
                'team' => $team,
                'global_blacklist' => $global_blacklist,
                'email_blacklist' => $email_blacklist,
            ];

            /* Return the view with the prepared data */
            return view('dashboard.blacklist', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Save the global blacklist items for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function saveGlobalBlacklist($slug, Request $request)
    {
        try {
            /* Retrieve the currently authenticated user */
            $creator = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Validate request data */
            $validator = Validator::make($request->all(), [
                'global_blacklist_item' => 'required|array|min:1',
                'global_blacklist_item.*' => 'string|max:255',
                'global_blacklist_type' => 'required|string',
                'global_comparison_type' => 'required|string',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->with('global_blacklist_error', true)
                    ->withInput();
            }

            /* Additional validation for 'profile_url' type */
            if ($request->input('global_blacklist_type') == 'profile_url') {
                if ($request->input('global_comparison_type') !== 'exact') {
                    return back()->withErrors([
                        'global_comparison_type' => 'If Profile Urls is selected so comparison type must be exact',
                    ])
                        ->with('global_blacklist_error', true)
                        ->withInput();
                }

                foreach ($request->input('global_blacklist_item') as $item) {
                    if (strpos($item, 'https://www.linkedin.com/in/') === false) {
                        return back()->withErrors([
                            'global_blacklist_item' => 'Profile URLs must contain "https://www.linkedin.com/in/"'
                        ])
                            ->with('global_blacklist_error', true)
                            ->withInput();
                    }
                }
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
            return redirect()->route('globalBlacklistPage', ['slug' => $slug])
                ->with('success', 'Global blacklist items saved successfully.');
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Save the email blacklist items for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveEmailBlacklist($slug, Request $request)
    {
        try {
            /* Retrieve the currently authenticated user */
            $creator = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Validate request data */
            $validator = Validator::make($request->all(), [
                'email_blacklist_item' => 'required|array|min:1',
                'email_blacklist_item.*' => 'string|max:255',
                'email_blacklist_type' => 'required|string',
                'email_comparison_type' => 'required|string',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->with('email_blacklist_error', true)
                    ->withInput();
            }

            /* Save each blacklist item to the Email_Blacklist table */
            foreach ($request->input('email_blacklist_item') as $item) {
                Email_Blacklist::create([
                    'creator_id' => $creator->id,
                    'team_id' => $team->id,
                    'keyword' => $item,
                    'blacklist_type' => $request->input('email_blacklist_type'),
                    'comparison_type' => $request->input('email_comparison_type'),
                ]);
            }

            /* Redirect back to the global blacklist page */
            return redirect()->route('globalBlacklistPage', ['slug' => $slug])
                ->with('success', 'Email blacklist items saved successfully.');
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Delete the global blacklist item for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  Int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteGlobalBlacklist($slug, $id)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Find the blacklist item by ID */
            $blacklistItem = Global_Blacklist::find($id);

            /* Check if the blacklist item exists and belongs to the team */
            if (!empty($blacklistItem) && $blacklistItem->team_id == $team->id) {
                /* Delete the blacklist item */
                $blacklistItem->delete();

                /* Return a success response */
                return response()->json(['success' => true, 'message' => 'Blacklist item deleted successfully.']);
            }

            /* Return a 404 Not Found response if the item does not exist */
            return response()->json(['error' => 'Blacklist item not found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Delete the email blacklist item for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  Int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEmailBlacklist($slug, $id)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Find the blacklist item by ID */
            $blacklistItem = Email_Blacklist::find($id);

            /* Check if the blacklist item exists and belongs to the team */
            if (!empty($blacklistItem) && $blacklistItem->team_id == $team->id) {
                /* Delete the blacklist item */
                $blacklistItem->delete();

                /* Return a success response */
                return response()->json(['success' => true, 'message' => 'Blacklist item deleted successfully.']);
            }

            /* Return a 404 Not Found response if the item does not exist */
            return response()->json(['error' => 'Blacklist item not found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Search the global blacklist items for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  String  $search
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchGlobalBlacklist($slug, $search)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Retrieve global blacklists */
            if ($search == 'null') {
                $global_blacklist = Global_Blacklist::where('team_id', $team->id)->get();
            } else {
                $global_blacklist = Global_Blacklist::where('team_id', $team->id)->where('keyword', 'like', '%' . $search . '%')->get();
            }

            /* Check if any blacklist items were found */
            if ($global_blacklist->isNotEmpty()) {
                /* Json response with global blacklist */
                return response()->json([
                    'success' => true,
                    'global_blacklist' => $global_blacklist,
                ]);
            }

            /* Global Blacklist not found */
            return response()->json(['success' => false, 'message' => 'No blacklist items found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Search the email blacklist items for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  String  $search
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchEmailBlacklist($slug, $search)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Retrieve email blacklists */
            if ($search == 'null') {
                $email_blacklist = Email_Blacklist::where('team_id', $team->id)->get();
            } else {
                $email_blacklist = Email_Blacklist::where('team_id', $team->id)->where('keyword', 'like', '%' . $search . '%')->get();
            }

            /* Check if any blacklist items were found */
            if ($email_blacklist->isNotEmpty()) {
                /* Json response with email blacklist */
                return response()->json([
                    'success' => true,
                    'email_blacklist' => $email_blacklist,
                ]);
            }

            /* Email Blacklist not found */
            return response()->json(['success' => false, 'message' => 'No blacklist items found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Filter the global blacklist items for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterGlobalBlacklist($slug, Request $request)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Initialize the query */
            $global_blacklist = Global_Blacklist::where('team_id', $team->id);

            /* Apply filters if present */
            if ($request->filled('filter_global_blacklist_type')) {
                $global_blacklist->whereIn('blacklist_type', $request->input('filter_global_blacklist_type'));
            }
            if ($request->filled('filter_global_comparison_type')) {
                $global_blacklist->whereIn('comparison_type', $request->input('filter_global_comparison_type'));
            }

            /* Execute the query */
            $global_blacklist = $global_blacklist->get();

            /* Return response */
            if ($global_blacklist->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'global_blacklist' => $global_blacklist,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'No blacklist items found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Filter the email blacklist items for the authenticated user and team.
     *
     * @param  String  $slug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterEmailBlacklist($slug, Request $request)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Initialize the query */
            $email_blacklist = Email_Blacklist::where('team_id', $team->id);

            /* Apply filters if present */
            if ($request->filled('filter_email_blacklist_type')) {
                $email_blacklist->whereIn('blacklist_type', $request->input('filter_email_blacklist_type'));
            }
            if ($request->filled('filter_email_comparison_type')) {
                $email_blacklist->whereIn('comparison_type', $request->input('filter_email_comparison_type'));
            }

            /* Execute the query */
            $email_blacklist = $email_blacklist->get();

            /* Return response */
            if ($email_blacklist->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'email_blacklist' => $email_blacklist,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'No blacklist items found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
