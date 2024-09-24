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
     * Display the setting dashboard.
     *
     * @param String $slug
     * @return \Illuminate\View\View
     */
    public function globalSetting($slug)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Prepare data to be passed to the view */
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
            return redirect()->route('loginPage')->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Change the password.
     *
     * @param String $slug
     * @param \Illuminate\Http\Request $request
     * @return RedirectResponse
     */
    public function changePassword($slug, Request $request)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();
            $user = User::find($user->id);

            /* Check if the provided old password matches the user's current password */
            if (!Hash::check($request->input('old_password'), $user->password)) {
                return redirect()->back()->withErrors(['old_password' => 'The old password is incorrect.']);
            }

            /* Validate request data */
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            /* If old password is correct, update the user's password */
            $user->password = Hash::make($request->input('new_password'));
            $user->updated_at = now();
            $user->save();

            /* Redirect to the dashboard with a success message */
            return redirect()->route('globalSetting', ['slug' => $slug])
                ->with('success', 'Password changed successfully.');
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes. */
            Log::error($e);

            /* Redirect to login with an error message if an exception occurs. */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
