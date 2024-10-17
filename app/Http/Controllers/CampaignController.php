<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Seat;
use App\Models\Campaign;
use App\Models\Campaign_Element;
use App\Models\Campaign_Path;
use App\Models\Campaign_Property;
use App\Models\Element;
use App\Models\Email_Integraion;
use App\Models\Email_Setting;
use App\Models\Global_Setting;
use App\Models\Linkedin_Integration;
use App\Models\Linkedin_Setting;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    public function campaign($slug, $seat_slug)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $linkedin_integrations = Linkedin_Integration::where('seat_id', $seat->id)->first();
            $lc = new LeadsController();
            $total_profile_views = 0;
            $campaigns = Campaign::where('seat_id', $seat->id)->where('is_active', 1)->where('is_archive', 0)->get();
            foreach ($campaigns as $campaign) {
                $campaign['lead_count'] = $lc->getLeadsCountByCampaign($campaign->id);
                $campaign['view_action_count'] = $lc->getViewProfileByCampaign($campaign->id);
                $total_profile_views += $lc->getViewProfileByCampaign($campaign->id);
                $campaign['invite_action_count'] = $lc->getInviteToConnectByCampaign($campaign->id);
                $campaign['message_count'] = $lc->getSentMessageByCampaign($campaign->id);
                $campaign['email_action_count'] = $lc->getSentEmailByCampaign($campaign->id);
            }
            $campaigns = $campaigns->values();
            $uc = new UnipileController();
            $request = ['account_id' => $linkedin_integrations['account_id'], 'profile_url' => session('linkedin_profile')['provider_id'],];
            $profile = $uc->view_profile(new \Illuminate\Http\Request($request))->getData(true);
            $data = [
                'title' => 'Dashboard - Campaign',
                'team' => $team,
                'seat' => $seat,
                'campaigns' => $campaigns,
                'profile' => $profile['user_profile'],
                'total_profile_views' => $total_profile_views,
            ];
            return view('back.campaign', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function createCampaign($slug, $seat_slug)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $data = [
                'title' => 'Dashboard - Create Campaign',
                'team' => $team,
                'seat' => $seat,
            ];
            return view('back.campaigncreate', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function campaignInfo($slug, $seat_slug, Request $request)
    {
        try {
            /* Validate the file input to ensure it's a CSV or TXT file. */
            $validator = Validator::make($request->all(), [
                'campaign_name' => 'required|string|max:255',
                'campaign_url' => 'required'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->withInput();
            }

            $all = $request->except('_token');
            if ($all['campaign_type'] == 'linkedin' && strpos($all['campaign_url'], 'https://www.linkedin.com/search/results/people') === false) {
                return redirect()->back()->withErrors(['campaign_url' => 'Invalid URL for LinkedIn search'])->withInput();
            } else if ($all['campaign_type'] == 'sales_navigator' && strpos($all['campaign_url'], 'https://www.linkedin.com/sales/search/people') === false) {
                return redirect()->back()->withErrors(['campaign_url' => 'Invalid URL for Sales Navigator search'])->withInput();
            } else if ($all['campaign_type'] == 'leads_list' && strpos($all['campaign_url'], 'https://www.linkedin.com/sales/lists/people') === false) {
                return redirect()->back()->withErrors(['campaign_url' => 'Invalid URL for Lead List search'])->withInput();
            } else if ($all['campaign_type'] == 'post_engagement') {
                preg_match('/activity-([0-9]+)/', $all['campaign_url'], $matches);
                if (strpos($all['campaign_url'], 'https://www.linkedin.com/posts') === false) {
                    return redirect()->back()->withErrors(['campaign_url' => 'Invalid URL for Posts'])->withInput();
                } else if (!isset($matches[1])) {
                    return redirect()->back()->withErrors(['campaign_url' => 'Post must be activity'])->withInput();
                }
            }
            $uc = new UnipileController();
            $seat = Seat::where('slug', $seat_slug)->first();
            $team = Team::where('slug', $slug)->first();
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
            $campaign_details = [];
            foreach ($all as $key => $value) {
                $campaign_details[$key] = $value;
            }
            $schedules = Schedule::where('seat_id', $seat->id)->get();
            $seats = Seat::where('team_id', $team->id)->get();
            $team_schedules = Schedule::whereIn('seat_id', $seats->pluck('id')->toArray())->get();
            $data = [
                'title' => 'Dashboard - Campaign Info',
                'campaign_details' => $campaign_details,
                'emails' => $emails,
                'team' => $team,
                'seat' => $seat,
                'schedules' => $schedules,
                'team_schedules' => $team_schedules,
            ];
            return view('back.campaigninfo', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function fromscratch($slug, $seat_slug, Request $request)
    {
        try {
            $seat = Seat::where('slug', $seat_slug)->first();
            $team = Team::where('slug', $slug)->first();
            $all = $request->except('_token');
            $settings = [];
            foreach ($all as $key => $value) {
                $settings[$key] = $value;
            }
            $data = [
                'campaigns' => Element::where('is_conditional', '0')->orderBy('id')->get(),
                'conditional_campaigns' => Element::where('is_conditional', '1')->orderBy('id')->get(),
                'title' => 'Dashboard - Campaign From Scratch',
                'settings' => $settings,
                'team' => $team,
                'seat' => $seat,
            ];
            return view('back.fromscratch', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function archiveCampaign($slug, $seat_slug, $campaign_id)
    {
        try {
            $campaign = Campaign::where('id', $campaign_id)->first();
            if ($campaign->is_archive == 1) {
                $campaign->is_archive = 0;
                $campaign->save();
                return response()->json(['success' => true, 'archive' => $campaign->is_archive]);
            } else {
                $campaign->is_archive = 1;
                $campaign->save();
                return response()->json(['success' => true, 'archive' => $campaign->is_archive]);
            }
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function filterCampaign($slug, $seat_slug, $filter, $search)
    {
        try {
            $lc = new LeadsController();
            $seat = Seat::where('slug', $seat_slug)->first();
            $campaigns = Campaign::where('seat_id', $seat->id);
            if ($search != 'null') {
                $campaigns = $campaigns->where('name', 'LIKE', '%' . $search . '%');
            }
            if ($filter == 'active') {
                $campaigns = $campaigns->where('is_active', 1)->where('is_archive', 0)->get();
            } else if ($filter == 'inactive') {
                $campaigns = $campaigns->where('is_active', 0)->where('is_archive', 0)->get();
            } else if ($filter == 'archive') {
                $campaigns = $campaigns->where('is_archive', 1)->get();
            }
            if (count($campaigns) != 0) {
                foreach ($campaigns as $campaign) {
                    $campaign['lead_count'] = $lc->getLeadsCountByCampaign($campaign->id);
                    $campaign['view_action_count'] = $lc->getViewProfileByCampaign($campaign->id);
                    $campaign['invite_action_count'] = $lc->getInviteToConnectByCampaign($campaign->id);
                    $campaign['message_count'] = $lc->getSentMessageByCampaign($campaign->id);
                    $campaign['email_action_count'] = $lc->getSentEmailByCampaign($campaign->id);
                }
                $campaigns = $campaigns->sortByDesc('lead_count')->values();
                return response()->json(['success' => true, 'campaigns' => $campaigns]);
            } else {
                return response()->json(['success' => false, 'campaigns' => 'Campaign not Found']);
            }
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function changeCampaignStatus($slug, $seat_slug, $campaign_id)
    {
        try {
            $campaign = Campaign::where('id', $campaign_id)->first();
            if ($campaign->is_active == 1) {
                $campaign->is_active = 0;
                $campaign->save();
                return response()->json(['success' => true, 'active' => $campaign->is_active]);
            } else {
                $campaign->is_active = 1;
                $campaign->save();
                return response()->json(['success' => true, 'active' => $campaign->is_active]);
            }
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function deleteCampaign($slug, $seat_slug, $campaign_id)
    {
        try {
            $campaign = Campaign::where('id', $campaign_id)->first();
            if ($campaign) {
                Linkedin_Setting::where('campaign_id', $campaign->id)->delete();
                Global_Setting::where('campaign_id', $campaign->id)->delete();
                Email_Setting::where('campaign_id', $campaign->id)->delete();
                $elements = Campaign_Element::where('campaign_id', $campaign->id)->get();
                if ($elements) {
                    foreach ($elements as $element) {
                        Campaign_Property::where('campaign_element_id', $element->id)->delete();
                        Campaign_Path::where('current_element_id', $element->id)->delete();
                        $element->delete();
                    }
                }
                $campaign->delete();
                return response()->json(['success' => true]);
            }
            return response()->json(['error' => 'Campaign not found'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function getCampaignDetails($slug, $seat_slug, $campaign_id)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $data = [
                'title' => 'Dashboard - Campaign Details',
                'team' => $team,
                'seat' => $seat,
                'campaign' => Campaign::where('id', $campaign_id)->first(),
            ];
            return view('back.campaignDetails', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function editCampaign($slug, $seat_slug, $campaign_id)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $campaign = Campaign::where('id', $campaign_id)->first();
            $data = [
                'title' => 'Dashboard - Edit Campaign',
                'campaign' => $campaign,
                'seat' => $seat,
                'team' => $team,
            ];
            return view('back.editCampaign', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function editCampaignInfo($slug, $seat_slug, Request $request, $campaign_id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'campaign_name' => 'required|string|max:255',
                'campaign_url' => 'required'
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->withInput();
            }
            $all = $request->except('_token');
            if ($all['campaign_type'] == 'linkedin' && strpos($all['campaign_url'], 'https://www.linkedin.com/search/results/people') === false) {
                return redirect()->back()->withErrors(['campaign_url' => 'Invalid URL for LinkedIn search'])->withInput();
            } else if ($all['campaign_type'] == 'sales_navigator' && strpos($all['campaign_url'], 'https://www.linkedin.com/sales/search/people') === false) {
                return redirect()->back()->withErrors(['campaign_url' => 'Invalid URL for Sales Navigator search'])->withInput();
            } else if ($all['campaign_type'] == 'leads_list' && strpos($all['campaign_url'], 'https://www.linkedin.com/sales/lists/people') === false) {
                return redirect()->back()->withErrors(['campaign_url' => 'Invalid URL for Lead List search'])->withInput();
            } else if ($all['campaign_type'] == 'post_engagement') {
                preg_match('/activity-([0-9]+)/', $all['campaign_url'], $matches);
                if (strpos($all['campaign_url'], 'https://www.linkedin.com/posts') === false) {
                    return redirect()->back()->withErrors(['campaign_url' => 'Invalid URL for Posts'])->withInput();
                } else if (!isset($matches[1])) {
                    return redirect()->back()->withErrors(['campaign_url' => 'Post must be activity'])->withInput();
                }
            }
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $seats = Seat::where('team_id', $team->id)->get();
            $team_schedules = Schedule::whereIn('seat_id', $seats->pluck('id')->toArray())->get();
            $email_settings = Email_Setting::where('campaign_id', $campaign_id)->get();
            $linkedin_settings = Linkedin_Setting::where('campaign_id', $campaign_id)->get();
            $global_settings = Global_Setting::where('campaign_id', $campaign_id)->get();
            $schedules = Schedule::orWhere('seat_id', $seat->id)->get();
            $all = $request->except('_token');
            $campaign_details = [];
            foreach ($all as $key => $value) {
                $campaign_details[$key] = $value;
            }
            $uc = new UnipileController();
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
            $data = [
                'title' => 'Dashboard - Campaign Info',
                'email_settings' => $email_settings,
                'linkedin_settings' => $linkedin_settings,
                'global_settings' => $global_settings,
                'campaign_details' => $campaign_details,
                'campaign_schedule' => $schedules,
                'campaign_id' => $campaign_id,
                'team' => $team,
                'seat' => $seat,
                'team_schedule' => $team_schedules,
                'emails' => $emails,
            ];
            return view('back.editCampaignInfo', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    function updateCampaign($slug, $seat_slug, Request $request, $campaign_id)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $settings = $request->except('_token');
            $campaign = Campaign::where('id', $campaign_id)->first();
            $campaign->name = $settings['campaign_name'];
            unset($settings['campaign_name']);
            $campaign->type = $settings['campaign_type'];
            unset($settings['campaign_type']);
            if (!empty($settings['campaign_url'])) {
                $campaign->url = $settings['campaign_url'];
                unset($settings['campaign_url']);
            }
            if (!empty($settings['campaign_connection'])) {
                $campaign->connection = $settings['campaign_connection'];
                unset($settings['campaign_connection']);
            }
            $campaign->save();
            if ($campaign->id) {
                foreach ($settings as $key => $value) {
                    if (str_contains($key, 'email_settings_')) {
                        $str_key = str_replace('email_settings_', '', $key);
                        $setting = Email_Setting::where('id', $str_key)->where('campaign_id', $campaign_id)->first();
                    }
                    if (str_contains($key, 'linkedin_settings_')) {
                        $str_key = str_replace('linkedin_settings_', '', $key);
                        $setting = Linkedin_Setting::where('id', $str_key)->where('campaign_id', $campaign_id)->first();
                    }
                    if (str_contains($key, 'global_settings_')) {
                        $str_key = str_replace('global_settings_', '', $key);
                        $setting = Global_Setting::where('id', $str_key)->where('campaign_id', $campaign_id)->first();
                    }
                    $setting->value = $value;
                    $setting->save();
                }
                return redirect()->route('campaignPage', ['slug' => $team->slug, 'seat_slug' => $seat->slug])
                    ->with(['success' => 'Campaign succesfully updated!']);
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
