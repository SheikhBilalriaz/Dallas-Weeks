<?php

namespace App\Http\Controllers;

use App\Models\Account_Health;
use App\Models\Account_Health_Limit;
use App\Models\Assigned_Seat;
use App\Models\Campaign;
use App\Models\Campaign_Action;
use App\Models\Campaign_Element;
use App\Models\Campaign_Path;
use App\Models\Campaign_Property;
use App\Models\Company_Info;
use App\Models\Email_Blacklist;
use App\Models\Email_Integraion;
use App\Models\Email_Setting;
use App\Models\Global_Blacklist;
use App\Models\Global_Limit;
use App\Models\Global_Permission;
use App\Models\Global_Setting;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Lead_Action;
use App\Models\Linkedin_Integration;
use App\Models\Linkedin_Setting;
use App\Models\Role;
use App\Models\Role_Permission;
use App\Models\Schedule;
use App\Models\Schedule_Day;
use App\Models\Seat;
use App\Models\Seat_Info;
use App\Models\Seat_Time;
use App\Models\Seat_Timezone;
use Exception;
use App\Models\Team;
use App\Models\Team_Member;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Display the global setting dashboard for a specific team.
     *
     * @param string $slug The unique slug of the team.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse The view of the setting page or a redirect on failure.
     */
    public function globalSetting($slug)
    {
        try {
            /* Retrieve the team by the provided slug or fail gracefully. */
            $team = Team::where('slug', $slug)->first();

            /* Prepare the data to pass to the view, including the title and the team instance. */
            $data = [
                'title' => 'Setting - Networked',
                'team' => $team,
            ];

            /* Return the view with the provided data */
            return view('dashboard.setting', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes. */
            Log::error($e);

            /* Redirect to login with an error message if an exception occurs. */
            return redirect()->route('dashboardPage', ['slug' => $slug])->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Change the password for the currently authenticated user.
     *
     * @param string $slug The unique slug for the team or page redirection.
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing password data.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the settings page with a success or error message.
     */
    public function changePassword($slug, Request $request)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();

            /* Ensure we have the latest user data from the database. */
            $user = User::find($user->id);

            /* Check if the provided old password matches the user's current password */
            if (!Hash::check($request->input('old_password'), $user->password)) {
                return redirect()->back()->withErrors(['old_password' => 'The old password is incorrect.']);
            }

            /* Validate the request data for the new password. */
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            /* If validation fails, redirect back with validation error messages. */
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            /* If old password is correct, update the user's password */
            $user->password = Hash::make($request->input('new_password'));
            $user->updated_at = now();
            $user->save();

            /* Redirect to the dashboard with a success message */
            return redirect()->route('globalSetting', ['slug' => $slug])->with('success', 'Password changed successfully.');
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes. */
            Log::error($e);

            /* Redirect to login with an error message if an exception occurs. */
            return redirect()->route('dashboardPage', ['slug' => $slug])->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $teams = Team::where('creator_id', $user->id)->get();
            foreach ($teams as $team) {
                $team_members = Team_Member::where('team_id', $team->id)->get();
                foreach ($team_members as $member) {
                    Assigned_Seat::where('member_id', $member->id)->delete();
                }
                $seats = Seat::where('team_id', $team->id)->get();
                foreach ($seats as $seat) {
                    Account_Health::where('seat_id', $seat->id)->delete();
                    Account_Health_Limit::where('seat_id', $seat->id)->delete();
                    Global_Limit::where('seat_id', $seat->id)->delete();
                    Seat_Time::where('seat_id', $seat->id)->delete();
                    Seat_Timezone::where('seat_id', $seat->id)->delete();
                    Webhook::where('seat_id', $seat->id)->delete();
                    Email_Integraion::where('seat_id', $seat->id)->delete();
                    Linkedin_Integration::where('seat_id', $seat->id)->delete();
                    Invoice::where('seat_id', $seat->id)->delete();
                    $campaigns = Campaign::where('seat_id', $seat->id)->get();
                    foreach ($campaigns as $campaign) {
                        Campaign_Action::where('campaign_id', $campaign->id)->delete();
                        Campaign_Property::where('campaign_id', $campaign->id)->delete();
                        Campaign_Path::where('campaign_id', $campaign->id)->delete();
                        Campaign_Element::where('campaign_id', $campaign->id)->delete();
                        Lead_Action::where('campaign_id', $campaign->id)->delete();
                        Lead::where('campaign_id', $campaign->id)->delete();
                        Email_Setting::where('campaign_id', $campaign->id)->delete();
                        Global_Setting::where('campaign_id', $campaign->id)->delete();
                        Linkedin_Setting::where('campaign_id', $campaign->id)->delete();
                        $campaign->delete();
                    }
                    Company_Info::where('id', $seat->company_info_id)->delete();
                    Seat_Info::where('id', $seat->seat_info_id)->delete();
                    $schedules = Schedule::where('seat_id', $seat->id)->get();
                    foreach ($schedules as $schedule) {
                        Schedule_Day::where('schedule_id', $schedule->id)->delete();
                        $schedule->delete();
                    }
                    $seat->delete();
                }
                Email_Blacklist::where('team_id', $team->id)->delete();
                Global_Blacklist::where('team_id', $team->id)->delete();
                Global_Permission::where('team_id', $team->id)->delete();
                $roles = Role::where('team_id', $team->id)->get();
                foreach ($roles as $role) {
                    Role_Permission::where('role_id', $role->id)->delete();
                    $role->delete();
                }
                $team->delete();
            }
            $team_members = Team_Member::where('user_id', $user->id)->get();
            foreach ($team_members as $member) {
                Assigned_Seat::where('member_id', $member->id)->delete();
                Global_Permission::where('team_id', $member->team_id)->where('user_id', $user->id)->delete();
            }
            $user->delete();
            DB::commit();
            return redirect()->route('loginPage')->with(['success' => 'Account deleted successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return redirect()->route('loginPage')->withErrors(['error' => 'Something went wrong']);
        }
    }
}
