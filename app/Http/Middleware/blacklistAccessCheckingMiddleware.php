<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Global_Permission;

class blacklistAccessCheckingMiddleware
{
    /**
     * Handle an incoming request to check if the user has access to the global blacklist.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request instance.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next  The next middleware or request handler.
     * @return \Symfony\Component\HttpFoundation\Response  The response returned by the next middleware or redirect if unauthorized.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            /* Retrieve the currently authenticated user */
            $user = Auth::user();

            /* Retrieve the 'slug' parameter from the request route (URL parameter) */
            $slug = $request->route('slug');

            /* Attempt to find the team using the provided slug */
            $team = Team::where('slug', $slug)->first();

            /* Fetch the user's permission to manage the global blacklist for this specific team */
            $permission = Global_Permission::where('user_id', $user->id)
                ->where('team_id', $team->id)
                ->where('slug', 'manage_global_blacklist')
                ->first();

            /* If the permission is missing or the user doesn't have access, redirect them to the dashboard with an error */
            if (!$permission || !$permission->access) {
                return redirect()->route('dashboardPage', ['slug' => $slug])
                    ->withErrors(['error' => "You don't have permission to manage the blacklist."]);
            }

            /* Proceed with the request if the user has the necessary permission */
            return $next($request);
        } catch (Exception $e) {
            /* Log the exception details for debugging purposes */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => "Something went wrong"]);
        }
    }
}
