<?php

namespace App\Http\Controllers;

use App\Models\Account_Health;
use App\Models\Campaign;
use App\Models\Campaign_Element;
use App\Models\Email_Integraion;
use App\Models\Lead_Action;
use App\Models\Linkedin_Integration;
use App\Models\Seat;
use App\Models\Seat_Time;
use App\Models\Seat_Timezone;
use App\Models\Team;
use Carbon\Carbon;
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
            $lc = new LeadsController();
            $request = ['account_id' => $linkedin_integrations['account_id'], 'profile_url' => session('linkedin_profile')['provider_id'],];
            $profile = $uc->view_profile(new \Illuminate\Http\Request($request))->getData(true);
            $campaigns = Campaign::where('seat_id', $seat->id)->where('is_archive', 0)->get();
            foreach ($campaigns as $campaign) {
                $campaign['lead_count'] = $lc->getLeadsCountByCampaign($campaign->id);
                $campaign['view_action_count'] = $lc->getViewProfileByCampaign($campaign->id);
                $campaign['invite_action_count'] = $lc->getInviteToConnectByCampaign($campaign->id);
                $campaign['message_count'] = $lc->getSentMessageByCampaign($campaign->id);
                $campaign['email_action_count'] = $lc->getSentEmailByCampaign($campaign->id);
            }
            $campaigns = $campaigns->values();
            $campaignIds = Campaign::where('seat_id', $seat->id)->pluck('id')->toArray();
            $campaignElements = Campaign_Element::whereIn('campaign_id', $campaignIds)
                ->where(function ($query) {
                    $query->where('slug', 'like', 'view_profile%')
                        ->orWhere('slug', 'like', 'invite_to_connect%')
                        ->orWhere('slug', 'like', 'email_message%')
                        ->orWhere('slug', 'like', 'follow%');
                })->get()->groupBy(function ($element) {
                    if (is_string($element->slug)) {
                        if (str_starts_with($element->slug, 'view_profile')) return 'view_profile';
                        if (str_starts_with($element->slug, 'invite_to_connect')) return 'invite_to_connect';
                        if (str_starts_with($element->slug, 'email_message')) return 'email_message';
                        if (str_starts_with($element->slug, 'follow')) return 'follow';
                    }
                    return 'other';
                });
            $leadActions = Lead_Action::whereIn('campaign_id', $campaignIds)->get()
                ->groupBy(function ($item) {
                    return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
                });
            $reports = [];
            foreach ($leadActions as $date => $actions) {
                $reports[$date] = [
                    'invite_count' => isset($campaignElements['invite_to_connect'])
                        ? $actions->whereIn('current_element_id', $campaignElements['invite_to_connect']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'email_count' => isset($campaignElements['email_message'])
                        ? $actions->whereIn('current_element_id', $campaignElements['email_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'view_count' => isset($campaignElements['view_profile'])
                        ? $actions->whereIn('current_element_id', $campaignElements['view_profile']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'follow_count' => isset($campaignElements['follow'])
                        ? $actions->whereIn('current_element_id', $campaignElements['follow']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                ];
            }
            $lead_actions_of_pre_month = Lead_Action::whereIn('campaign_id', $campaignIds)
                ->whereBetween(
                    'created_at',
                    [
                        Carbon::now()->subMonth()->startOfDay(),
                        Carbon::now()->endOfDay()
                    ]
                )->get()->groupBy(function ($item) {
                    return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
                });
            $past_month_reports = [];
            for ($date = Carbon::now()->subMonth()->startOfDay(); $date <= Carbon::now()->endOfDay(); $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');
                $past_month_reports[$formattedDate] = [
                    'invite_count' => isset($campaignElements['invite_to_connect'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['invite_to_connect']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'email_count' => isset($campaignElements['email_message'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['email_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'view_count' => isset($campaignElements['view_profile'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['view_profile']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'follow_count' => isset($campaignElements['follow'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['follow']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                ];
            }
            /* Prepare data to pass to the view */
            $data = [
                'title' => 'Dashboard - Networked',
                'team' => $team,
                'seat' => $seat,
                'profile' => $profile['user_profile'],
                'campaigns' => $campaigns,
                'chats' => $chats,
                'relations' => $relations,
                'reports' => $reports,
                'past_month_data' => $past_month_reports,
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
