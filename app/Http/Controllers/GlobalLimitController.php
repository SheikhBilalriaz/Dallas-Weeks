<?php

namespace App\Http\Controllers;

use App\Models\Seat_Time;
use App\Models\Seat;
use App\Models\Seat_Timezone;
use Illuminate\Http\Request;
use Exception;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GlobalLimitController extends Controller
{
    public function updateSeatLimit($slug, $seat_slug, Request $request)
    {
        try {
            /* If the user do not have access to update limits, so redirect with an error message */
            if (session('manage_global_limits') !== true) {
                return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                    ->withErrors(['error' => "You do not have access to manage global limits"]);
            }

            /* Validate the request input */
            $validator = Validator::make($request->all(), [
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'timezone' => 'required|in:' . implode(',', DateTimeZone::listIdentifiers(DateTimeZone::ALL)),
            ]);

            /* If validation fails, return back with errors */
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
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

            /* Handle time zone record */
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
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
