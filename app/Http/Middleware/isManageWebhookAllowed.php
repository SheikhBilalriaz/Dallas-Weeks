<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class isManageWebhookAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /* Retrieve the slug and seat_slug from the request */
        $slug = $request->route('slug');
        $seat_slug = $request->route('seat_slug');

        try {
            if (session('manage_webhooks') === true) {
                return $next($request);
            }

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('webhookPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'You can not manage webhooks']);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
