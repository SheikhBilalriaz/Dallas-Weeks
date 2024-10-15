<?php

namespace App\Http\Middleware;

use App\Models\Linkedin_Integration;
use Closure;
use Illuminate\Http\Request;
use App\Models\Seat;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LinkedinAccountCheckingMiddleware
{
    /**
     * Handle an incoming request to check if the seat has a LinkedIn account integrated.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request instance.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next  The next middleware or request handler.
     * @return \Symfony\Component\HttpFoundation\Response  The response returned by the next middleware or a redirect if LinkedIn integration is missing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            /* Retrieve 'slug' and 'seat_slug' parameters from the request route */
            $slug = $request->route('slug');
            $seat_slug = $request->route('seat_slug');

            /* Attempt to retrieve the seat record using the provided 'seat_slug' */
            $seat = Seat::where('slug', $seat_slug)->first();

            /* Check if the seat exists and if it lacks LinkedIn integration */
            if ($seat && !Linkedin_Integration::where('seat_id', $seat->id)->exists()) {
                /* Redirect to the seat settings page, prompting the user to add a LinkedIn account */
                return redirect()->route('seatSettingPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                    ->with(['add_account' => true]);
            }

            /* Proceed with the request if LinkedIn integration exists */
            return $next($request);
        } catch (Exception $e) {
            /* Log the error for debugging */
            Log::error($e);

            /* Redirect to the dashboard with an error message if something goes wrong */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => "Something went wrong"]);
        }
    }
}
