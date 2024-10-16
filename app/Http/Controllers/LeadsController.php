<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Campaign;
use App\Models\Campaign_Element;
use App\Models\Element;
use App\Models\Email_Integraion;
use App\Models\Global_Blacklist;
use App\Models\Team;
use App\Models\Lead;
use App\Models\Lead_Action;
use App\Models\Schedule;
use Exception;
use Illuminate\Support\Facades\Log;

class LeadsController extends Controller
{
    function leads($slug, $seat_slug)
    {
        try {
            $seat = Seat::where('slug', $seat_slug)->first();
            $team = Team::where('slug', $slug)->first();
            $campaigns = Campaign::where('seat_id', $seat->id)->where('is_active', 1)->where('is_archive', 0)->get();
            $leads = Lead::whereIn('campaign_id', $campaigns->pluck('id')->toArray())->get();
            foreach ($leads as $lead) {
                $leadAction = Lead_Action::where('lead_id', $lead['id'])->orderBy('created_at', 'desc')->first();
                $lead['current_step'] = null;
                $lead['next_step'] = null;
                if ($leadAction) {
                    $currentElementId = $leadAction->current_element_id;
                    $updatedCampaignElement = Campaign_Element::find($currentElementId);
                    if ($updatedCampaignElement) {
                        $elementId = $updatedCampaignElement->element_id;
                        $campaignElement = Element::find($elementId);
                        if ($campaignElement) {
                            $lead['current_step'] = $campaignElement->name;
                        }
                    }
                    $nextElementId = $leadAction->next_true_element_id;
                    $updatedCampaignElement = Campaign_Element::find($nextElementId);
                    if ($updatedCampaignElement) {
                        $elementId = $updatedCampaignElement->element_id;
                        $campaignElement = Element::find($elementId);
                        if ($campaignElement) {
                            $lead['next_step'] = $campaignElement->name;
                        }
                    } else {
                        $nextElementId = $leadAction->next_false_element_id;
                        $updatedCampaignElement = Campaign_Element::find($nextElementId);
                        if ($updatedCampaignElement) {
                            $elementId = $updatedCampaignElement->element_id;
                            $campaignElement = Element::find($elementId);
                            if ($campaignElement) {
                                $lead['next_step'] = $campaignElement->name;
                            }
                        }
                    }
                }
            }
            $uc = new UnipileController();
            $schedules = Schedule::where('seat_id', $seat->id)->get();
            $emails = Email_Integraion::where('seat_id', $seat->id)->get();
            foreach ($emails as $key => $email) {
                $request = new \Illuminate\Http\Request(['account_id' => $email['account_id']]);
                $accountResponse = $uc->retrieve_an_account($request)->getData(true);
                if (isset($accountResponse['error'])) {
                    unset($emails[$key]);
                    continue;
                }
                $email['account'] = $accountResponse['account'];
                $profileResponse = $uc->retrieve_own_profile($request)->getData(true);
                if (isset($profileResponse['error'])) {
                    unset($emails[$key]);
                    continue;
                }
                $email['profile'] = $profileResponse['profile'];
            }
            $seats = Seat::where('team_id', $team->id)->get();
            $team_schedules = Schedule::whereIn('seat_id', $seats->pluck('id')->toArray())->get();
            $data = [
                'title' => 'Dashboard - Leads',
                'emails' => $emails,
                'seat' => $seat,
                'team' => $team,
                'leads' => $leads,
                'campaigns' => $campaigns,
                'schedules' => $schedules,
                'team_schedules' => $team_schedules,
            ];
            return view('back.leads', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
    
    public function duplicateURLCampaign($slug, $seat_slug, $url)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('team_id', $team->id)->get();
            $campaigns = Campaign::whereIn('seat_id', $seat->pluck('id')->toArray())->get();
            $leads = Lead::whereIn('campaign_id', $campaigns->pluck('id')->toArray())->get();
            foreach ($leads as $lead) {
                if (stripos($lead->profileUrl, $url) !== false) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }

    function getLeadsCountByCampaign($campaign_id)
    {
        $lead_count = Lead::where('campaign_id', $campaign_id)->count();
        return $lead_count;
    }

    function getViewProfileByCampaign($campaign_id)
    {
        $campaign_elements = Campaign_Element::where('campaign_id', $campaign_id)->where('slug', 'like', 'view_profile%')->get();
        $view_action_count = Lead_Action::whereIn('current_element_id', $campaign_elements->pluck('id')->toArray())->where('status', 'completed')->count();
        return $view_action_count;
    }

    function getInviteToConnectByCampaign($campaign_id)
    {
        $campaign_elements = Campaign_Element::where('campaign_id', $campaign_id)->where('slug', 'like', 'invite_to_connect%')->get();
        $invite_action_count = Lead_Action::whereIn('current_element_id', $campaign_elements->pluck('id')->toArray())->where('status', 'completed')->count();
        return $invite_action_count;
    }

    function getSentMessageByCampaign($campaign_id)
    {
        $campaign_elements = Campaign_Element::where('campaign_id', $campaign_id)->where('slug', 'like', 'message%')->get();
        $message_count = Lead_Action::whereIn('current_element_id', $campaign_elements->pluck('id')->toArray())->where('status', 'completed')->count();
        return $message_count;
    }

    function getSentEmailByCampaign($campaign_id)
    {
        $campaign_elements = Campaign_Element::where('campaign_id', $campaign_id)->where('slug', 'like', 'email_message%')->get();
        $email_count = Lead_Action::whereIn('current_element_id', $campaign_elements->pluck('id')->toArray())->where('status', 'completed')->count();
        return $email_count;
    }

    public function blacklistURLCampaign($slug, $seat_slug, $url)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            if (Global_Blacklist::where('team_id', $team->id)->where('blacklist_type', 'profile_url')->where('keyword', $url)->exists()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }
}
