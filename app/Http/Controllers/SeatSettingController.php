<?php

namespace App\Http\Controllers;

use App\Models\Account_Health;
use App\Models\Account_Health_Limit;
use App\Models\Email_Integraion;
use App\Models\Global_Limit;
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

class SeatSettingController extends Controller
{
    public function seatSetting($slug, $seat_slug)
    {
        try {
            /* Retrieve the team and seat by their slugs */
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();

            /* Retrieve the list of time zones with their GMT offsets */
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

            /* Retrieve seat start and end times */
            $start_time = Seat_Time::where('seat_id', $seat->id)->where('time_status', 'start')->first();
            $end_time = Seat_Time::where('seat_id', $seat->id)->where('time_status', 'end')->first();

            /* Retrieve the seat's time zone and health status (e.g., run on weekends) */
            $seat_zone = Seat_Timezone::where('seat_id', $seat->id)->first();
            $run_on_weekends = Account_Health::where('seat_id', $seat->id)->where('health_slug', 'run_on_weekends')->first();
            $oldest_pending_invitations = Account_Health::where('seat_id', $seat->id)->where('health_slug', 'oldest_pending_invitations')->first();
            $pending_connections = Account_Health_Limit::where('seat_id', $seat->id)->where('health_slug', 'pending_connections')->first();
            $profile_views = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'profile_views')->first();
            $follows = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'follows')->first();
            $invite = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'invite')->first();
            $message = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'message')->first();
            $inmail = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'inmail')->first();
            $discover = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'discover')->first();
            $email_message = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'email_message')->first();
            $email_delay = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'email_delay')->first();

            /* Retrieve all email integrations for the seat */
            $integrated_emails = Email_Integraion::where('seat_id', $seat->id)->get();

            /* Initialize the UnipileController to retrieve external account information */
            $uc = new UnipileController();

            /* Loop through each integrated email and retrieve account & profile details */
            foreach ($integrated_emails as $key => $email) {
                /* Prepare the request data for Unipile API */
                $requestData = ['account_id' => $email['account_id']];
                $request = new \Illuminate\Http\Request();
                $request->replace($requestData);

                /* Retrieve the account details using Unipile API */
                $account = $uc->retrieve_an_account($request)->getData(true);

                /* If there is an error, remove this email from the list */
                if (isset($account['error'])) {
                    unset($integrated_emails[$key]);
                    continue;
                }

                /* Attach account information to the integrated email */
                $integrated_emails[$key]['account'] = $account['account'];

                /* Retrieve the profile details using Unipile API */
                $profile = $uc->retrieve_own_profile($request)->getData(true);

                /* If there is an error, remove this email from the list */
                if (isset($profile['error'])) {
                    unset($integrated_emails[$key]);
                    continue;
                }

                /* Attach profile information to the integrated email */
                $integrated_emails[$key]['profile'] = $profile['profile'];
            }

            /* Prepare data to pass to the view */
            $data = [
                'title' => 'Dashboard - Setting',
                'team' => $team,
                'seat' => $seat,
                'time_zones' => $time_zones_with_offset,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'seat_zone' => $seat_zone,
                'oldest_pending_invitations' => $oldest_pending_invitations,
                'run_on_weekends' => $run_on_weekends,
                'emails' => $integrated_emails,
                'error' => session()->has('error') ? session('error')->first() : null,
                'pending_connections' => $pending_connections,
                'profile_views' => $profile_views,
                'follows' => $follows,
                'invite' => $invite,
                'message' => $message,
                'inmail' => $inmail,
                'discover' => $discover,
                'email_message' => $email_message,
                'email_delay' => $email_delay,
            ];

            /* Return the view with the seat data */
            return view('back.setting', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
