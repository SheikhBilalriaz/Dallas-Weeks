<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Campaign_Element;
use App\Models\Lead_Action;
use App\Models\Seat;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    function report($slug, $seat_slug)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
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
                'title' => 'Dashboard - Report',
                'team' => $team,
                'seat' => $seat,
                'reports' => $reports
            ];
            return view('back.reports', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
