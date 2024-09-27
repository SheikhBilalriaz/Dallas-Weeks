<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        /* Retrieve the slug from the request URL parameters */
        $slug = $request->route('slug');

        /* Check if the session contains the 'is_manage_global_blacklist' permission */
        if (!session('is_manage_global_blacklist')) {
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => "You don't have permission to manage the blacklist."]);
        }

        /* Proceed with the request if the user has the necessary permission */
        return $next($request);
    }
}
