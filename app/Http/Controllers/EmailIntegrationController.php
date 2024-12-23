<?php

namespace App\Http\Controllers;

use App\Models\Email_Integraion;
use App\Models\Seat;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class EmailIntegrationController extends Controller
{
    public function createEmailAccount($slug, $seat_slug, Request $request)
    {
        try {
            /* If the user do not have access to update health, so redirect with an error message */
            if (session('manage_email_settings') !== true) {
                return redirect()->route('seatDashboard', ['slug' => $slug, 'seat_slug' => $seat_slug])
                    ->withErrors(['error' => "You do not have access to manage email integrations"]);
            }

            /* Retrieve the seat by its slug (seat_slug). */
            $seat = Seat::where('slug', $seat_slug)->first();

            /* Define the providers array with provider as a single entry */
            $provider[] = $request->input('provider');

            /* Set expiration time to 15 minutes from now in the required format */
            $expirationTime = (new \DateTime())->modify('+15 minutes')->format('Y-m-d\TH:i:s.v\Z');

            /* Initialize a Guzzle HTTP client with SSL verification disabled */
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);

            /* Make a POST request to create a new email account */
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/hosted/accounts/link', [
                'json' => [
                    'type' => 'create',
                    'providers' => $provider,
                    'api_url' => config('services.unipile.dsn'),
                    'expiresOn' => $expirationTime,
                    'success_redirect_url' => route('seatSettingPage', ['slug' => $slug, 'seat_slug' => $seat_slug]),
                    'failure_redirect_url' => route('seatSettingPage', ['slug' => $slug, 'seat_slug' => $seat_slug]),
                    'notify_url' => 'https://networked.site/unipile/email/webhook',
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
                ->withErrors(['success' => false, 'error' => 'Something went wrong']);
        }
    }

    public function disconnectEmailAccount($slug, $seat_slug, $email_id)
    {
        try {
            /* If the user do not have access to update health, so redirect with an error message */
            if (session('manage_email_settings') !== true) {
                return response()->json(['success' => false, 'error' => 'You do not have access to manage email integrations'], 403);
            }

            /* Retrieve the seat by its slug */
            $seat = Seat::where('slug', $seat_slug)->first();

            /* Retrieve the LinkedIn integration associated with the seat */
            $email_integration = Email_Integraion::where('id', $email_id)->first();

            /* If no LinkedIn account is associated, redirect to settings to add an account */
            if (!$email_integration || empty($email_integration->account_id)) {
                return response()->json(['success' => false, 'error' => 'Integrated Email not found'], 404);
            }

            /* Make object of an Unipile Controller */
            $uc = new UnipileController();

            /* Prepare request data for UnipileController */
            $requestData = ['account_id' => $email_integration->account_id];

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
                return response()->json(['success' => false, 'error' => 'Something went wrong'], 500);
            }

            $deleted_account = $uc->delete_account($request)->getData(true);

            if (isset($deleted_account['error'])) {
                /* Log the error message for debugging */
                Log::error($deleted_account['error']);

                /* Redirect response with error message if any error occurs */
                return response()->json(['success' => false, 'error' => 'Something went wrong'], 500);
            }

            /* Delete the Email integration */
            $email_integration->delete();

            /* return a success */
            return response()->json(['success' => true, 'message' => 'Email Disconnected succesfully']);
        } catch (Exception $e) {
            /* Log the exception */
            Log::error($e);

            /* return a 500 response with the error message */
            return response()->json(['success' => false, 'error' => 'Something went wrong'], 500);
        }
    }

    public function searchEmailAccount($slug, $seat_slug, $search)
    {
        try {
            /* Retrieve the seat by its slug */
            $seat = Seat::where('slug', $seat_slug)->first();

            /* Retrieve all email integrations for the seat */
            $integrated_emails = Email_Integraion::where('seat_id', $seat->id)->get();

            /* If no LinkedIn account is associated, redirect to settings to add an account */
            if (!$integrated_emails->isNotEmpty()) {
                return response()->json(['success' => false, 'error' => 'Integrated Emails not found'], 404);
            }

            /* Initialize the UnipileController to retrieve external account information */
            $uc = new UnipileController();

            /* Loop through each integrated email and retrieve account & profile details */
            foreach ($integrated_emails as $key => $email) {
                /* Prepare the request data for Unipile API */
                $requestData = ['account_id' => $email['account_id']];
                $request = new \Illuminate\Http\Request();
                $request->replace($requestData);

                /* Retrieve the account details using Unipile API */
                $account = $uc->retrieve_an_account($request)->getData(true);

                /* If there is an error, remove this email from the list */
                if (isset($account['error'])) {
                    unset($integrated_emails[$key]);
                    continue;
                }

                /* Attach account information to the integrated email */
                $integrated_emails[$key]['account'] = $account['account'];

                /* Retrieve the profile details using Unipile API */
                $profile = $uc->retrieve_own_profile($request)->getData(true);

                /* If there is an error, remove this email from the list */
                if (isset($profile['error'])) {
                    unset($integrated_emails[$key]);
                    continue;
                }

                /* Attach profile information to the integrated email */
                $integrated_emails[$key]['profile'] = $profile['profile'];

                if ($search !== 'null' && strpos($email['profile']['email'], $search) === false) {
                    unset($integrated_emails[$key]);
                    continue;
                }
            }

            /* return a success */
            return response()->json(['success' => true, 'email_accounts' => $integrated_emails]);
        } catch (Exception $e) {
            /* Log the exception */
            Log::error($e);

            /* return a 500 response with the error message */
            return response()->json(['success' => false, 'error' => 'Something went wrong'], 500);
        }
    }
}
