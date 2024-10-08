<?php

namespace App\Http\Middleware;

use App\Models\Seat;
use App\Models\Team_Member;
use App\Models\Assigned_Seat;
use Closure;
use Illuminate\Http\Request;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

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
        /* Retrieve seat_id from request or session */
        $seat_id = $request->input('seat_id', session('seat_id'));

        /* Retrieve the slug from the request URL parameters */
        $slug = $request->route('slug');

        if (!$seat_id) {
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong, please try again']);
        }

        /* Store seat_id in session */
        session(['seat_id' => $seat_id]);

        /* Retrieve the currently authenticated user */
        $user = Auth::user();

        /* Retrieve the team and associated seat in a single query */
        $team = Team::where('slug', $slug)->first();
        $seat = Seat::where('id', $seat_id)->where('team_id', $team->id)->first();

        /* Check if the seat doesn't exist */
        if (!$seat) {
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Seat not Found']);
        }

        /* Allow access if the user is the team creator */
        if ($user->id == $team->creator_id) {
            return $next($request);
        }

        /* Retrieve the team member and assigned seat in one step */
        $member = Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->first();

        /* Check for assigned seat access */
        if ($member && Assigned_Seat::where('member_id', $member->id)->where('seat_id', $seat->id)->exists()) {
            return $next($request);
        }

        /* Access denied if the user is neither the team creator nor an assigned member */
        return redirect()->route('dashboardPage', ['slug' => $slug])->withErrors(['error' => 'You do not have access to this seat']);
    }
}
