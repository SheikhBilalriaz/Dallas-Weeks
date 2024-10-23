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
        $teams = Team::where('creator_id', $user->id)->get();
        foreach ($teams as $team) {
            $team_members = Team_Member::where('team_id', $team->id)->get();
            foreach ($team_members as $member) {
                $assigned_seats = Assigned_Seat::where('member_id', $member->id)->get();
            }
            $seats = Seat::where('team_id', $team->id)->get();
            foreach ($seats as $seat) {
                $account_healths = Account_Health::where('seat_id', $seat->id)->get();
                $account_health_limits = Account_Health_Limit::where('seat_id', $seat->id)->get();
                $global_limits = Global_Limit::where('seat_id', $seat->id)->get();
                $seat_times = Seat_Time::where('seat_id', $seat->id)->get();
                $seat_timezones = Seat_Timezone::where('seat_id', $seat->id)->get();
                $campaigns = Campaign::where('seat_id', $seat->id)->get();
                foreach ($campaigns as $campaign) {
                    $campaign_actions = Campaign_Action::where('campaign_id', $campaign->id)->get();
                    $campaign_properties = Campaign_Property::where('campaign_id', $campaign->id)->get();
                    $campaign_paths = Campaign_Path::where('campaign_id', $campaign->id)->get();
                    $campaign_elements = Campaign_Element::where('campaign_id', $campaign->id)->get();
                    $lead_actions = Lead_Action::where('campaign_id', $campaign->id)->get();
                    $leads = Lead::where('campaign_id', $campaign->id)->get();
                    $email_settings = Email_Setting::where('campaign_id', $campaign->id)->get();
                    $global_settings = Global_Setting::where('campaign_id', $campaign->id)->get();
                    $linkedin_settings = Linkedin_Setting::where('campaign_id', $campaign->id)->get();
                }
                $webhooks = Webhook::where('seat_id', $seat->id)->get();
                $email_integrations = Email_Integraion::where('seat_id', $seat->id)->get();
                $linkedin_integrations = Linkedin_Integration::where('seat_id', $seat->id)->get();
                $company_info = Company_Info::where('id', $seat->company_info_id)->get();
                $seat_info = Seat_Info::where('id', $seat->seat_info_id)->get();
                $invoices = Invoice::where('seat_id', $seat->id)->get();
                $schedules = Schedule::where('seat_id', $seat->id)->get();
                foreach ($schedules as $schedule) {
                    $schedule_days = Schedule_Day::where('schedule_id', $schedule->id)->get();
                }
            }
            $email_blacklists = Email_Blacklist::where('team_id', $team->id)->get();
            $global_blacklists = Global_Blacklist::where('team_id', $team->id)->get();
            $global_permissions = Global_Permission::where('team_id', $team->id)->get();
            $roles = Role::where('team_id', $team->id)->get();
            foreach ($roles as $role) {
                $permission_to_roles = Role_Permission::where('role_id', $role->id)->get();
            }
        }
        $team_members = Team_Member::where('user_id', $user->id)->get();
        foreach ($team_members as $member) {
            $assigned_seats = Assigned_Seat::where('member_id', $member->id)->get();
            $global_permissions = Global_Permission::where('team_id', $member->team_id)->where('user_id', $user->id)->get();
        }
    }
}
