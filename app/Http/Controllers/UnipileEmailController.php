<?php

namespace App\Http\Controllers;

use App\Models\Email_Integraion;
use App\Models\Seat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnipileEmailController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
            /* Retrieve necessary inputs with validation defaults */
            $accound_id = $request->input('account_id');
            $status = $request->input('status');
            $name = $request->input('name');
            Log::info($accound_id);
            Log::info($status);
            Log::info($name);

            /* If the status is not provided or false, return a failed response */
            if (!$status) {
                return response()->json(['status' => 'failed', 'message' => 'Invalid status'], 403);
            }

            /* Fetch the seat by name (slug) */
            $seat = Seat::where('slug', $name)->first();

            /* Check if the seat exists, if not return a 404 response */
            if (!$seat) {
                return response()->json(['status' => 'failed', 'message' => 'Seat not found'], 404);
            }

            /* Create a Email integration for the seat */
            Email_Integraion::create([
                'seat_id' => $seat->id,
                'account_id' => $accound_id,
            ]);

            /* Return a success response */
            return response()->json(['status' => 'success'], 200);
        } catch (Exception $e) {
            /* If an exception occurs, log the error message for debugging purposes. */
            Log::error($e);

            /* Return a JSON response indicating failure due to a server error. */
            return response()->json(['status' => 'failed'], 500);
        }
    }
}
