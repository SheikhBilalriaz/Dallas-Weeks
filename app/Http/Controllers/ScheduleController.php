<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Schedule_Day;
use App\Models\Seat;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    private function sort_Days($days_array)
    {
        $selectedDays = array();
        $selectedDays['mon'] = $days_array['mon'];
        $selectedDays['tue'] = $days_array['tue'];
        $selectedDays['wed'] = $days_array['wed'];
        $selectedDays['thurs'] = $days_array['thurs'];
        $selectedDays['fri'] = $days_array['fri'];
        $selectedDays['sat'] = $days_array['sat'];
        $selectedDays['sun'] = $days_array['sun'];
        return $selectedDays;
    }

    public function createSchedule($slug, $seat_slug, Request $request)
    {
        $all = $request->all();
        $seat = Seat::where('slug', $seat_slug)->first();
        $name = $request->schedule_name == null ? '' : $request->schedule_name;
        unset($all['schedule_name']);
        $selectedDays = array();
        $timing = array();
        foreach ($all as $key => $value) {
            if (str_contains($key, '_selected_day')) {
                $str = str_replace('_selected_day', '', $key);
                $selectedDays[$str] = $value;
                unset($all[$key]);
            } else if (str_contains($key, '_end_time')) {
                $str = str_replace('_end_time', '', $key);
                $timing[$str]['end_time'] = $value;
                unset($all[$key]);
            } else if (str_contains($key, '_start_time')) {
                $str = str_replace('_start_time', '', $key);
                $timing[$str]['start_time'] = $value;
                unset($all[$key]);
            }
        }
        $selectedDays = $this->sort_Days($selectedDays);
        $campaign_schedule = new Schedule();
        $campaign_schedule->name = $name;
        $campaign_schedule->created_at = now();
        $campaign_schedule->updated_at = now();
        $campaign_schedule->seat_id = $seat->id;
        $campaign_schedule->save();
        foreach ($selectedDays as $key => $value) {
            $schedule_days = new Schedule_Day();
            $schedule_days->schedule_id = $campaign_schedule->id;
            $schedule_days->start_time = $timing[$key]['start_time'] == null ? '00:00:00' : $timing[$key]['start_time'];
            $schedule_days->end_time = $timing[$key]['end_time'] == null ? '00:00:00' : $timing[$key]['end_time'];
            $schedule_days->created_at = now();
            $schedule_days->updated_at = now();
            $schedule_days->day = $key;
            $schedule_days->is_active = $value == 'true' ? '1' : '0';
            $schedule_days->save();
        }
        $schedules = Schedule::where('seat_id', $seat->id)->orderBy('created_at')->get();
        foreach ($schedules as $schedule) {
            $schedule['Days'] = Schedule_Day::where('schedule_id', $schedule->id)->orderBy('id')->get();
        }
        return response()->json(['success' => true, 'schedules' => $schedules]);
    }

    public function filterSchedule($slug, $seat_slug, $search)
    {
        $seat = Seat::where('slug', $seat_slug)->first();
        $schedules = Schedule::where('seat_id', $seat->id);
        if ($search != 'null') {
            $schedules = $schedules->where('name', 'LIKE', '%' . $search . '%');
        }
        $schedules = $schedules->orderBy('created_at')->get();
        foreach ($schedules as $schedule) {
            $schedule['Days'] = Schedule_Day::where('schedule_id', $schedule->id)->get();
        }
        if (count($schedules) != 0) {
            return response()->json(['success' => true, 'schedules' => $schedules]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function filterTeamSchedule($slug, $seat_slug, $search)
    {
        $team = Team::where('slug', $slug)->first();
        $seats = Seat::where('team_id', $team->id)->get();
        $schedules = Schedule::whereIn('seat_id', $seats->pluck('id')->toArray());
        if ($search != 'null') {
            $schedules = $schedules->where('name', 'LIKE', '%' . $search . '%');
        }
        $schedules = $schedules->orderBy('created_at')->get();
        foreach ($schedules as $schedule) {
            $schedule['Days'] = Schedule_Day::where('schedule_id', $schedule->id)->get();
        }
        if (count($schedules) != 0) {
            return response()->json(['success' => true, 'schedules' => $schedules]);
        } else {
            return response()->json(['success' => false]);
        }
    }
}
