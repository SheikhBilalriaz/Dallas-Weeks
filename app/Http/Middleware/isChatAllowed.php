<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Teams;
use App\Models\SeatInfo;
use App\Models\AssignedSeats;
use App\Models\Roles;
use App\Models\Permissions;
use App\Models\Role_Permission;
use Exception;

class isChatAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /* Retrieve the slug and seat_slug from the session */
        $slug = session('slug');
        $seat_slug = session('seat_slug');
        
        try {
            if (session('manage_chat') !== true && session('manage_chat') !== 'view_only') {
                /* Redirect to the dashboard with a generic error message if an exception occurs */
                return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                    ->withErrors(['error' => 'You can not access messages or chats']);
            }

            return $next($request);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
