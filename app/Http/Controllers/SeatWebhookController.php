<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SeatWebhookController extends Controller
{
    public function webhook($slug, $seat_slug)
    {
        dd($slug, $seat_slug);
    }
}
