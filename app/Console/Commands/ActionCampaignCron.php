<?php

namespace App\Console\Commands;

use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\UnipileController;
use App\Models\Campaign;
use App\Models\Global_Limit;
use App\Models\Lead;
use App\Models\Linkedin_Integration;
use App\Models\Linkedin_Setting;
use App\Models\Seat;
use App\Models\Team;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActionCampaignCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'action:campaign';
    protected $logFilePath = 'campaign_action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for campaign actions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::channel($this->logFilePath)->info('Action campaign started successfully at ' . now());
        try {
            $sc = new SeatController();
            $final_accounts = $sc->get_final_accounts();
            foreach ($final_accounts as $final_account) {
                $linkedin_integrations = Linkedin_Integration::whereIn('account_id', $final_account)->get();
                $seat_ids = $linkedin_integrations->pluck('seat_id')->toArray();
                $seats = Seat::whereIn('id', $seat_ids)->get();
                foreach ($seats as $seat) {
                    $discover = Global_Limit::where('seat_id', $seat->id)->where('health_slug', 'discover')->first();
                    $campaigns = Campaign::where('seat_id', $seat->id)->where('is_active', 1)->where('is_archive', 0)->get();
                    if ($campaigns->isNotEmpty()) {
                        $this->campaign_working($campaigns, $discover->value);
                    } else {
                        Log::channel($this->logFilePath)->info('No campaigns found for seats # (' . implode(', ', $seat_ids) . ')');
                    }
                }
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error during campaign processing: ' . $e->getMessage());
        } finally {
            Log::channel($this->logFilePath)->info('Action campaign completed at ' . now());
        }
    }

    private function campaign_working($campaigns, $remain_distribution_limit = 80, $campaignFunctionCount = 0)
    {
        Log::channel($this->logFilePath)->info('Working on campaigns ' . ++$campaignFunctionCount . ' times started successfully at ' . now());
        try {
            if ($remain_distribution_limit < count($campaigns)) {
                $campaigns = array_slice($campaigns, 0, $remain_distribution_limit);
            }
            $lead_distribution_limit = $remain_distribution_limit > 0 ? floor($remain_distribution_limit / count($campaigns)) : 0;
            $remain_distribution_limit = 0;
            $campaignsToRemove = [];
            foreach ($campaigns as $index => $campaign) {
                $remain_distribution_limit = $this->processCampaignByType($campaign, $remain_distribution_limit, $lead_distribution_limit);
                if ($remain_distribution_limit > 0) {
                    $campaignsToRemove[] = $index;
                }
            }
            foreach ($campaignsToRemove as $index) {
                unset($campaigns[$index]);
            }
            if ($campaigns->isNotEmpty() && $remain_distribution_limit > 0) {
                $this->campaign_working($campaigns, $remain_distribution_limit, $campaignFunctionCount);
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error in campaign processing: ' . $e->getMessage());
        }
    }

    private function processCampaignByType($campaign, $remain_distribution_limit, $lead_distribution_limit)
    {
        switch ($campaign['type']) {
            case 'linkedin':
                return $this->linkedinLeads($campaign, ($remain_distribution_limit + $lead_distribution_limit));
            case 'sales_navigator':
                return $this->salesLeads($campaign, ($remain_distribution_limit + $lead_distribution_limit));
            case 'recruiter':
                return $this->leadList($campaign, ($remain_distribution_limit + $lead_distribution_limit));
            case 'import':
                return $this->importLeads($campaign, ($remain_distribution_limit + $lead_distribution_limit));
            case 'post_engagement':
                return $this->postLeads($campaign, ($remain_distribution_limit + $lead_distribution_limit));
            case 'leads_list':
                return $this->leadList($campaign, ($remain_distribution_limit + $lead_distribution_limit));
            default:
                Log::channel($this->logFilePath)->info('Unknown campaign type ' . $campaign['type'] . ' of campaign # ' . $campaign['id']);
                return $remain_distribution_limit;
        }
    }

    private function linkedinLeads($campaign, $lead_distribution_limit, $leadCount = 0, $searchedLeadCount = 0)
    {
        Log::channel($this->logFilePath)->info('Searching Linkedin Leads started succesfully at ' . now());
        try {
            $integrated_linkedin = Linkedin_Integration::where('seat_id', $campaign->seat_id)->first();
            $seat = Seat::where('id', $campaign->seat_id)->first();
            $account_id = $integrated_linkedin->account_id;
            $request = [
                'account_id' => $account_id,
                'query' => $this->extractLinkedinParams($campaign->url),
                'start' => $searchedLeadCount
            ];
            $k = 0;
            $uc = new UnipileController();
            $linkedin_search = $uc->linkedin_search(new \Illuminate\Http\Request($request))->getData(true);
            $team = Team::where('id', $seat->team_id)->first();
            if (!isset($linkedin_search['error'])) {
                $searches = $linkedin_search['accounts'];
                if (count($searches) > 0) {
                    foreach ($searches as $search) {
                        $items = $search['items'];
                        foreach ($items as $item) {
                            if (isset($item['item']['entityResult'])) {
                                $result = $item['item']['entityResult'];
                                $k++;
                                if ($leadCount >= $lead_distribution_limit) {
                                    break 2;
                                }
                                $profileUrl = str_replace('urn:li:fsd_entityResultViewModel:(urn:li:fsd_profile:', '', $result['entityUrn']);
                                $index = strpos($profileUrl, ',');
                                if ($index !== false) {
                                    $profileUrl = substr($profileUrl, 0, $index);
                                }
                                $request = [
                                    'account_id' => $account_id,
                                    'profile_url' => $profileUrl,
                                ];
                                $profile = $uc->view_profile(new \Illuminate\Http\Request($request))->getData(true);
                                Log::channel($this->logFilePath)->info('Profile: ' . $profile);
                                if (!isset($profile['error'])) {
                                    $profile = $profile['user_profile'];
                                    if (strpos($profile['public_identifier'], 'https://www.linkedin.com/in/') !== false) {
                                        $url = $profile['public_identifier'];
                                    } else {
                                        $url = 'https://www.linkedin.com/in/' . $profile['public_identifier'];
                                    }
                                    $lead = Lead::where('campaign_id', $campaign['id'])->where('profileUrl', $url)->first();
                                    if (empty($lead) && $leadCount < $lead_distribution_limit) {
                                        $bc = new BlacklistController();
                                        $lc = new LeadsController();
                                        if ($bc->applyBlacklist($team, $url, $profile)) {
                                            if ($lc->applySettings($campaign, $url, $profile)) {
                                                if ($this->insertLeadToDB($url, $campaign, $profile)) {
                                                    $leadCount++;
                                                    Log::channel($this->logFilePath)->error('Lead inserted succesfully');
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    Log::channel($this->logFilePath)->error('Error in fetching searches: ' . $profile['error']);
                                }
                            }
                        }
                    }
                    if ($leadCount < $lead_distribution_limit) {
                        return $this->linkedinLeads($campaign, $lead_distribution_limit, $leadCount, ($k + $searchedLeadCount));
                    }
                } else if ($leadCount < $lead_distribution_limit) {
                    Log::channel($this->logFilePath)->error('No more searches found');
                }
                if ($leadCount >= $lead_distribution_limit) {
                    Log::channel($this->logFilePath)->error('Limitation reached');
                }
            } else {
                Log::channel($this->logFilePath)->error('Error in fetching searches: ' . $linkedin_search['error']);
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error in linkedin leads processing: ' . $e->getMessage());
        } finally {
            return $lead_distribution_limit - $leadCount;
        }
    }

    private function extractLinkedinParams($url)
    {
        $queryString = parse_url($url, PHP_URL_QUERY);
        parse_str($queryString, $params);
        $query = [];
        foreach ($params as $key => $value) {
            $decodedValue = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if ($key === 'keywords' && is_string($decodedValue)) {
                    $query[$key] = rawurlencode(trim($decodedValue));
                } else {
                    $query[$key] = $decodedValue;
                }
            } else {
                $query[$key] = rawurlencode(trim($value));
            }
        }
        return $query;
    }

    private function insertLeadToDB($url, $campaign, $profile)
    {
        try {
            $lead = new Lead();
            $lead->is_active = 1;
            $lead->campaign_id = $campaign->id;
            $lead->send_connections = 'discovered';
            $lead->profileUrl = $url;
            $lead->provider_id = $profile['provider_id'];
            $lead->executed_time = date('H:i:s');
            $lead->created_at = now();
            $lead->updated_at = now();
            if (isset($profile['first_name']) && isset($profile['last_name'])) {
                $name = $profile['first_name'] . ' ' . $profile['last_name'];
                $name = ucwords($name);
                $lead->title_company = $name;
            } else if (isset($profile['name'])) {
                $name = $profile['name'];
                $lead->title_company = $name;
            }
            if (
                Linkedin_Setting::where('campaign_id', $campaign->id)
                ->where('setting_slug', 'linkedin_settings_collect_contact_information')
                ->value('value') === 'yes'
            ) {
                if (isset($profile['contact_info']['phones'][0])) {
                    $lead->contact = $profile['contact_info']['phones'][0];
                }
                if (isset($profile['contact_info']['emails'][0])) {
                    $lead->email = $profile['contact_info']['emails'][0];
                }
                if (isset($profile['contact_info']['adresses'][0])) {
                    $lead->address = $profile['contact_info']['adresses'][0];
                }
                if (isset($profile['websites'][0])) {
                    $lead->website = $profile['websites'][0];
                }
            }
            $lead->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
