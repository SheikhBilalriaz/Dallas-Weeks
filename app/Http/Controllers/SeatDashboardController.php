<?php

namespace App\Http\Controllers;

use App\Models\Account_Health;
use App\Models\Seat;
use App\Models\Seat_Time;
use App\Models\Seat_Timezone;
use App\Models\Team;
use Exception;
use DateTime;
use DateTimeZone;
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
            $time_zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
            $time_zones_with_offset = array_map(function ($timezone) {
                $datetime = new DateTime("now", new DateTimeZone($timezone));
                $offset = $datetime->getOffset() / 3600;
                $sign = ($offset >= 0) ? '+' : '-';
                return [
                    'timezone' => $timezone,
                    'offset' => 'GMT' . $sign . abs($offset),
                ];
            }, $time_zones);
            $start_time = Seat_Time::where('seat_id', $seat->id)->where('time_status', 'start')->first();
            $end_time = Seat_Time::where('seat_id', $seat->id)->where('time_status', 'end')->first();
            $seat_zone = Seat_Timezone::where('seat_id', $seat->id)->first();
            $run_on_weekends = Account_Health::where('seat_id', $seat->id)->where('health_slug', 'run_on_weekends')->first();
            $oldest_pending_invitations = Account_Health::where('seat_id', $seat->id)->where('health_slug', 'oldest_pending_invitations')->first();
            $data = [
                'title' => 'Dashboard - Networked',
                'team' => $team,
                'seat' => $seat,
                'time_zones' => $time_zones_with_offset,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'seat_zone' => $seat_zone,
                'oldest_pending_invitations' => $oldest_pending_invitations,
                'run_on_weekends' => $run_on_weekends,
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
