<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Campaign;
use App\Models\Campaign_Element;
use App\Models\Element;
use App\Models\Email_Integraion;
use App\Models\Email_Setting;
use App\Models\Global_Blacklist;
use App\Models\Global_Setting;
use App\Models\Team;
use App\Models\Lead;
use App\Models\Lead_Action;
use App\Models\Linkedin_Setting;
use App\Models\Schedule;
use App\Models\Webhook;
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
            $data = [
                'title' => 'Dashboard - Leads',
                'emails' => $emails,
                'seat' => $seat,
                'team' => $team,
                'leads' => $leads,
                'campaigns' => $campaigns,
                'schedules' => $schedules,
                'team_schedules' => $team_schedules,
                'reports' => $reports,
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

    function getLeadsByCampaign($slug, $seat_slug, $id, $search)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $lead = null;
            $campaign = null;
            $email_setting = null;
            $linkedin_setting = null;
            $global_setting = null;
            if ($search != 'null' && $id != 'all') {
                $campaign = Campaign::where('seat_id', $seat->id)
                    ->where('id', $id)
                    ->where('is_archive', 0)
                    ->first();
                $lead = Lead::where(function ($query) use ($search) {
                    $query->where('contact', 'LIKE', '%' . $search . '%')->orWhere('title_company', 'LIKE', '%' . $search . '%');
                })->where('campaign_id', $campaign->id)->get();
                $email_setting = Email_Setting::where('campaign_id', $campaign->id)->get();
                $linkedin_setting = Linkedin_Setting::where('campaign_id', $campaign->id)->get();
                $global_setting = Global_Setting::where('campaign_id', $campaign->id)->get();
            } else if ($id != 'all') {
                $campaign = Campaign::where('seat_id', $seat->id)
                    ->where('id', $id)
                    ->where('is_archive', 0)
                    ->first();
                $lead = Lead::where('campaign_id', $campaign->id)->get();
                $email_setting = Email_Setting::where('campaign_id', $campaign->id)->get();
                $linkedin_setting = Linkedin_Setting::where('campaign_id', $campaign->id)->get();
                $global_setting = Global_Setting::where('campaign_id', $campaign->id)->get();
            } else if ($search != 'null') {
                $campaign = Campaign::where('seat_id', $seat->id)
                    ->where('is_archive', 0)
                    ->get();
                $lead = Lead::where(function ($query) use ($search) {
                    $query->where('contact', 'LIKE', '%' . $search . '%')->orWhere('title_company', 'LIKE', '%' . $search . '%');
                })->whereIn('campaign_id', $campaign->pluck('id')->toArray())->get();
                $campaign = null;
            } else {
                $campaign = Campaign::where('seat_id', $seat->id)
                    ->where('is_archive', 0)
                    ->get();
                $lead = Lead::whereIn('campaign_id', $campaign->pluck('id')->toArray())->get();
                $campaign = null;
            }
            foreach ($lead as $item) {
                $leadAction = Lead_Action::where('lead_id', $item['id'])->orderBy('created_at', 'desc')->first();
                $item['current_step'] = null;
                $item['next_step'] = null;
                if ($leadAction) {
                    $currentElementId = $leadAction->current_element_id;
                    $updatedCampaignElement = Campaign_Element::find($currentElementId);
                    if ($updatedCampaignElement) {
                        $elementId = $updatedCampaignElement->element_id;
                        $campaignElement = Element::find($elementId);
                        if ($campaignElement) {
                            $item['current_step'] = $campaignElement->element_name;
                        }
                    }
                    $nextElementId = $leadAction->next_true_element_id;
                    $updatedCampaignElement = Campaign_Element::find($nextElementId);
                    if ($updatedCampaignElement) {
                        $elementId = $updatedCampaignElement->element_id;
                        $campaignElement = Element::find($elementId);
                        if ($campaignElement) {
                            $item['next_step'] = $campaignElement->element_name;
                        }
                    } else {
                        $nextElementId = $leadAction->next_false_element_id;
                        $updatedCampaignElement = Campaign_Element::find($nextElementId);
                        if ($updatedCampaignElement) {
                            $elementId = $updatedCampaignElement->element_id;
                            $campaignElement = Element::find($elementId);
                            if ($campaignElement) {
                                $item['next_step'] = $campaignElement->element_name;
                            }
                        }
                    }
                }
            }
            $settings = [
                'email_setting' => $email_setting,
                'linkedin_setting' => $linkedin_setting,
                'global_setting' => $global_setting
            ];
            $campaignIds = [];
            if ($campaign instanceof \Illuminate\Database\Eloquent\Collection && $campaign->count() == 1) {
                $campaignIds = [$campaign->first()->id];
            } elseif ($campaign instanceof \App\Models\Campaign) {
                $campaignIds = [$campaign->id];
            } else {
                $campaignIds = Campaign::where('seat_id', $seat->id)->pluck('id')->toArray();
            }
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
            if (count($lead) > 0) {
                return response()->json(['success' => true, 'leads' => $lead, 'campaign' => $campaign, 'settings' => $settings, 'reports' => $reports]);
            } else {
                return response()->json(['success' => false, 'leads' => $lead, 'campaign' => $campaign, 'settings' => $settings, 'reports' => $reports]);
            }
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
