<?php

namespace App\Http\Controllers;

use App\Models\Linkedin_Integration;
use App\Models\Seat;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class LinkedinIntegrationController extends Controller
{
    public function createLinkedinAccount($slug, $seat_slug)
    {
        try {
            /* If the user do not have access to update health, so redirect with an error message */
            if (session('manage_linkedin_integrations') !== true) {
                return redirect()->route('seatDashboard', ['slug' => $slug, 'seat_slug' => $seat_slug])
                    ->withErrors(['error' => "You do not have access to manage linkedin integrations"]);
            }

            /* Retrieve the seat by its slug (seat_slug). */
            $seat = Seat::where('slug', $seat_slug)->first();

            /* If it exists, return a JSON response with an error message and a 403 status code. */
            if (Linkedin_Integration::where('seat_id', $seat->id)->exists()) {
                return response()->json(['error' => 'LinkedIn already integrated to your seat'], 403);
            }

            /* Define the providers array with "LINKEDIN" as a single entry */
            $provider[] = "LINKEDIN";

            /* Set expiration time to 15 minutes from now in the required format */
            $expirationTime = (new \DateTime())->modify('+15 minutes')->format('Y-m-d\TH:i:s.v\Z');

            /* Initialize a Guzzle HTTP client with SSL verification disabled */
            $client = new \GuzzleHttp\Client([
                'verify' => true,
            ]);

            /* Make a POST request to create a new LinkedIn account */
            $response = $client->request('POST', config('services.unipile.dsn') . '/api/v1/hosted/accounts/link' . '?port=13443', [
                'json' => [
                    'type' => 'create',
                    'providers' => $provider,
                    'api_url' => config('services.unipile.dsn') . ':13443',
                    'expiresOn' => $expirationTime,
                    'success_redirect_url' => route('seatSettingPage', ['slug' => $slug, 'seat_slug' => $seat_slug]),
                    'failure_redirect_url' => route('seatSettingPage', ['slug' => $slug, 'seat_slug' => $seat_slug]),
                    'notify_url' => 'https://networked.site/unipile/linkedin/webhook',
                    'name' => $seat_slug,
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            /* Prepare response data to return as JSON */
            $data = [
                'success' => true,
                'data' => json_decode($response->getBody()->getContents(), true)
            ];

            /* Return the success response as JSON */
            return response()->json($data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function disconnectLinkedinAccount($slug, $seat_slug)
    {
        try {
            /* Check if the user has permission to manage LinkedIn integrations */
            if (session('manage_linkedin_integrations') !== true) {
                return response()->json(['success' => false, 'error' => 'You do not have access to manage LinkedIn integrations'], 403);
            }

            /* Retrieve the seat by its slug */
            $seat = Seat::where('slug', $seat_slug)->first();

            /* Retrieve the LinkedIn integration associated with the seat */
            $linkedin_integration = Linkedin_Integration::where('seat_id', $seat->id)->first();

            /* If no LinkedIn account is associated, redirect to settings to add an account */
            if (!$linkedin_integration || empty($linkedin_integration->account_id)) {
                return redirect()->route('dashSettingsPage', ['slug' => $slug, 'seat_slug' => $seat_slug])->with(['add_account' => true]);
            }

            /* Make object of an Unipile Controller */
            $uc = new UnipileController();

            /* Prepare request data for UnipileController */
            $requestData = ['account_id' => $linkedin_integration->account_id];

            /* Create a new Request object and replace data */
            $request = new \Illuminate\Http\Request();
            $request->replace($requestData);

            /* Call UnipileController's retrieve_an_account method */
            $uc = new UnipileController();
            $account = $uc->retrieve_an_account($request)->getData(true);

            /* Check if there was an error in the Unipile API response */
            if (isset($account['error'])) {
                /* Log the error message for debugging */
                Log::error($account['error']);

                /* Redirect response with error message if any error occurs */
                return response()->json(['success' => false, 'error' => 'Something went wrong']);
            }

            $deleted_account = $uc->delete_account($request)->getData(true);

            if (isset($deleted_account['error'])) {
                /* Log the error message for debugging */
                Log::error($deleted_account['error']);

                /* Redirect response with error message if any error occurs */
                return response()->json(['success' => false, 'error' => 'Something went wrong']);
            }

            /* Delete the LinkedIn integration */
            $linkedin_integration->delete();

            /* Remove specific session keys */
            session()->forget('seat_linkedin');
            session()->forget('linkedin_profile');

            /* Update the seat */
            $seat->is_connected = 0;
            $seat->updated_at = now();
            $seat->save();

            /* return a success */
            return response()->json(['success' => true, 'message' => 'Linkedin Disconnected succesfully']);
        } catch (Exception $e) {
            /* Log the exception */
            Log::error($e);

            /* return a 500 response with the error message */
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
