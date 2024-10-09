<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SeatDashboardController extends Controller
{
    public function toSeatDashboard($slug, Request $request)
    {
        try {
            /* Get the seat_id from the request input */
            $seat_id = $request->input('seat_id');

            /* Retrieve the team where the user is the creator based on the provided slug */
            $team = Team::where('slug', $slug)->first();

            /* Check if seat_id is provided */
            if (!$seat_id) {
                /* Redirect to the dashboard page with an error message if no seat_id is found */
                return redirect()->route('dashboardPage', ['slug' => $team->slug])
                    ->withErrors(['error' => 'Something went wrong, please try again']);
            }

            /* Find the seat by its ID */
            $seat = Seat::find($seat_id);

            /* Check if the seat exists in the database */
            if (!$seat) {
                /* Redirect to the dashboard page if the seat is not found */
                return redirect()->route('dashboardPage', ['slug' => $slug])
                    ->withErrors(['error' => 'Seat not found']);
            }

            /* Check if the seat is active (indicating that the payment is up-to-date) */
            if (!$seat->is_active) {
                /* Redirect to the dashboard page if the seat is inactive due to payment expiration */
                return redirect()->route('dashboardPage', ['slug' => $slug])
                    ->withErrors(['error' => 'Payment subscription expired']);
            }

            /* Redirect to the dashboard with the team slug */
            $redirect = redirect()->route('seatDashboardPage', ['slug' => $team->slug, 'seat_slug' => $seat->slug]);

            /* Include errors if present in the session */
            if (session()->has('error')) {
                $redirect->withErrors(['error' => session('error')->first()]);
            }

            return $redirect;
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Filter seats based on the search term and retrieve additional account information.
     *
     * @param string $slug
     * @param string $search The search term to filter seat names.
     * @return \Illuminate\Http\JsonResponse The JSON response with the filtered seats and their statuses.
     */
    public function seatDashboard($slug, $seat_slug)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $data = [
                'title' => 'Dashboard - Networked',
                'team' => $team,
                'seat' => $seat,
                'error' => session()->has('error') ? session('error')->first() : null,
            ];
            return view('back.setting', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }
}
