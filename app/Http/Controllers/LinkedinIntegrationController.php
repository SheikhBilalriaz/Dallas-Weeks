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
                'verify' => false,
            ]);

            /* Make a POST request to create a new LinkedIn account */
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/hosted/accounts/link', [
                'json' => [
                    'type' => 'create',
                    'providers' => $provider,
                    'api_url' => config('services.unipile.dsn'),
                    'expiresOn' => $expirationTime,
                    'success_redirect_url' => 'https://networked.staging.designinternal.com/dashboard',
                    'failure_redirect_url' => 'https://networked.staging.designinternal.com/settings',
                    'notify_url' => 'https://networked.staging.designinternal.com/unipile/linkedin/webhook',
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
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    public function disconnectLinkedinAccount($slug, $seat_slug)
    {
        try {
            $seat = Seat::where('slug', $seat_slug)->first();
            $linkedin_integration = Linkedin_Integration::where('seat_id', $seat->id)->first();
            if (!$linkedin_integration || empty($$linkedin_integration->account_id)) {
                session(['add_account' => true]);
                return redirect()->route('dash-settings');
            }
            $uc = new UnipileController();
            $request = ['account_id' => $seat['account_id']];
            $account = $uc->delete_account(new \Illuminate\Http\Request($request));
            if ($account instanceof JsonResponse) {
                $account = $account->getData(true);
                if (isset($account['error'])) {
                    return response()->json(['success' => false, 'error' => $account['error']]);
                }
                $seat->update(['account_id' => null]);
                session(['delete_account' => true]);
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
