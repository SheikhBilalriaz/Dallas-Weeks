<?php

namespace App\Http\Controllers;

use App\Models\Linkedin_Integration;
use App\Models\Seat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnipileLinkedinController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
            /* Retrieve necessary inputs with validation defaults */
            $accound_id = $request->input('account_id');
            $status = $request->input('status');
            $name = $request->input('name');

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

            /* Create a Linkedin integration for the seat */
            Linkedin_Integration::updateOrCreate(
                ['seat_id' => $seat->id],
                ['account_id' => $accound_id, 'updated_at' => now()]
            );

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
