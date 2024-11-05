<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class isWebhookAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /* Retrieve the slug and seat_slug from the session */
        $slug = session('slug');
        $seat_slug = session('seat_slug');

        try {
            if (session('manage_webhooks') === true || session('manage_webhooks') === 'view_only') {
                return $next($request);
            }
            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'You can not access webhook']);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
