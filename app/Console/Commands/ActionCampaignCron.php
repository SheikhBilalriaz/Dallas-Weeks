<?php

namespace App\Console\Commands;

use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\UnipileController;
use App\Models\Campaign;
use App\Models\Campaign_Path;
use App\Models\Global_Limit;
use App\Models\Lead;
use App\Models\Lead_Action;
use App\Models\Linkedin_Integration;
use App\Models\Linkedin_Setting;
use App\Models\Seat;
use App\Models\Team;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\CsvController;

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
                        $this->campaign_working($campaigns, 5);
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
                $campaigns = $campaigns->take($remain_distribution_limit);
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
                                        if ($bc->applyBlacklist($team, $url, $profile) && $lc->applySettings($campaign, $url, $profile)) {
                                            if ($this->insertLeadToDB($url, $campaign, $profile)) {
                                                $leadCount++;
                                                Log::channel($this->logFilePath)->error('Lead inserted succesfully');
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
            if ($lead->id) {
                $lead_action = new Lead_Action();
                $campaign_path = Campaign_Path::where('campaign_id', $campaign->id)->first();
                $lead_action->current_element_id = null;
                $lead_action->next_true_element_id = $campaign_path->current_element_id;
                $lead_action->next_false_element_id = null;
                $lead_action->status = 'inprogress';
                $lead_action->lead_id = $lead->id;
                $lead_action->ending_time = now();
                $lead_action->campaign_id = $campaign->id;
                $lead_action->created_at = now();
                $lead_action->updated_at = now();
                $lead_action->save();
                if (isset($lead_action->id)) {
                    return true;
                }
                $lead->delete();
                return false;
            }
            $lead->delete();
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }

    private function importLeads($campaign, $lead_distribution_limit, $leadCount = 0)
    {
        Log::channel($this->logFilePath)->info('Searching Import Leads started succesfully at ' . now());
        try {
            $integrated_linkedin = Linkedin_Integration::where('seat_id', $campaign->seat_id)->first();
            $seat = Seat::where('id', $campaign->seat_id)->first();
            $account_id = $integrated_linkedin->account_id;
            $team = Team::where('id', $seat->team_id)->first();
            $csvController = new CsvController();
            $csvData = $csvController->importedLeadToArray($campaign->url);
            $have_url = false;
            $uc = new UnipileController();
            if ($csvData !== NULL) {
                foreach ($csvData as $key => $value) {
                    if (str_contains(strtolower($key), 'url')) {
                        $have_url = true;
                        foreach ($value as $profileUrl) {
                            try {
                                if ($leadCount >= $lead_distribution_limit) {
                                    break 2;
                                }
                                $request = [
                                    'account_id' => $account_id,
                                    'profile_url' => $profileUrl,
                                ];
                                $profile = $uc->view_profile(new \Illuminate\Http\Request($request))->getData(true);
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
                                                    Log::channel($this->logFilePath)->info('Lead inserted succesfully');
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    Log::channel($this->logFilePath)->error('Error in fetching searches: ' . $profile['error']);
                                }
                            } catch (Exception $e) {
                                Log::channel($this->logFilePath)->error('Error in fetching leads: ' . $e);
                            }
                        }
                    }
                }
                if (!$have_url) {
                    Log::channel($this->logFilePath)->error('No URL column found');
                }
                if ($leadCount < $lead_distribution_limit) {
                    Log::channel($this->logFilePath)->error('No more searches found');
                } else {
                    Log::channel($this->logFilePath)->error('Limitation reached');
                }
            } else {
                Log::channel($this->logFilePath)->error('No data in csv file');
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error in import leads processing: ' . $e->getMessage());
        } finally {
            return $lead_distribution_limit - $leadCount;
        }
    }

    private function salesLeads($campaign, $lead_distribution_limit, $leadCount = 0, $searchedLeadCount = 0)
    {
        Log::channel($this->logFilePath)->info('Searching Import Leads started succesfully at ' . now());
        try {
            $integrated_linkedin = Linkedin_Integration::where('seat_id', $campaign->seat_id)->first();
            $seat = Seat::where('id', $campaign->seat_id)->first();
            $account_id = $integrated_linkedin->account_id;
            $team = Team::where('id', $seat->team_id)->first();
            $uc = new UnipileController();
            $query = '';
            $url = $campaign->url;
            $parsed_url = parse_url($url);
            $query_string = isset($parsed_url['query']) ? $parsed_url['query'] : '';
            parse_str($query_string, $params);
            $query = isset($params['query']) ? $params['query'] : null;
            $request = [
                'account_id' => $account_id,
                'query' => $query,
                'count' => 80,
                'start' => $searchedLeadCount
            ];
            $sales_navigator_search = $uc->sales_navigator_search(new \Illuminate\Http\Request($request))->getData(true);
            if (!isset($sales_navigator_search['error'])) {
                $searches = $sales_navigator_search['accounts'];
                if (count($searches) > 0) {
                    foreach ($searches as $search) {
                        try {
                            if ($leadCount >= $lead_distribution_limit) {
                                break;
                            }
                            $profileUrl = str_replace('urn:li:fs_salesProfile:(', '', $search['entityUrn']);
                            $index = strpos($profileUrl, ',');
                            if ($index !== false) {
                                $profileUrl = substr($profileUrl, 0, $index);
                            }
                            $request = [
                                'account_id' => $account_id,
                                'profile_url' => $profileUrl,
                                'sales_navigator' => true,
                            ];
                            $profile = $uc->view_profile(new \Illuminate\Http\Request($request))->getData(true);
                            if (!isset($profile['error'])) {
                                $profile = $profile['user_profile'];
                                $url = $profile['public_profile_url'];
                                $lead = Lead::where('campaign_id', $campaign['id'])->where('profileUrl', $url)->first();
                                if (empty($lead) && $leadCount < $lead_distribution_limit) {
                                    $bc = new BlacklistController();
                                    $lc = new LeadsController();
                                    if ($bc->applyBlacklist($team, $url, $profile)) {
                                        if ($lc->applySettings($campaign, $url, $profile)) {
                                            if ($this->insertLeadToDB($url, $campaign, $profile)) {
                                                $leadCount++;
                                                Log::channel($this->logFilePath)->info('Lead inserted succesfully');
                                            }
                                        }
                                    }
                                }
                            } else {
                                Log::channel($this->logFilePath)->error('Error in fetching searches: ' . $profile['error']);
                            }
                        } catch (Exception $e) {
                            Log::channel($this->logFilePath)->error('Error in sales leads processing: ' . $e->getMessage());
                        }
                    }
                    if ($leadCount < $lead_distribution_limit) {
                        return $this->salesLeads($campaign, $lead_distribution_limit, $leadCount, (count($searches) + $searchedLeadCount));
                    }
                } else if ($leadCount < $lead_distribution_limit) {
                    Log::channel($this->logFilePath)->error('No more searches found');
                }
                if ($leadCount >= $lead_distribution_limit) {
                    Log::channel($this->logFilePath)->error('Limitation reached');
                }
            } else {
                Log::channel($this->logFilePath)->error('Error in fetching searches: ' . $sales_navigator_search['error']);
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error in sales leads processing: ' . $e->getMessage());
        } finally {
            return $lead_distribution_limit - $leadCount;
        }
    }

    private function postLeads($campaign, $lead_distribution_limit, $leadCount = 0, $searchedLeadCount = 0, $cursor = null, $is_comment = false)
    {
        Log::channel($this->logFilePath)->info('Searching Post Leads started succesfully at ' . now());
        try {
            $integrated_linkedin = Linkedin_Integration::where('seat_id', $campaign->seat_id)->first();
            $seat = Seat::where('id', $campaign->seat_id)->first();
            $team = Team::where('id', $seat->team_id)->first();
            $account_id = $integrated_linkedin->account_id;
            $matches = array();
            $url = $campaign->url;
            preg_match('/activity-([0-9]+)/', $url, $matches);
            if (isset($matches[1])) {
                $request = [
                    'account_id' => $account_id,
                    'identifier' => $matches[1]
                ];
                $uc = new UnipileController();
                if (!isset($post_search)) {
                    $post_search = $uc->post_search(new \Illuminate\Http\Request($request))->getData(true);
                }
                $post = $post_search['post'];
                if (count($post) > 0) {
                    $request = [
                        'account_id' => $account_id,
                        'identifier' => $post['social_id'],
                        'cursor' => $cursor
                    ];
                    if ($is_comment) {
                        $response_post_search = $uc->comments_post_search(new \Illuminate\Http\Request($request))->getData(true);
                        if (count($response_post_search['reactions']['items']) > 0) {
                            $paging['cursor'] = $response_post_search['reactions']['cursor'];
                        } else {
                            $paging['cursor'] = null;
                        }
                        $reactions = $response_post_search['reactions']['items'];
                    } else {
                        $response_post_search = $uc->reactions_post_search(new \Illuminate\Http\Request($request))->getData(true);
                        $paging = $response_post_search['reactions']['paging'];
                        $reactions = $response_post_search['reactions']['items'];
                    }
                    if (count($reactions) > 0) {
                        foreach ($reactions as $reaction) {
                            try {
                                if ($leadCount >= $lead_distribution_limit) {
                                    break;
                                }
                                if ($is_comment) {
                                    $author = $reaction['author_details'];
                                } else {
                                    $author = $reaction['author'];
                                }
                                $request = [
                                    'account_id' => $account_id,
                                    'profile_url' => $author['id'],
                                ];
                                $profile = $uc->view_profile(new \Illuminate\Http\Request($request))->getData(true);
                                if (!isset($profile['error'])) {
                                    $profile = $profile['user_profile'];
                                    $conn = true;
                                    $connection_map = [
                                        1 => ['DISTANCE_1', 'FIRST_DEGREE'],
                                        2 => ['DISTANCE_2', 'SECOND_DEGREE'],
                                        3 => ['DISTANCE_3', 'THIRD_DEGREE']
                                    ];
                                    if (
                                        isset($connection_map[$campaign['connection']]) &&
                                        !in_array($author['network_distance'], $connection_map[$campaign['connection']])
                                    ) {
                                        $conn = false;
                                    }
                                    if ($conn) {
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
                                                        Log::channel($this->logFilePath)->info('Lead inserted succesfully');
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        Log::channel($this->logFilePath)->info('"' . $url . '" Connection is not ' . $campaign['connection']);
                                    }
                                } else {
                                    Log::channel($this->logFilePath)->error('Error in post leads processing: ' . $profile['error']);
                                }
                            } catch (Exception $e) {
                                Log::channel($this->logFilePath)->error('Error in post leads processing: ' . $e->getMessage());
                            }
                        }
                    }
                    if ($leadCount < $lead_distribution_limit) {
                        if (isset($paging['cursor'])) {
                            if (!$is_comment) {
                                file_put_contents($this->logFilePath, '     }' . PHP_EOL, FILE_APPEND);
                                return $this->postLeads($campaign, $lead_distribution_limit, $leadCount, (count($reactions) + $searchedLeadCount), $paging['cursor'], false, $post_search);
                            } else if ($is_comment) {
                                file_put_contents($this->logFilePath, '     }' . PHP_EOL, FILE_APPEND);
                                return $this->postLeads($campaign, $lead_distribution_limit, $leadCount, (count($reactions) + $searchedLeadCount), $paging['cursor'], true, $post_search);
                            }
                        } else if (!isset($paging['cursor'])) {
                            if (!$is_comment) {
                                file_put_contents($this->logFilePath, '     }' . PHP_EOL, FILE_APPEND);
                                return $this->postLeads($campaign, $lead_distribution_limit, $leadCount, (count($reactions) + $searchedLeadCount), null, true, $post_search);
                            }
                        } else {
                            file_put_contents($this->logFilePath, '         No more searches found' . PHP_EOL, FILE_APPEND);
                        }
                    } else {
                        Log::channel($this->logFilePath)->error('Limitation reached');
                    }
                } else if ($leadCount < $lead_distribution_limit) {
                    Log::channel($this->logFilePath)->error('No more searches found');
                }
            } else if ($leadCount < $lead_distribution_limit) {
                Log::channel($this->logFilePath)->error('No more searches found');
            }
            if ($leadCount >= $lead_distribution_limit) {
                Log::channel($this->logFilePath)->error('Limitation reached');
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error in post leads processing: ' . $e->getMessage());
        } finally {
            return $lead_distribution_limit - $leadCount;
        }
    }

    private function leadList($campaign, $lead_distribution_limit, $leadCount = 0, $cursor = null)
    {
        Log::channel($this->logFilePath)->info('Searching Lead list Leads started succesfully at ' . now());
        try {
            $integrated_linkedin = Linkedin_Integration::where('seat_id', $campaign->seat_id)->first();
            $seat = Seat::where('id', $campaign->seat_id)->first();
            $team = Team::where('id', $seat->team_id)->first();
            $account_id = $integrated_linkedin->account_id;
            $url = $campaign->url;
            $request = [
                'account_id' => $account_id,
                'search_url' => $url,
                'cursor' => $cursor
            ];
            $uc = new UnipileController();
            $lead_list_search = $uc->lead_list_search(new \Illuminate\Http\Request($request))->getData(true);
            $searches = $lead_list_search['accounts']['items'];
            $cursor = $lead_list_search['accounts']['cursor'];
            if (count($searches) > 0) {
                foreach ($searches as $search) {
                    if ($leadCount >= $lead_distribution_limit) {
                        break;
                    }
                    $request = [
                        'account_id' => $account_id,
                        'profile_url' => $search['public_profile_url'],
                    ];
                    $profile = $uc->view_profile(new \Illuminate\Http\Request($request))->getData(true);
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
                                        Log::channel($this->logFilePath)->info('Lead inserted succesfully');
                                    }
                                }
                            }
                        }
                    }
                }
                if ($leadCount < $lead_distribution_limit && !is_null($cursor)) {
                    return $this->leadList($campaign, $lead_distribution_limit, $leadCount, $cursor);
                }
            } else if ($leadCount < $lead_distribution_limit) {
                Log::channel($this->logFilePath)->error('No more searches found');
            }
            if ($leadCount >= $lead_distribution_limit) {
                Log::channel($this->logFilePath)->error('Limitation reached');
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error in lead list leads processing: ' . $e->getMessage());
        } finally {
            return $lead_distribution_limit - $leadCount;
        }
    }
}
