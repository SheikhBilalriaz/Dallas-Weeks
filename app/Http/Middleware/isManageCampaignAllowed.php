<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isManageCampaignAllowed
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
            if (session('manage_campaigns') === true) {
                return $next($request);
            }

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('campaignPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'You can not manage campaigns or leads']);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
