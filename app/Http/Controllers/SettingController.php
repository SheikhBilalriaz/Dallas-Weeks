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
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;

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
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $uc = new UnipileController();
            $teams = Team::where('creator_id', $user->id)->get();
            foreach ($teams as $team) {
                $seats = Seat::where('team_id', $team->id)->get();
                foreach ($seats as $seat) {
                    $integrated_emails = Email_Integraion::where('seat_id', $seat->id)->get();
                    foreach ($integrated_emails as $integration) {
                        try {
                            $deleteRequest = new \Illuminate\Http\Request(['account_id' => $integration->account_id]);
                            $deletedAccount = $uc->delete_account($deleteRequest)->getData(true);
                        } catch (Exception $e) {
                            Log::error($e);
                        } finally {
                            $integration->delete();
                        }
                    }
                    $linkedin_integrations = Linkedin_Integration::where('seat_id', $seat->id)->get();
                    foreach ($linkedin_integrations as $integration) {
                        try {
                            $deleteRequest = new \Illuminate\Http\Request(['account_id' => $integration->account_id]);
                            $deletedAccount = $uc->delete_account($deleteRequest)->getData(true);
                        } catch (Exception $e) {
                            Log::error($e);
                        } finally {
                            $integration->delete();
                        }
                    }
                    $webhooks = Webhook::where('seat_id', $seat->id)->get();
                    foreach ($webhooks as $webhook) {
                        try {
                            $deleteRequest = new \Illuminate\Http\Request(['webhook_id' => $webhook->webhook_id]);
                            $deletedAccount = $uc->delete_webhook($deleteRequest)->getData(true);
                        } catch (Exception $e) {
                            Log::error($e);
                        } finally {
                            $webhook->delete();
                        }
                    }
                    Subscription::update(
                        $seat->subscription_id,
                        ['cancel_at_period_end' => false,]
                    );
                    $subscription = Subscription::retrieve($seat->subscription_id);
                    $subscription->cancel();
                    $customer = Customer::retrieve($seat->customer_id);
                    $customer->delete();
                    $company_id = $seat->company_info_id;
                    $seat_info_id = $seat->seat_info_id;
                    $seat->delete();
                    Seat_Info::where('id', $seat_info_id)->delete();
                    Company_Info::where('id', $company_id)->delete();
                }
                $team->delete();
            }
            $team_members = Team_Member::where('user_id', $user->id)->get();
            foreach ($team_members as $member) {
                Assigned_Seat::where('member_id', $member->id)->delete();
                Global_Permission::where('team_id', $member->team_id)->where('user_id', $user->id)->delete();
                $member->delete();
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
