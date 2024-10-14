<?php

namespace App\Http\Controllers;

use App\Models\Account_Health;
use App\Models\Campaign;
use App\Models\Email_Integraion;
use App\Models\Linkedin_Integration;
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
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Display the seat dashboard with seat details, time zones, and integrated emails.
     *
     * @param string $slug The team slug.
     * @param string $seat_slug The seat slug.
     * @return \Illuminate\Http\JsonResponse The JSON response with seat data and statuses.
     */
    public function seatDashboard($slug, $seat_slug)
    {
        try {
            /* Retrieve the team and seat by their slugs */
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $linkedin_integrations = Linkedin_Integration::where('seat_id', $seat->id)->first();
            $uc = new UnipileController();
            $request = ['account_id' => $linkedin_integrations['account_id'], 'limit' => 10];
            $chats = $uc->list_all_chats(new \Illuminate\Http\Request($request))->getData(true);
            if (!isset($chats['error'])) {
                $chats = $chats['chats']['items'];
            } else {
                $chats = array();
            }
            $request = ['account_id' => $linkedin_integrations['account_id'], 'limit' => 3];
            $relations = $uc->list_all_relations(new \Illuminate\Http\Request($request))->getData(true);
            if (!isset($relations['error'])) {
                $relations = $relations['relations']['items'];
            } else {
                $relations = array();
            }
            $request = ['account_id' => $linkedin_integrations['account_id'], 'profile_url' => session('linkedin_profile')['provider_id'],];
            $profile = $uc->view_profile(new \Illuminate\Http\Request($request))->getData(true);
            $campaigns = Campaign::where('seat_id', $seat->id)->get();

            /* Prepare data to pass to the view */
            $data = [
                'title' => 'Dashboard - Networked',
                'team' => $team,
                'seat' => $seat,
                'profile' => $profile['user_profile'],
                'campaigns' => $campaigns,
                'chats' => $chats,
                'relations' => $relations
            ];

            /* Return the view with the seat data */
            return view('back.main', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
