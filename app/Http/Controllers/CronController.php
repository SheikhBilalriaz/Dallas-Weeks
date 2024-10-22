<?php

namespace App\Http\Controllers;

use App\Models\Campaign_Property;
use App\Models\Email_Integraion;
use App\Models\Email_Setting;
use App\Models\Lead;
use App\Models\Properties;
use Exception;
use Illuminate\Http\Request;

class CronController extends Controller
{
    public function view_profile($action, $account_id)
    {
        try {
            $lead = Lead::where('id', $action->lead_id)->first();
            $url = $lead->profileUrl;
            $uc = new UnipileController();
            $profile = [
                'account_id' => $account_id,
                'profile_url' => $url,
                'notify' => true,
            ];
            $user_profile = $uc->view_profile(new \Illuminate\Http\Request($profile))->getData(true);
            if (!isset($user_profile['error'])) {
                $lead->send_connections = 'profile_viewed';
                $lead->updated_at = now();
                $lead->save();
                return true;
            } else {
                throw new Exception($user_profile['error']);
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function follow($action, $account_id)
    {
        try {
            $lead = Lead::where('id', $action->lead_id)->first();
            $url = $lead->profileUrl;
            $uc = new UnipileController();
            $profile = [
                'account_id' => $account_id,
                'profile_url' => $url,
            ];
            $user_profile = $uc->view_profile(new \Illuminate\Http\Request($profile))->getData(true);
            if (!isset($user_profile['error'])) {
                $user_profile = $user_profile['user_profile'];
                if (isset($user_profile['provider_id'])) {
                    $follow = [
                        'account_id' => $account_id,
                        'identifier' => $user_profile['provider_id'],
                    ];
                    $follow_user = $uc->follow(new \Illuminate\Http\Request($follow))->getData(true);
                    if (!isset($follow_user['error'])) {
                        $lead->send_connections = 'followed';
                        $lead->updated_at = now();
                        $lead->save();
                        return true;
                    } else {
                        throw new Exception($follow_user['error']);
                    }
                } else {
                    throw new Exception('User do not have provider_id');
                }
            } else {
                throw new Exception($user_profile['error']);
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function message($action, $account_id, $element, $campaign_element)
    {
        try {
            $lead = Lead::where('id', $action->lead_id)->first();
            $url = $lead->profileUrl;
            $uc = new UnipileController();
            $profile = [
                'account_id' => $account_id,
                'profile_url' => $url,
            ];
            $user_profile = $uc->view_profile(new \Illuminate\Http\Request($profile))->getData(true);
            if (!isset($user_profile['error'])) {
                $user_profile = $user_profile['user_profile'];
                if (isset($user_profile['provider_id']) && $user_profile['is_relationship'] === true) {
                    $message = [
                        'account_id' => $account_id,
                        'identifier' => $user_profile['provider_id'],
                    ];
                    if (isset($element) && isset($campaign_element)) {
                        $property = Properties::where('property_name', 'Message')->first();
                        $campaign_property = Campaign_Property::where('property_id', $property->id)
                            ->where('campaign_element_id', $campaign_element->id)->first();
                        if ($campaign_property) {
                            $message['message'] = $campaign_property->value;
                        }
                        $message = $uc->message(new \Illuminate\Http\Request($message))->getData(true);
                        if (!isset($message['error'])) {
                            $lead->send_connections = 'messaged';
                            $lead->updated_at = now();
                            $lead->save();
                            return true;
                        } else {
                            throw new Exception($message['error']);
                        }
                    } else {
                        if (!isset($element)) {
                            throw new Exception('Element is not saved');
                        } else {
                            throw new Exception('Campaign Element is not saved');
                        }
                    }
                } else {
                    if (!isset($user_profile['provider_id'])) {
                        throw new Exception('User do not have provider_id');
                    } else {
                        throw new Exception('User is not in relation');
                    }
                }
            } else {
                throw new Exception($user_profile['error']);
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function if_connected($action, $account_id)
    {
        try {
            $lead = Lead::where('id', $action->lead_id)->first();
            $url = $lead->profileUrl;
            $uc = new UnipileController();
            $profile = [
                'account_id' => $account_id,
                'profile_url' => $url
            ];
            $user_profile = $uc->view_profile(new \Illuminate\Http\Request($profile))->getData(true);
            if (!isset($user_profile['error'])) {
                $user_profile = $user_profile['user_profile'];
                if ($user_profile['is_relationship'] == true) {
                    $lead->send_connections = 'connected';
                    $lead->updated_at = now();
                    $lead->save();
                    return 'true';
                } else {
                    $lead->send_connections = 'not_connected';
                    $lead->updated_at = now();
                    $lead->save();
                    return 'false';
                }
            } else {
                throw new Exception($user_profile['error']);
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function invite_to_connect($action, $account_id, $element, $campaign_element)
    {
        try {
            $lead = Lead::where('id', $action->lead_id)->first();
            $url = $lead->profileUrl;
            $uc = new UnipileController();
            $profile = [
                'account_id' => $account_id,
                'profile_url' => $url,
            ];
            $user_profile = $uc->view_profile(new \Illuminate\Http\Request($profile))->getData(true);
            if (!isset($user_profile['error'])) {
                $user_profile = $user_profile['user_profile'];
                if (isset($user_profile['provider_id'])) {
                    $invite_to_connect = [
                        'account_id' => $account_id,
                        'identifier' => $user_profile['provider_id'],
                    ];
                    if (isset($element) && isset($campaign_element)) {
                        $property = Properties::where('property_name', 'Connect Message')->first();
                        $campaign_property = Campaign_Property::where('property_id', $property->id)
                            ->where('campaign_element_id', $campaign_element->id)->first();
                        if ($campaign_property) {
                            $invite_to_connect['message'] = $campaign_property->value;
                        }
                        $invite_to_connect = $uc->invite_to_connect(new \Illuminate\Http\Request($invite_to_connect))->getData(true);
                        if (!isset($invite_to_connect['error'])) {
                            $lead->send_connections = 'connection_pending';
                            $lead->updated_at = now();
                            $lead->save();
                            return true;
                        } else {
                            throw new Exception($invite_to_connect['error']);
                        }
                    } else {
                        if (!isset($element)) {
                            throw new Exception('Element is not saved');
                        } else {
                            throw new Exception('Campaign Element is not saved');
                        }
                    }
                } else {
                    throw new Exception('User do not have provider_id');
                }
            } else {
                throw new Exception($user_profile['error']);
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function inmail_message($action, $account_id, $element, $campaign_element)
    {
        try {
            $lead = Lead::where('id', $action->lead_id)->first();
            $url = $lead->profileUrl;
            $uc = new UnipileController();
            $profile = [
                'account_id' => $account_id,
                'profile_url' => $url,
            ];
            $user_profile = $uc->view_profile(new \Illuminate\Http\Request($profile))->getData(true);
            if (!isset($user_profile['error'])) {
                $user_profile = $user_profile['user_profile'];
                if (isset($user_profile['provider_id']) && $user_profile['is_relationship'] === true) {
                    $inmail_message = [
                        'account_id' => $account_id,
                        'identifier' => $user_profile['provider_id'],
                    ];
                    if (isset($element) && isset($campaign_element)) {
                        $property = Properties::where('property_name', 'Message')->first();
                        $campaign_property = Campaign_Property::where('property_id', $property->id)
                            ->where('campaign_element_id', $campaign_element->id)->first();
                        if ($campaign_property) {
                            $inmail_message['message'] = $campaign_property->value;
                        }
                        $inmail_message = $uc->inmail_message(new \Illuminate\Http\Request($inmail_message))->getData(true);
                        if (!isset($inmail_message['error'])) {
                            $lead->send_connections = 'messaged';
                            $lead->updated_at = now();
                            $lead->save();
                            return true;
                        } else {
                            throw new Exception($inmail_message['error']);
                        }
                    } else {
                        if (!isset($element)) {
                            throw new Exception('Element is not saved');
                        } else {
                            throw new Exception('Campaign Element is not saved');
                        }
                    }
                } else {
                    if (!isset($user_profile['provider_id'])) {
                        throw new Exception('User do not have provider_id');
                    } else {
                        throw new Exception('User is not in relation');
                    }
                }
            } else {
                throw new Exception($user_profile['error']);
            }
        } catch (\Exception $e) {
            throw new Exception($e);
        }
    }

    public function email_message($action, $account_id, $element, $campaign_element)
    {
        try {
            $lead = Lead::where('id', $action->lead_id)->first();
            $url = $lead->profileUrl;
            $uc = new UnipileController();
            $profile = [
                'account_id' => $account_id,
                'profile_url' => $url,
            ];
            $user_profile = $uc->view_profile(new \Illuminate\Http\Request($profile))->getData(true);
            if (!isset($user_profile['error'])) {
                $user_profile = $user_profile['user_profile'];
                if (isset($user_profile['contact_info']['emails'])) {
                    $email_message = [
                        'account_id' => $account_id,
                        'email' => $user_profile['contact_info']['emails'][0],
                    ];
                    $email_settings_text_only_email_no_html = Email_Setting::where('campaign_id', $action->campaign_id)
                        ->where('setting_slug', 'email_settings_text_only_email_no_html')->value('value');
                    if ($email_settings_text_only_email_no_html == 'yes') {
                        $email_message['html'] = true;
                    } else {
                        $email_message['html'] = false;
                    }
                    $email_settings_track_the_number_of_email_link_clicks = Email_Setting::where('campaign_id', $action->campaign_id)
                        ->where('setting_slug', 'email_settings_track_the_number_of_email_link_clicks')->value('value');
                    if ($email_settings_track_the_number_of_email_link_clicks == 'yes') {
                        $email_message['link'] = true;
                    } else {
                        $email_message['link'] = false;
                    }
                    $email_settings_track_the_number_of_opened_emails = Email_Setting::where('campaign_id', $action->campaign_id)
                        ->where('setting_slug', 'email_settings_track_the_number_of_opened_emails')->value('value');
                    if ($email_settings_track_the_number_of_opened_emails == 'yes') {
                        $email_message['track'] = true;
                    } else {
                        $email_message['track'] = false;
                    }
                    if (isset($element) && isset($campaign_element)) {
                        $body_property = Properties::where('property_name', 'Body')->first();
                        $campaign_body_property = Campaign_Property::where('property_id', $body_property->id)
                            ->where('campaign_element_id', $campaign_element->id)->first();
                        if ($campaign_body_property) {
                            $email_message['message'] = $campaign_body_property->value;
                        }
                        $subject_property = Properties::where('property_name', 'Subject')->first();
                        $campaign_subject_property = Campaign_Property::where('property_id', $subject_property->id)
                            ->where('campaign_element_id', $campaign_element->id)->first();
                        if ($campaign_subject_property) {
                            $email_message['message'] = $campaign_subject_property->value;
                        }
                        $email_settings_email_id = Email_Setting::where('campaign_id', $action->campaign_id)
                            ->where('setting_slug', 'email_settings_email_id')->first();
                        if ($email_settings_email_id) {
                            $emailFrom = Email_Integraion::where('id', $email_settings_email_id['value'])->first();
                            $email_message['account_id'] = $emailFrom['account_id'];
                            $email_message = $uc->send_an_email(new \Illuminate\Http\Request($email_message))->getData(true);
                            if (!isset($email_message['error'])) {
                                $lead->send_connections = 'email_sent';
                                $lead->updated_at = now();
                                $lead->save();
                                return true;
                            } else {
                                throw new Exception($email_message['error']);
                            }
                        } else {
                            $email_message = $uc->email_message(new \Illuminate\Http\Request($email_message))->getData(true);
                            if (!isset($email_message['error'])) {
                                $lead->send_connections = 'email_sent';
                                $lead->updated_at = now();
                                $lead->save();
                                return true;
                            } else {
                                throw new Exception($email_message['error']);
                            }
                        }
                    } else {
                        if (!isset($element)) {
                            throw new Exception('Element is not saved');
                        } else {
                            throw new Exception('Campaign Element is not saved');
                        }
                    }
                } else {
                    throw new Exception('Email not found of User');
                }
            } else {
                throw new Exception($user_profile['error']);
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function if_email_is_opened($action)
    {
        try {
            $lead = Lead::where('id', $action['lead_id'])->first();
            if ($lead) {
                if (!is_null($lead['email']) && $lead['email'] != '') {
                    return 'true';
                } else {
                    return 'false';
                }
            } else {
                return 'false';
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function if_has_imported_email($action)
    {
        try {
            $lead = Lead::where('id', $action['lead_id'])->first();
            if ($lead) {
                if (!is_null($lead['email']) && $lead['email'] != '') {
                    return 'true';
                } else {
                    return 'false';
                }
            } else {
                return 'false';
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function if_has_verified_email($action)
    {
        try {
            $lead = Lead::where('id', $action['lead_id'])->first();
            if ($lead) {
                if (!is_null($lead['email']) && $lead['email'] != '') {
                    return 'true';
                } else {
                    return 'false';
                }
            } else {
                return 'false';
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    public function if_free_inmail($action)
    {
        try {
            $lead = Lead::where('id', $action['lead_id'])->first();
            if ($lead) {
                if (!is_null($lead['email']) && $lead['email'] != '') {
                    return 'true';
                } else {
                    return 'false';
                }
            } else {
                return 'false';
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
