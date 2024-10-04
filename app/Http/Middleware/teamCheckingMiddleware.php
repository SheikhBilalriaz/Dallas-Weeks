<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\Team_Member;
use App\Models\Global_Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class teamCheckingMiddleware
{
    /**
     * Handle an incoming request to ensure the user is part of the team referenced by the session's team slug.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request instance.
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next  The next middleware or request handler.
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse  The response returned by the next middleware or redirect if unauthenticated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /* Retrieve the currently authenticated user */
        $user = Auth::user();

        /* Retrieve the slug from the request URL parameters */
        $slug = $request->route('slug');

        /* Attempt to retrieve the team by slug */
        $team = Team::where('slug', $slug)->first();

        /* If no team is found, redirect with an error message */
        if (!$team) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => "Team `{$slug}` not found"])
                ->with('team_slug', $slug);
        }

        /* Check if the user is the team creator or a team member */
        if ($team->creator_id !== $user->id) {
            $team_member = Team_Member::where('team_id', $team->id)
                ->where('user_id', $user->id)
                ->first();

            /* If the user is not a member of the team, redirect with an error message */
            if (!$team_member) {
                return redirect()->route('dashboard')
                    ->withErrors(['error' => "You cannot access team `{$slug}`"])
                    ->with('team_slug', $slug);
            }
        }

        /* Store whether the user is the team creator */
        session(['is_creator' => $team->creator_id == $user->id]);

        /* Retrieve permissions for the user in a single query */
        $permissions = Global_Permission::where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->whereIn('slug', ['manage_payment_system', 'manage_global_blacklist'])
            ->pluck('slug')
            ->toArray();

        /* Store permissions in the session */
        session([
            'is_manage_payment_system' => in_array('manage_payment_system', $permissions),
            'is_manage_global_blacklist' => in_array('manage_global_blacklist', $permissions),
        ]);

        /* Continue processing the request */
        return $next($request);
    }
}
