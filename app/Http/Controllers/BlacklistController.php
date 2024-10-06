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
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BlacklistController extends Controller
{
    /**
     * Display the blacklist for the authenticated user's team.
     *
     * @param string $slug  The slug of the team.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function blacklist($slug)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::with(['globalBlacklists', 'emailBlacklists'])->where('slug', $slug)->first();

            /* Prepare data for the view */
            $data = [
                'title' => 'Blacklist - Networked',
                'team' => $team,
                'global_blacklists' => $team->globalBlacklists,
                'email_blacklists' => $team->emailBlacklists,
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
     * @return \Illuminate\Http\RedirectResponse
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

            /* Additional validation for 'profile_url' blacklist type */
            $validator->after(function ($validator) use ($request) {
                if ($request->input('global_blacklist_type') === 'profile_url') {
                    if ($request->input('global_comparison_type') !== 'exact') {
                        $validator->errors()->add('global_comparison_type', 'If Profile URLs is selected, the comparison type must be exact.');
                    }

                    /* Validate that each profile URL contains 'https://www.linkedin.com/in/' */
                    foreach ($request->input('global_blacklist_item') as $item) {
                        if (strpos($item, 'https://www.linkedin.com/in/') === false) {
                            $validator->errors()->add('global_blacklist_item', 'Profile URLs must contain "https://www.linkedin.com/in/".');
                        }
                    }
                }
            });

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->with('global_blacklist_error', true)
                    ->withInput();
            }

            /* Retrieve the validated data */
            $globalBlacklistType = $request->input('global_blacklist_type');
            $globalComparisonType = $request->input('global_comparison_type');
            $blacklistItems = $request->input('global_blacklist_item');

            /* Begin a database transaction to ensure all-or-nothing operations. */
            DB::beginTransaction();

            /* Prepare the batch insert data */
            $insertData = [];
            foreach ($blacklistItems as $item) {
                $insertData[] = [
                    'creator_id' => $creator->id,
                    'team_id' => $team->id,
                    'keyword' => $item,
                    'blacklist_type' => $globalBlacklistType,
                    'comparison_type' => $globalComparisonType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            /* Perform a batch insert */
            DB::table('global_blacklist')->insert($insertData);

            /* Commit the transaction if all inserts are successful. */
            DB::commit();

            /* Redirect back to the global blacklist page with a success message */
            return redirect()->route('globalBlacklistPage', ['slug' => $slug])
                ->with('success', 'Global blacklist items saved successfully.');
        } catch (Exception $e) {
            /* Rollback the transaction if an error occurs. */
            DB::rollBack();

            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to the global blacklist page with a generic error message */
            return redirect()->route('globalBlacklistPage', ['slug' => $slug])
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

            /* Retrieve input values */
            $emailBlacklistType = $request->input('email_blacklist_type');
            $emailComparisonType = $request->input('email_comparison_type');
            $blacklistItems = $request->input('email_blacklist_item');

            /* Begin a database transaction to ensure all-or-nothing operations. */
            DB::beginTransaction();

            /* Prepare the batch insert data */
            $insertData = [];
            foreach ($blacklistItems as $item) {
                $insertData[] = [
                    'creator_id' => $creator->id,
                    'team_id' => $team->id,
                    'keyword' => $item,
                    'blacklist_type' => $emailBlacklistType,
                    'comparison_type' => $emailComparisonType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            /* Perform a batch insert */
            DB::table('email_blacklist')->insert($insertData);

            /* Commit the transaction if all inserts are successful. */
            DB::commit();

            /* Redirect back to the global blacklist page */
            return redirect()->route('globalBlacklistPage', ['slug' => $slug])
                ->with('success', 'Email blacklist items saved successfully.');
        } catch (Exception $e) {
            /* Rollback the transaction if an error occurs. */
            DB::rollBack();

            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to dashboard with an error message */
            return redirect()->route('globalBlacklistPage', ['slug' => $slug])
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

            /* Begin a database transaction */
            DB::beginTransaction();

            /* Find the blacklist item by ID */
            $blacklistItem = Global_Blacklist::where('id', $id)->where('team_id', $team->id)->firstOrFail();

            /* Delete the blacklist item */
            $blacklistItem->delete();

            /* Commit the transaction */
            DB::commit();

            /* Return a success response */
            return response()->json(['success' => true, 'message' => 'Blacklist item deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            /* Return a 404 Not Found response if the item does not exist */
            return response()->json(['error' => 'Blacklist item not found.'], 404);
        } catch (Exception $e) {
            /* Rollback the transaction if something went wrong */
            DB::rollBack();

            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
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

            /* Begin a database transaction */
            DB::beginTransaction();

            /* Find the blacklist item by ID */
            $blacklistItem = Email_Blacklist::where('id', $id)->where('team_id', $team->id)->firstOrFail();

            /* Delete the blacklist item */
            $blacklistItem->delete();

            /* Commit the transaction */
            DB::commit();

            /* Return a success response */
            return response()->json(['success' => true, 'message' => 'Blacklist item deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            /* Rollback the transaction if something went wrong */
            DB::rollBack();

            /* Return a 404 Not Found response if the item does not exist */
            return response()->json(['error' => 'Blacklist item not found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
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

            /* Build the query to retrieve global blacklists */
            $query = Global_Blacklist::where('team_id', $team->id);

            /* Check if search is not empty */
            if (!empty($search) && $search !== 'null') {
                $query->where('keyword', 'like', '%' . $search . '%');
            }

            $global_blacklist = $query->get();

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

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
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

            /* Build the query to retrieve email blacklists */
            $query = Email_Blacklist::where('team_id', $team->id);

            if (!empty($search) && $search !== 'null') {
                $query->where('keyword', 'like', '%' . $search . '%');
            }

            $email_blacklist = $query->get();

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

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
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
            $query = Global_Blacklist::where('team_id', $team->id);

            /* Apply filters if present */
            if ($request->filled('filter_global_blacklist_type')) {
                $query->whereIn('blacklist_type', $request->input('filter_global_blacklist_type'));
            }
            if ($request->filled('filter_global_comparison_type')) {
                $query->whereIn('comparison_type', $request->input('filter_global_comparison_type'));
            }

            /* Execute the query */
            $global_blacklist = $query->get();

            /* Return response */
            if ($global_blacklist->isNotEmpty()) {
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

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
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
            $query = Email_Blacklist::where('team_id', $team->id);

            /* Apply filters if present */
            if ($request->filled('filter_email_blacklist_type')) {
                $query->whereIn('blacklist_type', $request->input('filter_email_blacklist_type'));
            }
            if ($request->filled('filter_email_comparison_type')) {
                $query->whereIn('comparison_type', $request->input('filter_email_comparison_type'));
            }

            /* Execute the query */
            $email_blacklist = $query->get();

            /* Return response */
            if ($email_blacklist->isNotEmpty()) {
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

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }
}
