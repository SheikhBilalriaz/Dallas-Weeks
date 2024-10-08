<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeatDashboardController extends Controller
{
    /**
     * Filter seats based on the search term and retrieve additional account information.
     *
     * @param string $slug
     * @param string $search The search term to filter seat names.
     * @return \Illuminate\Http\JsonResponse The JSON response with the filtered seats and their statuses.
     */
    public function seatDashboard($slug, Request $request)
    {
        $seat_id = $request->input('seat_id', session('seat_id'));
        $user = Auth::user();
        $team = Team::where('slug', $slug)->first();
        if ($user->id == $team->creator_id) {
            
        }
        dd($team);
    }
}
