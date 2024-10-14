<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Seat;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    public function campaign($slug, $seat_slug)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            // $lc = new LeadsController();
            $campaigns = Campaign::where('seat_id', $seat->id)->where('is_active', 1)->where('is_archive', 0)->get();
            // foreach ($campaigns as $campaign) {
            //     $campaign['lead_count'] = $lc->getLeadsCountByCampaign($user->id, $campaign->id);
            //     $campaign['view_action_count'] = $lc->getViewProfileByCampaign($user->id, $campaign->id);
            //     $campaign['invite_action_count'] = $lc->getInviteToConnectByCampaign($user->id, $campaign->id);
            //     $campaign['message_count'] = $lc->getSentMessageByCampaign($user->id, $campaign->id);
            //     $campaign['email_action_count'] = $lc->getSentEmailByCampaign($user->id, $campaign->id);
            // }
            // $campaigns = $campaigns->sortByDesc('lead_count')->values();
            $data = [
                'title' => 'Dashboard - Campaign',
                'team' => $team,
                'seat' => $seat,
                'campaigns' => $campaigns,
            ];
            return view('back.campaign', $data);
        } catch (Exception $e) {
            Log::info($e);
            return redirect()->route('acc_dash')->withErrors(['error' => $e->getMessage()]);
        }
    }
}
