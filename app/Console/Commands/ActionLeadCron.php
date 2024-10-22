<?php

namespace App\Console\Commands;

use App\Http\Controllers\CronController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\SeatController;
use App\Models\Campaign;
use App\Models\Campaign_Element;
use App\Models\Campaign_Path;
use App\Models\Campaign_Property;
use App\Models\Element;
use App\Models\Global_Limit;
use App\Models\Lead_Action;
use App\Models\Linkedin_Integration;
use App\Models\Properties;
use App\Models\Seat;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActionLeadCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'action:lead';
    protected $logFilePath = 'lead_action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for lead actions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::channel($this->logFilePath)->info('Action lead started successfully at ' . now());
        try {
            $sc = new SeatController();
            $final_accounts = $sc->get_final_accounts();
            foreach ($final_accounts as $final_account) {
                $linkedin_integrations = Linkedin_Integration::whereIn('account_id', $final_account)->get();
                $seat_ids = $linkedin_integrations->pluck('seat_id')->toArray();
                $seats = Seat::whereIn('id', $seat_ids)->get();
                foreach ($seats as $seat) {
                    $campaigns = Campaign::where('seat_id', $seat->id)->where('is_active', 1)->where('is_archive', 0)->get();
                    if ($campaigns->isNotEmpty()) {
                        $this->campaign_working($campaigns, $seat);
                    } else {
                        Log::channel($this->logFilePath)->info('No campaigns found for seats # (' . implode(', ', $seat_ids) . ')');
                    }
                }
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error during lead processing: ' . $e->getMessage());
        } finally {
            Log::channel($this->logFilePath)->info('Action lead completed at ' . now());
        }
    }

    private function campaign_working($campaigns, $seat)
    {
        Log::channel($this->logFilePath)->info('Working on campaigns started succesfully at ' . now());
        try {
            $current_time = now();
            $lc = new LeadsController();
            $view_distribution_limit = $lc->get_view_count($campaigns, $seat);
            $invitation_distribution_limit = $lc->get_invite_count($campaigns, $seat);
            $message_distribution_limit = $lc->get_message_count($campaigns, $seat);
            $follow_distribution_limit = $lc->get_follow_count($campaigns, $seat);
            $inmail_distribution_limit = $lc->get_inmail_message_count($campaigns, $seat);
            $email_distribution_limit = $lc->get_email_message_count($campaigns, $seat);
            foreach ($campaigns as $campaign) {
                $actions = Lead_Action::where('campaign_id', $campaign['id'])->where('status', 'inprogress')->get();
                if ($actions->isNotEmpty()) {
                    $view_count = 0;
                    $invite_count = 0;
                    $message_count = 0;
                    $follow_count = 0;
                    $inmail_message_count = 0;
                    $email_message_count = 0;
                    foreach ($actions as $action) {
                        $success = false;
                        try {
                            $conditional_output = '';
                            if ($action['current_element_id'] != null) {
                                $campaign_element = Campaign_Element::where('id', $action['current_element_id'])->first();
                                $element = Element::where('id', $campaign_element['element_id'])->first();
                                $seat = Seat::where('id', $campaign['seat_id'])->first();
                                $linkedin_integration = Linkedin_Integration::where('seat_id', $seat->id)->first();
                                $account_id = $linkedin_integration['account_id'];
                                if ($current_time <= $action['ending_time']) {
                                    $cc = new CronController();
                                    switch ($element['slug']) {
                                        case 'view_profile':
                                            if ($view_count < $view_distribution_limit) {
                                                $success = $cc->view_profile($action, $account_id);
                                                if ($success) {
                                                    Log::channel($this->logFilePath)->info('Profile Viewed successfully');
                                                }
                                                $view_count++;
                                            } else {
                                                Log::channel($this->logFilePath)->info('Profile View Limitation reached');
                                            }
                                            break;
                                        case 'follow':
                                            if ($follow_count < $follow_distribution_limit) {
                                                $success = $cc->follow($action, $account_id);
                                                if ($success) {
                                                    Log::channel($this->logFilePath)->info('Follow user successfully');
                                                }
                                                $follow_count++;
                                            } else {
                                                Log::channel($this->logFilePath)->info('Follow Limitation reached');
                                            }
                                            break;
                                        case 'message':
                                            if ($message_count < $message_distribution_limit) {
                                                $success = $cc->message($action, $account_id, $element, $campaign_element);
                                                if ($success) {
                                                    Log::channel($this->logFilePath)->info('Message sent successfully');
                                                }
                                                $message_count++;
                                            } else {
                                                Log::channel($this->logFilePath)->info('Message Limitation reached');
                                            }
                                            break;
                                        case 'invite_to_connect':
                                            if ($invite_count < $invitation_distribution_limit) {
                                                $success = $cc->invite_to_connect($action, $account_id, $element, $campaign_element);
                                                if ($success) {
                                                    Log::channel($this->logFilePath)->info('Invitation to connect sent successfully');
                                                }
                                                $invite_count++;
                                            } else {
                                                Log::channel($this->logFilePath)->info('Invitation Limitation reached');
                                            }
                                            break;
                                        case 'inmail_message':
                                            if ($inmail_message_count < $inmail_distribution_limit) {
                                                $success = $cc->inmail_message($action, $account_id, $element, $campaign_element);
                                                if ($success) {
                                                    Log::channel($this->logFilePath)->info('Inmail message sent successfully');
                                                }
                                            } else {
                                                Log::channel($this->logFilePath)->info('Inmail message Limitation reached');
                                            }
                                            break;
                                        case 'email_message':
                                            if ($email_message_count < $email_distribution_limit) {
                                                $success = $cc->email_message($action, $account_id, $element, $campaign_element);
                                                if ($success) {
                                                    Log::channel($this->logFilePath)->info('Email sent successfully');
                                                }
                                            } else {
                                                Log::channel($this->logFilePath)->info('Email message Limitation reached');
                                            }
                                            break;
                                        case 'if_connected':
                                            $conditional_output = $cc->if_connected($action, $account_id);
                                            if ($conditional_output == 'true') {
                                                Log::channel($this->logFilePath)->info('Lead is already connected');
                                            } else {
                                                Log::channel($this->logFilePath)->info('Lead is not connected');
                                            }
                                            $success = true;
                                            break;
                                        case 'if_email_is_opened':
                                            $conditional_output = $cc->if_email_is_opened($action);
                                            if ($conditional_output == 'true') {
                                                Log::channel($this->logFilePath)->info('Email is already opened');
                                            } else {
                                                Log::channel($this->logFilePath)->info('Email is not opened');
                                            }
                                            $success = true;
                                            break;
                                        case 'if_has_imported_email':
                                            $conditional_output = $cc->if_has_imported_email($action);
                                            if ($conditional_output == 'true') {
                                                Log::channel($this->logFilePath)->info('Email is imported successfully');
                                            } else {
                                                Log::channel($this->logFilePath)->info('Email is not imported');
                                            }
                                            $success = true;
                                            break;
                                        case 'if_has_verified_email':
                                            $conditional_output = $cc->if_has_verified_email($action);
                                            if ($conditional_output == 'true') {
                                                Log::channel($this->logFilePath)->info('Email is already verified');
                                            } else {
                                                Log::channel($this->logFilePath)->info('Email is not verified');
                                            }
                                            $success = true;
                                            break;
                                        case 'if_free_inmail':
                                            $conditional_output = $cc->if_free_inmail($action);
                                            if ($conditional_output == 'true') {
                                                Log::channel($this->logFilePath)->info('Inmail is free');
                                            } else {
                                                Log::channel($this->logFilePath)->info('Inmail is not free');
                                            }
                                            $success = true;
                                            break;
                                        default:
                                            Log::channel($this->logFilePath)->info('Unknown action ' . $element['slug'] . ' of campaign # ' . $campaign['id']);
                                            break;
                                    }
                                }
                            } else {
                                $campaign_element = Campaign_Element::where('id', $action['next_true_element_id'])->first();
                                $element = Element::where('id', $campaign_element['element_id'])->first();
                            }
                        } catch (Exception $e) {
                            Log::channel($this->logFilePath)->error('Error in campaign processing: ' . $e->getMessage());
                        }
                        if ($success || $action['current_element_id'] == null || $current_time > $action['ending_time']) {
                            $action['status'] = 'completed';
                            $action['updated_at'] = now();
                            $action->save();
                            $this->handleNextActions($action, $element, $conditional_output, $campaign);
                        }
                    }
                } else {
                    Log::channel($this->logFilePath)->info('No actions found for campaigns # (' . $campaign['id'] . ')');
                }
            }
        } catch (Exception $e) {
            Log::channel($this->logFilePath)->error('Error in campaign processing: ' . $e->getMessage());
        }
    }

    private function handleNextActions($action, $element, $conditional_output, $campaign)
    {
        if ($action['next_true_element_id'] != null || $action['next_false_element_id'] != null) {
            if ($element['is_conditional'] == 1) {
                if ($conditional_output == 'true' && $action['next_true_element_id'] != null) {
                    $this->createNewAction($action['next_true_element_id'], $action, $campaign);
                } else if ($conditional_output == 'false' && $action['next_false_element_id'] != null) {
                    $this->createNewAction($action['next_false_element_id'], $action, $campaign);
                }
            } else {
                $this->createNewAction($action['next_true_element_id'], $action, $campaign);
            }
        }
    }

    private function createNewAction($next_element_id, $action, $campaign)
    {
        $campaign_path = Campaign_Path::where('current_element_id', $next_element_id)->first();
        $new_action = new Lead_Action();
        $new_action['current_element_id'] = $next_element_id;
        if (!empty($campaign_path)) {
            $new_action['next_true_element_id'] = $campaign_path['next_true_element_id'];
            $new_action['next_false_element_id'] = $campaign_path['next_false_element_id'];
        } else {
            $new_action['next_true_element_id'] = null;
            $new_action['next_false_element_id'] = null;
        }
        $new_action['lead_id'] = $action['lead_id'];
        $new_action['created_at'] = now();
        $new_action['updated_at'] = now();
        $new_action['campaign_id'] = $campaign['id'];
        $new_action['status'] = 'inprogress';
        $properties = Campaign_Property::where('campaign_element_id', $new_action['current_element_id'])->get();
        $time = now();
        foreach ($properties as $property) {
            $campaign_property = Properties::where('id', $property['property_id'])->first();
            if (!empty($campaign_property) && isset($property['value'])) {
                $timeToAdd = intval($property['value']);
                if ($campaign_property['property_name'] == 'Hours') {
                    $time->modify('+' . $timeToAdd . ' hours');
                } else if ($campaign_property['property_name'] == 'Days') {
                    $time->modify('+' . $timeToAdd . ' days');
                }
            }
        }
        $new_action['ending_time'] = $time->format('Y-m-d H:i:s');
        $new_action->save();
    }
}
