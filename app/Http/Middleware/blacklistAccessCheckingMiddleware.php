<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
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
        /* Retrieve the currently authenticated user */
        $user = Auth::user();

        /* Retrieve the slug from the request URL parameters */
        $slug = $request->route('slug');

        /* Attempt to retrieve the team by slug */
        $team = Team::where('slug', $slug)->first();

        /* Check user permissions for managing the global blacklist */
        $permission = Global_Permission::where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->where('slug', 'manage_global_blacklist')
            ->first();

        /* Check if the user has the necessary permission */
        if (!$permission || !$permission->access) {
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => "You don't have permission to manage the blacklist."]);
        }

        /* Proceed with the request if the user has the necessary permission */
        return $next($request);
    }
}
