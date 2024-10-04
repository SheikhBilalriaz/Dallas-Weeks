<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserAuthMiddleware
{
    /**
     * Handle an incoming request and check if the user is authenticated.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request instance.
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next  The next middleware or request handler.
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse  The response returned by the next middleware or redirect if unauthenticated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /* Check if the user is not authenticated */
        $user = Auth::user();

        /* If the user is not authenticated, clear the specific session keys */
        if (!$user) {
            session()->flush();
            return redirect()->route('loginPage')->withErrors(['error' => 'Please log in to access this page.']);
        }

        /* Store the user's email verification status in the session */
        session(['email_verified' => !is_null($user->verified_at)]);

        /* Continue processing the request */
        return $next($request);
    }
}
