<?php

namespace App\Http\Controllers;

use App\Models\Seat_Time;
use App\Models\Seat;
use App\Models\Seat_Timezone;
use Illuminate\Http\Request;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Validator;

class GlobalLimitController extends Controller
{
    public function updatSeatLimit($slug, $seat_slug, Request $request)
    {
        /* Validate the request input */
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'timezone' => 'required|in:' . implode(',', DateTimeZone::listIdentifiers(DateTimeZone::ALL)),
        ]);

        /* If validation fails, return back with errors */
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }

        /* Create Carbon instances for start and end times */
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);

        /* Check if end time is after start time */
        if ($endTime->lessThanOrEqualTo($startTime)) {
            return redirect()->back()->withErrors(['end_time' => 'The end time must be after the start time.']);
        }

        /* Retrieve the seat by slug */
        $seat = Seat::where('slug', $seat_slug)->first();

        /* Handle start time record */
        $start_time = Seat_Time::updateOrCreate(
            [
                'seat_id' => $seat->id,
                'time_status' => 'start',
            ],
            [
                'time' => $startTime->format('H:i:s'),
                'updated_at' => now(),
            ]
        );

        /* Handle end time record */
        $end_time = Seat_Time::updateOrCreate(
            [
                'seat_id' => $seat->id,
                'time_status' => 'end',
            ],
            [
                'time' => $endTime->format('H:i:s'),
                'updated_at' => now(),
            ]
        );

        $timezone = Seat_Timezone::updateOrCreate(
            [
                'seat_id' => $seat->id,
            ],
            [
                'timezone' => $request->input('timezone'),
                'updated_at' => now(),
            ]
        );

        /* Redirect to the seat dashboard page with a success message */
        return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
            ->with(['success' => 'Global Limits updated successfully']);
    }
}
