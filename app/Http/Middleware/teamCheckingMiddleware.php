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

        /* Attempt to retrieve the team where the user is the creator */
        $team = Team::where('slug', $slug)->where('creator_id', auth()->user()->id)->first();

        /* If the user is not the team creator, check if the team exists */
        if (!$team) {
            $team = Team::where('slug', $slug)->first();

            /* If the team exists, check if the user is a member of the team */
            if ($team) {
                $team_member = Team_Member::where('team_id', $team->id)->where('user_id', auth()->user()->id)->first();

                /* If the user is not a member of the team, redirect with an error message */
                if (!$team_member) {
                    return redirect()->route('dashboardPage')->withErrors(['error' => 'You cannot access team: ' . $slug])->with('team_slug', $slug);
                }
            } else {
                /* If no team is found, redirect with an error */
                return redirect()->route('dashboardPage')->withErrors(['error' => 'Team not found: ' . $slug])->with('team_slug', $slug);
            }
        }
        /* Store the user's roles and permissions in the session */
        $is_creator = Team::where('id', $team->id)->where('creator_id', $user->id)->exists();
        session(['is_creator' => $is_creator]);

        $is_manage_payment_system = Global_Permission::where('slug', 'manage_payment_system')
            ->where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->exists();
        session(['is_manage_payment_system' => $is_manage_payment_system]);

        $is_manage_global_blacklist = Global_Permission::where('slug', 'manage_global_blacklist')
            ->where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->exists();
        session(['is_manage_global_blacklist' => $is_manage_global_blacklist]);

        /* Continue processing the request */
        return $next($request);
    }
}
