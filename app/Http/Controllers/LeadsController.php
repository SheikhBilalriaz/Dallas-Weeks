<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Campaign;
use App\Models\Campaign_Element;
use App\Models\Element;
use App\Models\Email_Integraion;
use App\Models\Email_Setting;
use App\Models\Global_Blacklist;
use App\Models\Global_Limit;
use App\Models\Global_Setting;
use App\Models\Team;
use App\Models\Lead;
use App\Models\Lead_Action;
use App\Models\Linkedin_Setting;
use App\Models\Schedule;
use App\Models\Webhook;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Writer;

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
                        ->orWhere('slug', 'like', 'follow%')
                        ->orWhere('slug', 'like', 'message%')
                        ->orWhere('slug', 'like', 'inmail_message%');
                })->get()->groupBy(function ($element) {
                    if (is_string($element->slug)) {
                        if (str_starts_with($element->slug, 'view_profile')) return 'view_profile';
                        if (str_starts_with($element->slug, 'invite_to_connect')) return 'invite_to_connect';
                        if (str_starts_with($element->slug, 'email_message')) return 'email_message';
                        if (str_starts_with($element->slug, 'follow')) return 'follow';
                        if (str_starts_with($element->slug, 'message')) return 'message';
                        if (str_starts_with($element->slug, 'inmail_message')) return 'inmail_message';
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
                    'message_count' => isset($campaignElements['message'])
                        ? $lead_actions_of_pre_month->whereIn('current_element_id', $campaignElements['message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'in_mail_count' => isset($campaignElements['inmail_message'])
                        ? $lead_actions_of_pre_month->whereIn('current_element_id', $campaignElements['inmail_message']->pluck('id'))->where('status', 'completed')->count()
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
                'past_month_data' => $past_month_reports,
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
            if (
                Global_Blacklist::where('team_id', $team->id)
                ->where('keyword', $url)
                ->where('blacklist_type', 'profile_url')
                ->exists()
            ) {
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
                            $item['current_step'] = $campaignElement->name;
                        }
                    }
                    $nextElementId = $leadAction->next_true_element_id;
                    $updatedCampaignElement = Campaign_Element::find($nextElementId);
                    if ($updatedCampaignElement) {
                        $elementId = $updatedCampaignElement->element_id;
                        $campaignElement = Element::find($elementId);
                        if ($campaignElement) {
                            $item['next_step'] = $campaignElement->name;
                        }
                    } else {
                        $nextElementId = $leadAction->next_false_element_id;
                        $updatedCampaignElement = Campaign_Element::find($nextElementId);
                        if ($updatedCampaignElement) {
                            $elementId = $updatedCampaignElement->element_id;
                            $campaignElement = Element::find($elementId);
                            if ($campaignElement) {
                                $item['next_step'] = $campaignElement->name;
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
                    'message_count' => isset($campaignElements['message'])
                        ? $actions->whereIn('current_element_id', $campaignElements['message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'in_mail_count' => isset($campaignElements['inmail_message'])
                        ? $actions->whereIn('current_element_id', $campaignElements['inmail_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                ];
            }
            if (count($lead) > 0) {
                return response()->json([
                    'success' => true,
                    'leads' => $lead,
                    'campaign' => $campaign,
                    'settings' => $settings,
                    'reports' => $reports,
                    'past_month_data' => $past_month_reports,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'leads' => $lead,
                    'campaign' => $campaign,
                    'settings' => $settings,
                    'reports' => $reports,
                    'past_month_data' => $past_month_reports,
                ]);
            }
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function applySettings($campaign, $url, $profile)
    {
        try {
            if (
                Global_Setting::where('campaign_id', $campaign->id)
                ->where('setting_slug', 'global_settings_discover_new_leads_only')
                ->value('value') === 'yes'
            ) {
                if (
                    Lead::whereIn('campaign_id', Campaign::where('seat_id', $campaign->seat_id)->pluck('id'))
                    ->where('profileUrl', $url)
                    ->exists()
                ) {
                    return false;
                }
            }

            if (
                Linkedin_Setting::where('campaign_id', $campaign->id)
                ->where('setting_slug', 'linkedin_settings_remove_leads_with_pending_connections')
                ->value('value') === 'yes'
            ) {
                if (
                    Lead::whereIn(
                        'id',
                        Lead_Action::whereIn(
                            'current_element_id',
                            Campaign_Element::whereIn(
                                'campaign_id',
                                Campaign::where('seat_id', $campaign->seat_id)->pluck('id')->toArray()
                            )
                                ->where('slug', 'like', 'invite_to_connect%')
                                ->pluck('id')
                        )
                            ->where('status', 'inprogress')
                            ->pluck('id')
                    )
                    ->where('profileUrl', $url)
                    ->exists()
                ) {
                    return false;
                }
            }

            if (
                Linkedin_Setting::where('campaign_id', $campaign->id)
                ->where('setting_slug', 'linkedin_settings_discover_leads_with_open_profile_status_only')
                ->value('value') === 'yes'
            ) {
                if (
                    (isset($profile['is_open_profile'])
                        &&
                        !$profile['is_open_profile'])
                    ||
                    !isset($profile['is_open_profile'])
                ) {
                    return false;
                }
            }

            if (
                Linkedin_Setting::where('campaign_id', $campaign->id)
                ->where('setting_slug', 'linkedin_settings_discover_leads_with_open_profile_status_only')
                ->value('value') === 'yes'
            ) {
                if (
                    (isset($user_profile['is_premium'])
                        &&
                        !$user_profile['is_premium'])
                    ||
                    !isset($user_profile['is_premium'])
                ) {
                    return false;
                }
            }
            return true;
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }

    function get_view_count($campaigns, $seat)
    {
        $past_time = now()->modify('-1 days')->format('Y-m-d');
        $views = Campaign_Element::whereIn('campaign_id', $campaigns->pluck('id')->toArray())
            ->where('slug', 'like', 'view_profile%')->get();
        $views = Lead_Action::whereIn('current_element_id', $views->pluck('id')->toArray())
            ->whereDate('updated_at', '>=', $past_time)->where('status', 'completed')->get();
        $profile_views = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'profile_views')->first();
        $view_count = floor(($profile_views->value - count($views)) / count($campaigns));
        if ($view_count > 0) {
            return $view_count;
        }
        return 0;
    }

    function get_invite_count($campaigns, $seat)
    {
        $past_time = now()->modify('-1 days')->format('Y-m-d');
        $invites = Campaign_Element::whereIn('campaign_id', $campaigns->pluck('id')->toArray())
            ->where('slug', 'like', 'invite_to_connect%')->get();
        $invites = Lead_Action::whereIn('current_element_id', $invites->pluck('id')->toArray())
            ->whereDate('updated_at', '>=', $past_time)->where('status', 'completed')->get();
        $invite = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'invite')->first();
        $invite_count = floor(($invite->value - count($invites)) / count($campaigns));
        if ($invite_count > 0) {
            return $invite_count;
        }
        return 0;
    }

    function get_message_count($campaigns, $seat)
    {
        $past_time = now()->modify('-1 days')->format('Y-m-d');
        $messages = Campaign_Element::whereIn('campaign_id', $campaigns->pluck('id')->toArray())
            ->where('slug', 'like', 'message%')->get();
        $messages = Lead_Action::whereIn('current_element_id', $messages->pluck('id')->toArray())
            ->whereDate('updated_at', '>=', $past_time)->where('status', 'completed')->get();
        $message = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'message')->first();
        $message_count = floor(($message->value - count($messages)) / count($campaigns));
        if ($message_count > 0) {
            return $message_count;
        }
        return 0;
    }

    function get_follow_count($campaigns, $seat)
    {
        $past_time = now()->modify('-1 days')->format('Y-m-d');
        $follows = Campaign_Element::whereIn('campaign_id', $campaigns->pluck('id')->toArray())
            ->where('slug', 'like', 'follow%')->get();
        $follows = Lead_Action::whereIn('current_element_id', $follows->pluck('id')->toArray())
            ->whereDate('updated_at', '>=', $past_time)->where('status', 'completed')->get();
        $follow = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'follows')->first();
        $follow_count = floor(($follow->value - count($follows)) / count($campaigns));
        if ($follow_count > 0) {
            return $follow_count;
        }
        return 0;
    }

    function get_inmail_message_count($campaigns, $seat)
    {
        $past_time = now()->modify('-1 days')->format('Y-m-d');
        $inmail_messages = Campaign_Element::whereIn('campaign_id', $campaigns->pluck('id')->toArray())
            ->where('slug', 'like', 'inmail_message%')->get();
        $inmail_messages = Lead_Action::whereIn('current_element_id', $inmail_messages->pluck('id')->toArray())
            ->whereDate('updated_at', '>=', $past_time)->where('status', 'completed')->get();
        $inmail_message = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'inmail')->first();
        $inmail_message_count = floor(($inmail_message->value - count($inmail_messages)) / count($campaigns));
        if ($inmail_message_count > 0) {
            return $inmail_message_count;
        }
        return 0;
    }

    function get_email_message_count($campaigns, $seat)
    {
        $past_time = now()->modify('-1 days')->format('Y-m-d');
        $email_messages = Campaign_Element::whereIn('campaign_id', $campaigns->pluck('id')->toArray())
            ->where('slug', 'like', 'email_message%')->get();
        $email_messages = Lead_Action::whereIn('current_element_id', $email_messages->pluck('id')->toArray())
            ->whereDate('updated_at', '>=', $past_time)->where('status', 'completed')->get();
        $email_message = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'email_message')->first();
        $email_message_count = floor(($email_message->value - count($email_messages)) / count($campaigns));
        if ($email_message_count > 0) {
            return $email_message_count;
        }
        return 0;
    }

    function sendLeadsToEmail($slug, $seat_slug, Request $request)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $campaign_id = $request->input('campaign_id');
            $email = $request->input('email');
            $campaigns = Campaign::where('seat_id', $seat->id);
            if ($campaign_id !== 'all') {
                $campaigns->where('id', $campaign_id);
            }
            $campaigns = $campaigns->get();
            if ($campaigns->isNotEmpty()) {
                foreach ($campaigns as $campaign) {
                    $fileName = 'leads_' . time() . '_' . Str::random(10) . '.csv';
                    $uploadDir = 'uploads/';
                    $uploadFilePath = $uploadDir . $fileName;
                    $csv = Writer::createFromFileObject(new \SplTempFileObject());
                    $leads = Lead::where('campaign_id', $campaign->id)->get();
                    $csv->insertOne(['Sr. #', 'Campaign Id', 'Campaign Name', 'Status', 'Contact', 'Title Company', 'Send Connections', 'Next Step', 'Executed Time']);
                    if (!$leads->isEmpty()) {
                        $count = 1;
                        foreach ($leads as $lead) {
                            $csv->insertOne([
                                $count++,
                                $campaign->id,
                                $campaign->name,
                                $lead->is_active == '1' ? 'Active' : 'Not Active',
                                $lead->contact,
                                $lead->title_company,
                                $lead->send_connections,
                                $lead->next_step ?? 'Completed',
                                $lead->executed_time
                            ]);
                        }
                    } else {
                        $csv->insertOne(['No Lead Found', '', '', '', '', '', '', '', '']);
                    }
                    $csvContent = $csv->getContent();
                    Storage::put($uploadFilePath, $csvContent);
                    $filePaths[$campaign->name] = $uploadFilePath;
                }
                Mail::send([], [], function ($message) use ($email, $filePaths) {
                    $message->to($email)
                        ->subject('Your Leads CSVs');
                    $count = 1;
                    foreach ($filePaths as $name => $filePath) {
                        $message->attach(Storage::path($filePath), [
                            'as' => $name,
                            'mime' => 'text/csv',
                        ]);
                    }
                });
            }
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
