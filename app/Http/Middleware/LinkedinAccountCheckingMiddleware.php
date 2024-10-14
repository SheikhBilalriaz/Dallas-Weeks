<?php

namespace App\Http\Middleware;

use App\Models\Linkedin_Integration;
use Closure;
use Illuminate\Http\Request;
use App\Models\Seat;
use Symfony\Component\HttpFoundation\Response;

class LinkedinAccountCheckingMiddleware
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

        /* Retrieve the seat record based on the seat_slug */
        $seat = Seat::where('slug', $seat_slug)->first();

        /* Check if LinkedIn integration exists for the seat */
        if ($seat && !Linkedin_Integration::where('seat_id', $seat->id)->exists()) {
            return redirect()->route('seatSettingPage', ['slug' => $slug, 'seat_slug' => $seat_slug])->with(['add_account' => true]);
        }

        /* If LinkedIn integration exists, proceed with the next request */
        return $next($request);
    }
}
