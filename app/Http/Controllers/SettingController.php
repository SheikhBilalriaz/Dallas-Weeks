<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Team;
use App\Models\User;
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
            return redirect()->route('dashboardPage', ['slug' => $slug])->withErrors(['error' => 'Something went wrong while viewing the global setting.']);
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
            return redirect()->route('dashboardPage', ['slug' => $slug])->withErrors(['error' => 'Something went wrong while updating the password.']);
        }
    }
}
