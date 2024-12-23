<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Campaign_Action;
use App\Models\Campaign_Element;
use App\Models\Campaign_Path;
use App\Models\Campaign_Property;
use App\Models\Element;
use App\Models\Email_Setting;
use App\Models\Global_Setting;
use App\Models\Lead;
use App\Models\Lead_Action;
use App\Models\Linkedin_Setting;
use App\Models\Properties;
use App\Models\Seat;
use App\Models\Team;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CampaignElementController extends Controller
{
    public function campaignElement($slug, $seat_slug, $element_slug)
    {
        try {
            $element = Element::where('slug', $element_slug)->first();
            if ($element) {
                $properties = Properties::where('element_id', $element->id)->get();
                if ($properties->isNotEmpty()) {
                    return response()->json(['success' => true, 'properties' => $properties]);
                } else {
                    return response()->json(['success' => false, 'message' => 'No Properties Found']);
                }
            }
            return response()->json(['success' => false, 'message' => 'No Properties Found']);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function createCampaign($slug, $seat_slug, Request $request)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            DB::beginTransaction();
            $campaign = null;
            try {
                $user_id = Auth::user()->id;
                $data = $request->all();
                $final_array = $data['final_array'];
                $final_data = $data['final_data'];
                $settings = $data['settings'];
                $img_path = $data['img_url'];
                $oneMinuteAgo = Carbon::now()->subMinute();
                $existing_campaign = Campaign::where('name', $settings['campaign_name'])
                    ->where('seat_id', $seat->id)
                    ->where('created_at', '>=', $oneMinuteAgo)
                    ->first();
                if ($existing_campaign) {
                    $request->session()->flash('success', 'Campaign successfully saved!');
                    return response()->json(['success' => true]);
                }
                $campaign = new Campaign([
                    'name' => $settings['campaign_name'],
                    'type' => $settings['campaign_type'],
                    'url' => $settings['campaign_url'],
                    'connection' => ($settings['campaign_type'] != 'import' && $settings['campaign_type'] != 'recruiter' && $settings['campaign_type'] != 'leads_list') ? $settings['connections'] : 'o',
                    'seat_id' => $seat->id,
                    'img_path' => $img_path
                ]);
                $campaign->save();
                if (!empty($settings['campaign_url_hidden'])) {
                    $campaign['url'] = $settings['campaign_url_hidden'];
                    $campaign->save();
                }
                $this->saveSettings($settings, $campaign->id, $user_id);
                $this->saveCampaignElements($final_array, $final_data, $campaign->id, $user_id);
                $this->createInitialCampaignAction($campaign->id);
                DB::commit();
                $request->session()->flash('success', 'Campaign successfully saved!');
                return response()->json(['success' => true]);
            } catch (Exception $e) {
                Log::error($e);
                DB::rollBack();
                if ($campaign !== null) {
                    $campaign->delete();
                }
                return response()->json(['success' => false, 'message' => 'Campaign save unsuccesfull']);
            }
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    private function saveSettings($settings, $campaign_id, $user_id)
    {
        foreach ($settings as $key => $value) {
            $setting = $this->getSettingModel($key);
            if ($setting) {
                $setting->create([
                    'campaign_id' => $campaign_id,
                    'setting_slug' => $key,
                    'user_id' => $user_id,
                    'seat_id' => 1,
                    'value' => $value,
                    'setting_name' => ucwords(str_replace('_', ' ', $key)),
                ]);
            }
        }
    }

    private function getSettingModel($key)
    {
        if (str_contains($key, 'email_settings_')) {
            return new Email_Setting();
        }
        if (str_contains($key, 'linkedin_settings_')) {
            return new Linkedin_Setting();
        }
        if (str_contains($key, 'global_settings_')) {
            return new Global_Setting();
        }
        return null;
    }

    private function saveCampaignElements($final_array, $final_data, $campaign_id, $user_id)
    {
        $time = now();
        $path_array = [];
        foreach ($final_array as $key => $value) {
            if ($key != 'step' && $key != 'step-1') {
                $element = Element::where('slug', $this->remove_prefix($key))->first();
                if ($element) {
                    $element_item = Campaign_Element::create([
                        'element_id' => $element->id,
                        'campaign_id' => $campaign_id,
                        'user_id' => $user_id,
                        'seat_id' => 1,
                        'position_x' => $value['position_x'],
                        'position_y' => $value['position_y'],
                        'slug' => $key,
                    ]);
                    $path_array[$key] = $element_item->id;
                    if (isset($final_data[$key])) {
                        $this->saveElementProperties($element_item->id, $final_data[$key], $campaign_id, $time);
                    }
                }
            }
        }
        foreach ($final_array as $key => $value) {
            if (isset($path_array[$key])) {
                Campaign_Path::create([
                    'campaign_id' => $campaign_id,
                    'current_element_id' => $path_array[$key],
                    'next_false_element_id' => $final_array[$key]['0'] ? $path_array[$value['0']] : null,
                    'next_true_element_id' => $final_array[$key]['1'] ? $path_array[$value['1']] : null,
                ]);
            }
        }
    }

    private function saveElementProperties($element_item_id, $property_item, $campaign_id, &$time)
    {
        foreach ($property_item as $property_id => $value) {
            $property = Properties::find($property_id);

            if ($property) {
                $element_property = Campaign_Property::create([
                    'campaign_element_id' => $element_item_id,
                    'property_id' => $property_id,
                    'campaign_id' => $campaign_id,
                    'value' => $value ?? '',
                ]);

                if ($element_property->value) {
                    $timeToAdd = intval($element_property->value);
                    if ($property->property_name == 'Hours') {
                        $time->addHours($timeToAdd);
                    } elseif ($property->property_name == 'Days') {
                        $time->addDays($timeToAdd);
                    }
                }
            }
        }
    }

    private function createInitialCampaignAction($campaign_id)
    {
        $campaign_path = Campaign_Path::where('campaign_id', $campaign_id)->first();

        $current_element_id = $campaign_path ? $campaign_path->current_element_id : null;

        if ($current_element_id) {
            Campaign_Action::create([
                'current_element_id' => $current_element_id,
                'next_true_element_id' => $campaign_path->next_true_element_id ?? null,
                'next_false_element_id' => $campaign_path->next_false_element_id ?? null,
                'campaign_id' => $campaign_id,
                'status' => 'inprogress',
                'ending_time' => now(),
            ]);
        } else {
            Log::error("No valid current element found for campaign ID: $campaign_id");
        }
    }

    private function deleteCampaignData($campaign_id)
    {
        Linkedin_Setting::where('campaign_id', $campaign_id)->delete();
        Lead_Action::where('campaign_id', $campaign_id)->delete();
        Lead::where('campaign_id', $campaign_id)->delete();
        Global_Setting::where('campaign_id', $campaign_id)->delete();
        Email_Setting::where('campaign_id', $campaign_id)->delete();
        Campaign_Property::where('campaign_id', $campaign_id)->delete();
        Campaign_Path::where('campaign_id', $campaign_id)->delete();
        Campaign_Element::where('campaign_id', $campaign_id)->delete();
        Campaign_Action::where('campaign_id', $campaign_id)->delete();
        Campaign::where('id', $campaign_id)->delete();
    }

    private function remove_prefix($value)
    {
        $reverse = strrev($value);
        $first_index = strpos($reverse, '_');
        $second_index = strlen($value) - $first_index - 1;
        $string = substr($value, 0, $second_index);
        return $string;
    }
}
