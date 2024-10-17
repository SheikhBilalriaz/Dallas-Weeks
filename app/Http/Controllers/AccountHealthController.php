<?php

namespace App\Http\Controllers;

use App\Models\Account_Health;
use App\Models\Account_Health_Limit;
use App\Models\Seat;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AccountHealthController extends Controller
{
    public function updateAccountHealth($slug, $seat_slug, Request $request)
    {
        try {
            /* If the user do not have access to update health, so redirect with an error message */
            if (session('manage_account_health') !== true) {
                return redirect()->route('seatDashboard', ['slug' => $slug, 'seat_slug' => $seat_slug])
                    ->withErrors(['error' => "You do not have access to manage account health"]);
            }

            /* Validate the request input */
            $validator = Validator::make($request->all(), []);

            /* If validation fails, return back with errors */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->withInput();
            }

            /* Retrieve the seat by slug */
            $seat = Seat::where('slug', $seat_slug)->first();

            $pendingConnections = Account_Health_Limit::firstOrNew(
                [
                    'seat_id' => $seat->id,
                    'health_slug' => 'pending_connections',
                ],
                [
                    'value' => 0,
                ]
            );

            /* Update the value based on request input */
            $pendingConnections->value = $request->input('pending_connections') ?? 0;
            $pendingConnections->updated_at = now();
            $pendingConnections->save();

            /* Retrieve or create the account health record */
            $oldestPendingInvitations = Account_Health::firstOrNew(
                [
                    'seat_id' => $seat->id,
                    'health_slug' => 'oldest_pending_invitations',
                ],
                [
                    'value' => 0,
                ]
            );

            /* Update the value based on request input */
            $oldestPendingInvitations->value = $request->has('oldest_pending_invitations') ? 1 : 0;
            $oldestPendingInvitations->updated_at = now();
            $oldestPendingInvitations->save();

            $run_on_weekends = Account_Health::firstOrNew(
                [
                    'seat_id' => $seat->id,
                    'health_slug' => 'run_on_weekends',
                ],
                [
                    'value' => 0,
                ]
            );

            /* Update the value based on request input */
            $run_on_weekends->value = $request->has('run_on_weekends') ? 1 : 0;
            $run_on_weekends->updated_at = now();
            $run_on_weekends->save();

            /* Redirect to the seat dashboard page with a success message */
            return redirect()->route('seatSettingPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->with(['success' => 'Account Health updated successfully']);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
