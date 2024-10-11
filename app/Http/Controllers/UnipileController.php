<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnipileController extends Controller
{
    public function retrieve_an_account(Request $request)
    {
        try {
            /* Check if 'account_id', Unipile API key, and DSN are present */
            if (!$request->has('account_id') || !config('services.unipile.key') || !config('services.unipile.dsn')) {
                /* Return error response if any required parameters are missing */
                return response()->json(['error' => 'Missing required parameters'], 400);
            }

            /* Assign the account_id from request data */
            $account_id = $request->input('account_id');

            /* Create a new GuzzleHttp client with SSL verification disabled */
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);

            /* Construct the API URL using the account_id and DSN from the config */
            $url = config('services.unipile.dsn') . 'api/v1/accounts/' . $account_id;

            /* Make the GET request to the Unipile API */
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);

            /* Parse the API response body as an associative array */
            $account = json_decode($response->getBody(), true);

            /* Return the account details as a JSON response */
            return response()->json(['account' => $account]);
        } catch (\Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function retrieve_own_profile(Request $request)
    {
        try {
            /* Check if 'account_id', Unipile API key, and DSN are present */
            if (!$request->has('account_id') || !config('services.unipile.key') || !config('services.unipile.dsn')) {
                /* Return error response if any required parameters are missing */
                return response()->json(['error' => 'Missing required parameters'], 400);
            }

            /* Assign the account_id from request data */
            $account_id = $request->input('account_id');

            /* Create a new GuzzleHttp client with SSL verification disabled */
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
            /* Construct the API URL using the account_id and DSN from the config */
            $url = config('services.unipile.dsn') . 'api/v1/users/me?account_id=' . $account_id;

            /* Make the GET request to the Unipile API */
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);

            /* Parse the API response body as an associative array */
            $profile = json_decode($response->getBody(), true);

            /* Return the profile details as a JSON response */
            return response()->json(['profile' => $profile]);
        } catch (\Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
