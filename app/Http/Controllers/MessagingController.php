<?php

namespace App\Http\Controllers;

use App\Models\Linkedin_Integration;
use App\Models\Seat;
use Illuminate\Http\Request;

class MessagingController extends Controller
{
    public function getProfileAndLatestMessage($slug, $seat_slug, $attendee_provider_id, $chat_id)
    {
        $seat = Seat::where('slug', $seat_slug)->first();
        $linkedin_integrations = Linkedin_Integration::where('seat_id', $seat->id)->first();
        $data = [];
        $uc = new UnipileController();
        $request = ['account_id' => $linkedin_integrations['account_id'], 'profile_url' => $attendee_provider_id,];
        $profile = $uc->view_profile(new \Illuminate\Http\Request($request));
        $profile = $profile->getData(true);
        if (isset($profile['error'])) {
            return response()->json(['success' => false]);
        }
        $data['success'] = true;
        $data['user_profile'] = $profile['user_profile'];
        $request = [
            'chat_id' => $chat_id,
            'limit' => 1
        ];
        $last_message = $uc->list_all_messages_from_chat(new \Illuminate\Http\Request($request));
        $last_message = $last_message->getData(true);
        if (!isset($last_message['error']) && isset($last_message['messages']['items'])) {
            $data['message'] = $last_message['messages']['items'];
        }
        return response()->json($data);
    }
}
