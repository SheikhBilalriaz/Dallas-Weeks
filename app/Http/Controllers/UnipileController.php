<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
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
            $url = config('services.unipile.dsn') . '/api/v1/accounts/' . $account_id;

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
        } catch (Exception $e) {
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
            $url = config('services.unipile.dsn') . '/api/v1/users/me?account_id=' . $account_id;

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
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete_account(Request $request)
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
            $url = config('services.unipile.dsn') . '/api/v1/accounts/' . $account_id;

            /* Make the GET request to the Unipile API */
            $response = $client->request('DELETE', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);

            /* Parse the API response body as an associative array */
            $delete_account = json_decode($response->getBody(), true);

            /* Return the profile details as a JSON response */
            return response()->json(['delete' => $delete_account]);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function list_all_chats(Request $request)
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
            $url = config('services.unipile.dsn') . '/api/v1/chats?account_id=' . $account_id;

            if ($request->has('cursor')) {
                $url .= '&' . 'cursor=' . $request->input('cursor');
            }
            if ($request->has('unread')) {
                $url .= '&' . 'unread=' . 'true';
            }
            if ($request->has('before')) {
                $url .= '&' . 'before=' . $request->input('before');
            }
            if ($request->has('after')) {
                $url .= '&' . 'after=' . $request->input('after');
            }
            if ($request->has('limit')) {
                $url .= '&' . 'limit=' . $request->input('limit');
            }
            if ($request->has('account_type')) {
                $url .= '&' . 'account_type=' . $request->input('account_type');
            }

            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $chats = json_decode($response->getBody(), true);
            return response()->json(['chats' => $chats]);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function view_profile(Request $request)
    {
        try {
            /* Check if 'account_id', Unipile API key, and DSN are present */
            if (!$request->has('account_id') || !config('services.unipile.key') || !config('services.unipile.dsn')) {
                /* Return error response if any required parameters are missing */
                return response()->json(['error' => 'Missing required parameters'], 400);
            }

            /* Assign the account_id and profile_id from request data */
            $account_id = $request->input('account_id');
            $profile_url = $request->input('profile_url');

            $notify = 'false';
            if ($request->has('notify')) {
                $notify = 'true';
            }

            /* Create a new GuzzleHttp client with SSL verification disabled */
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);

            if ($request->input('sales_navigator')) {
                $url = config('services.unipile.dsn') . '/api/v1/users/' . $profile_url . '?linkedin_api=sales_navigator&linkedin_sections=%2A&notify=' . $notify . '&account_id=' . $account_id;
            } else {
                if (strpos($profile_url, 'https://www.linkedin.com/company/') !== false) {
                    $profile_url = str_replace('https://www.linkedin.com/company/', config('services.unipile.dsn') . '/api/v1/linkedin/company/', $profile_url);
                } else if (strpos($profile_url, 'https://www.linkedin.com/in/') !== false) {
                    $profile_url = str_replace('https://www.linkedin.com/in/', config('services.unipile.dsn') . '/api/v1/users/', $profile_url);
                } else {
                    $profile_url =  config('services.unipile.dsn') . '/api/v1/users/' . $profile_url;
                }
                $url = $profile_url . '?linkedin_sections=%2A&notify=' . $notify . '&account_id=' . $account_id;
            }

            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $chats = json_decode($response->getBody(), true);
            return response()->json(['user_profile' => $chats]);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function list_all_messages_from_chat(Request $request)
    {
        try {
            $all = $request->all();
            if (!isset($all['chat_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
                return response()->json(['error' => 'Missing required parameters'], 400);
            }
            $chat_id = $all['chat_id'];
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
            $url = config('services.unipile.dsn') . '/api/v1/chats/' . $chat_id . '/messages?';
            if (isset($all['cursor'])) {
                $url .= 'cursor=' . $all['cursor'] . '&';
            }
            if (isset($all['before'])) {
                $url .= 'before=' . $all['before'] . '&';
            }
            if (isset($all['after'])) {
                $url .= 'after=' . $all['after'] . '&';
            }
            if (isset($all['limit'])) {
                $url .= 'limit=' . $all['limit'] . '&';
            }
            if (isset($all['sender'])) {
                $url .= 'sender_id=' . $all['sender'];
            }
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $messages = json_decode($response->getBody(), true);
            return response()->json(['messages' => $messages]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function list_all_relations(Request $request)
    {
        try {
            $all = $request->all();
            if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
                return response()->json(['error' => 'Missing required parameters'], 400);
            }
            $account_id = $all['account_id'];
            $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
            $url = config('services.unipile.dsn') . '/api/v1/users/relations' . '?account_id=' . $account_id . '&';
            if (isset($all['cursor'])) {
                $url .= 'cursor=' . $all['cursor'] . '&';
            }
            if (isset($all['limit'])) {
                $url .= 'limit=' . $all['limit'];
            }
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $relations = json_decode($response->getBody(), true);
            return response()->json(['relations' => $relations]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
