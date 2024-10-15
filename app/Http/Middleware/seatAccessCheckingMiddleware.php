<?php

namespace App\Http\Middleware;

use App\Http\Controllers\UnipileController;
use App\Models\Seat;
use App\Models\Team_Member;
use App\Models\Assigned_Seat;
use App\Models\Linkedin_Integration;
use App\Models\Permission;
use App\Models\Role_Permission;
use Exception;
use Closure;
use Illuminate\Http\Request;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class seatAccessCheckingMiddleware
{
    /**
     * Handle an incoming request to ensure the user is have access to seat.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request instance.
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next  The next middleware or request handler.
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse  The response returned by the next middleware or redirect if unauthenticated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            /* Retrieve the currently authenticated user */
            $user = Auth::user();

            /* Retrieve the slug and seat_slug from the request */
            $slug = $request->route('slug');
            $seat_slug = $request->route('seat_slug');

            /* Find the seat and team by their slugs */
            $seat = Seat::where('slug', $seat_slug)->first();
            $team = Team::where('slug', $slug)->first();

            /* Check if the seat exists in the database */
            if (!$seat) {
                /* Redirect to the dashboard page if the seat is not found */
                return redirect()->route('dashboardPage', ['slug' => $slug])
                    ->withErrors(['error' => "Seat `{$seat_slug}` not found"]);
            }

            /* Initialize the role_id to 0, to be updated if the user is assigned a seat */
            $role_id = 0;

            /* Check if the current user is the creator of the team */
            if ($team->creator_id !== $user->id) {
                /* If the user is not the creator, check if they are a team member */
                $team_member = Team_Member::where('team_id', $team->id)
                    ->where('user_id', $user->id)
                    ->first();

                /* If the user is not a member of the team, redirect with an error message */
                if (!$team_member) {
                    return redirect()->route('dashboardPage', ['slug' => $slug])
                        ->withErrors(['error' => "You cannot access seat `{$slug}`"]);
                }

                /* Check if the team member is assigned to the seat */
                $assigned_seat = Assigned_Seat::where('member_id', $team_member->id)
                    ->where('seat_id', $seat->id)
                    ->first();

                /* If no assigned seat is found, redirect with an error message */
                if (!$assigned_seat) {
                    return redirect()->route('dashboardPage', ['slug' => $slug])
                        ->withErrors(['error' => "You cannot access seat `{$slug}`"]);
                }

                /* Store the role_id for the assigned seat */
                $role_id = $assigned_seat->role_id;
            }

            $demanding_permissions = [
                'manage_webhooks',
                'manage_other_messages',
                'manage_global_limits',
                'manage_blacklist',
                'manage_chat',
                'manage_campaigns',
                'manage_campaign_details_and_reports',
                'open_linkedin_profile',
                'manage_linkedin_integrations',
                'manage_account_health',
                'manage_email_settings'
            ];

            /* Check if the user is the creator of the team */
            if (session('is_creator')) {
                /* Grant all demanding permissions to the creator */
                foreach ($demanding_permissions as $perm_slug) {
                    session([$perm_slug => true]);
                }
            } else {
                /* Set the user's session based on the assigned permissions */
                foreach ($demanding_permissions as $perm_slug) {
                    $permission = Permission::where('slug', $perm_slug)->first();
                    $permission = Role_Permission::where('role_id', $role_id)
                        ->where('permission_id', $permission->id)
                        ->first();
                    if (!$permission->access) {
                        session([$perm_slug => false]);
                    } else {
                        if (!$permission->view_only) {
                            session([$perm_slug => true]);
                        } else {
                            session([$perm_slug => 'view_only']);
                        }
                    }
                }
            }

            $linkedin_integrations = Linkedin_Integration::where('seat_id', $seat->id)->first();
            if ($linkedin_integrations) {
                /* Prepare the request data as an array */
                $requestData = ['account_id' => $linkedin_integrations['account_id']];

                /* Create a new Request object using the array of data */
                $request = new \Illuminate\Http\Request();
                $request->replace($requestData);

                /* Initialize the UnipileController */
                $uc = new UnipileController();

                /* Call retrieve_an_account method with the properly formatted Request object */
                $account = $uc->retrieve_an_account($request)->getData(true);

                /* Call retrieve_own_profile method with the same Request object */
                $account_profile = $uc->retrieve_own_profile($request)->getData(true);

                /* Store the account and profile data in the session */
                session(['seat_linkedin' => $account['account']]);
                session(['linkedin_profile' => $account_profile['profile']]);
            }

            /* If the user is authorized, proceed to the next middleware or request handler */
            return $next($request);
        } catch (Exception $e) {
            /* Log the error for debugging */
            Log::error($e);

            /* Redirect to the dashboard page if the any general exception */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => "Something went wrong"]);
        }
    }
}
