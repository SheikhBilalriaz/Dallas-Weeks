<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    function settingRolesPermission($slug)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            $data = [
                'title' => 'Setting - Networked',
                'team' => $team,
            ];
            // return view('setting', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes. */
            Log::info($e);

            /* Redirect to login with an error message if an exception occurs. */
            return redirect()->route('loginPage')->withErrors(['error' => $e->getMessage()]);
        }
    }
}
