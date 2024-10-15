<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Global_Permission;
use Exception;
use Illuminate\Support\Facades\Log;

class invoiceAccessCheckingMiddleware
{
    /**
     * Handle an incoming request to check if the user has access to the global invoice system.
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

            /* Check if the user has permission to manage the payment system for this team */
            $permission = Global_Permission::where('user_id', $user->id)
                ->where('team_id', $team->id)
                ->where('slug', 'manage_payment_system')
                ->first();

            /* If permission is missing or the user doesn't have access, redirect to the dashboard with an error */
            if (!$permission || !$permission->access) {
                return redirect()->route('dashboardPage', ['slug' => $slug])
                    ->withErrors(['error' => "You don't have access to payment and invoices."]);
            }

            /* Proceed with the request if the user has the necessary permission */
            return $next($request);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => "Something went wrong"]);
        }
    }
}
