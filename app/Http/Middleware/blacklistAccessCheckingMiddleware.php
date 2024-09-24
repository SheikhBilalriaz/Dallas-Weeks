<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class blacklistAccessCheckingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /* Retrieve the slug from the request URL parameters */
        $slug = $request->route('slug');
        
        /* Check if the user has permission to manage the global blacklist. */
        if (!session('is_manage_global_blacklist')) {
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => "You don't have access to blacklist"]);
        }

        return $next($request);
    }
}
