<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                'verify' => true,
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
        } catch (Exception $e) {
            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function retrieve_own_profile(Request $request)
    {
        /* Check if 'account_id', Unipile API key, and DSN are present */
        if (!$request->has('account_id') || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            /* Return error response if any required parameters are missing */
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        /* Assign the account_id from request data */
        $account_id = $request->input('account_id');

        /* Create a new GuzzleHttp client with SSL verification disabled */
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);

        /* Construct the API URL using the account_id and DSN from the config */
        $url = config('services.unipile.dsn') . 'api/v1/users/me?account_id=' . $account_id;

        try {
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
            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete_account(Request $request)
    {
        /* Check if 'account_id', Unipile API key, and DSN are present */
        if (!$request->has('account_id') || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            /* Return error response if any required parameters are missing */
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        /* Assign the account_id from request data */
        $account_id = $request->input('account_id');

        /* Create a new GuzzleHttp client with SSL verification disabled */
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);

        /* Construct the API URL using the account_id and DSN from the config */
        $url = config('services.unipile.dsn') . 'api/v1/accounts/' . $account_id;

        try {
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
            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function list_all_chats(Request $request)
    {
        /* Check if 'account_id', Unipile API key, and DSN are present */
        if (!$request->has('account_id') || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            /* Return error response if any required parameters are missing */
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        /* Assign the account_id from request data */
        $account_id = $request->input('account_id');

        /* Create a new GuzzleHttp client with SSL verification disabled */
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);

        /* Construct the API URL using the account_id and DSN from the config */
        $url = config('services.unipile.dsn') . 'api/v1/chats?account_id=' . $account_id;

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
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $chats = json_decode($response->getBody(), true);
            return response()->json(['chats' => $chats]);
        } catch (Exception $e) {
            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function view_profile(Request $request)
    {
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
            'verify' => true,
        ]);

        if ($request->input('sales_navigator')) {
            $url = config('services.unipile.dsn') . 'api/v1/users/' . $profile_url . '?linkedin_api=sales_navigator&linkedin_sections=%2A&notify=' . $notify . '&account_id=' . $account_id;
        } else {
            if (strpos($profile_url, 'https://www.linkedin.com/company/') !== false) {
                $profile_url = str_replace('https://www.linkedin.com/company/', config('services.unipile.dsn') . 'api/v1/linkedin/company/', $profile_url);
            } else if (strpos($profile_url, 'https://www.linkedin.com/in/') !== false) {
                $profile_url = str_replace('https://www.linkedin.com/in/', config('services.unipile.dsn') . 'api/v1/users/', $profile_url);
            } else {
                $profile_url =  config('services.unipile.dsn') . 'api/v1/users/' . $profile_url;
            }
            $url = $profile_url . '?linkedin_sections=%2A&notify=' . $notify . '&account_id=' . $account_id;
        }
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $chats = json_decode($response->getBody(), true);
            return response()->json(['user_profile' => $chats]);
        } catch (Exception $e) {
            /* Handle other unexpected exceptions */
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function list_all_messages_from_chat(Request $request)
    {

        $all = $request->all();
        if (!isset($all['chat_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $chat_id = $all['chat_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/chats/' . $chat_id . '/messages?';
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
        try {
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

        $all = $request->all();
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/users/relations' . '?account_id=' . $account_id . '&';
        if (isset($all['cursor'])) {
            $url .= 'cursor=' . $all['cursor'] . '&';
        }
        if (isset($all['limit'])) {
            $url .= 'limit=' . $all['limit'];
        }
        try {
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

    public function list_all_accounts(Request $request)
    {
        $all = $request->all();
        if (!config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/accounts?';
        if (isset($all['cursor'])) {
            $url .= 'cursor=' . $all['cursor'] . '&';
        }
        if (isset($all['limit'])) {
            $url .= 'limit=' . $all['limit'];
        }
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $accounts = json_decode($response->getBody(), true);
            return response()->json(['accounts' => $accounts]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function restart_an_account(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/accounts/' . $account_id . '/restart';
        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $restart_account = json_decode($response->getBody(), true);
            return response()->json(['account' => $restart_account]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function start_a_new_chat(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['attendee_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $attendee_id = $all['attendee_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        if (isset($all['message'])) {
            $message = $all['message'];
        } else {
            $message = '';
        }
        $url = config('services.unipile.dsn') . 'api/v1/chats';
        try {
            $response = $client->request('POST', $url, [
                'multipart' => [
                    [
                        'name' => 'attendees_ids',
                        'contents' => $attendee_id
                    ],
                    [
                        'name' => 'account_id',
                        'contents' => $account_id
                    ],
                    [
                        'name' => 'text',
                        'contents' => $message
                    ]
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $chat = json_decode($response->getBody(), true);
            return response()->json(['chat' => $chat]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function retrieve_a_chat(Request $request)
    {
        $all = $request->all();
        if (!isset($all['chat_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $chat_id = $all['chat_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/chats/' . $chat_id;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $chats = json_decode($response->getBody(), true);
            return response()->json(['chat' => $chats]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function send_a_message_in_a_chat(Request $request)
    {
        $all = $request->all();
        if (!isset($all['chat_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $chat_id = $all['chat_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        if (isset($all['message'])) {
            $message = $all['message'];
        } else {
            $message = '';
        }
        $url = config('services.unipile.dsn') . 'api/v1/chats/' . $chat_id . '/messages';
        try {
            $response = $client->request('POST', $url, [
                'multipart' => [
                    [
                        'name' => 'text',
                        'contents' => $message
                    ]
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $message = json_decode($response->getBody(), true);
            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function list_all_attendees_from_chat(Request $request)
    {
        $all = $request->all();
        if (!isset($all['chat_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $chat_id = $all['chat_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/chats/' . $chat_id . '/attendees';
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $attendees = json_decode($response->getBody(), true);
            return response()->json(['attendees' => $attendees]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function change_status_chat(Request $request)
    {
        $all = $request->all();
        if (!isset($all['chat_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $chat_id = $all['chat_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/chats/' . $chat_id;
        try {
            $response = $client->request('PATCH', $url, [
                'body' => '{"action":"setReadStatus","value":true}',
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
            $status = json_decode($response->getBody(), true);
            return response()->json(['status' => $status]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function retrieve_a_message(Request $request)
    {
        $all = $request->all();
        if (!isset($all['message_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $message_id = $all['message_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/messages/' . $message_id;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $message = json_decode($response->getBody(), true);
            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function list_all_messages(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/messages?account_id=' . $account_id . '&';
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
        if (isset($all['sender_id'])) {
            $url .= 'sender_id=' . $all['sender_id'];
        }
        try {
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

    public function retrieve_an_attachment_from_a_message(Request $request)
    {
        $all = $request->all();
        if (!isset($all['message_id']) || !isset($all['attachment_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $message_id = $all['message_id'];
        $attachment_id = $all['attachment_id'];
        $url = config('services.unipile.dsn') . 'api/v1/messages/' . $message_id . '/attachments' . '/' . $attachment_id;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'Accept' => '*/*',
                ],
            ]);
            $contentType = $all['mimetype'] ?? 'application/octet-stream';
            $filename =  $all['file_name'] ?? 'attachment';
            return response($response->getBody(), $response->getStatusCode())
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function list_all_attendees(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/chat_attendees?account_id=' . $account_id . '&';
        if (isset($all['cursor'])) {
            $url .= 'cursor=' . $all['cursor'] . '&';
        }
        if (isset($all['limit'])) {
            $url .= 'limit=' . $all['limit'];
        }
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $attendees = json_decode($response->getBody(), true);
            return response()->json(['attendees' => $attendees]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function retrieve_an_attendee(Request $request)
    {
        $all = $request->all();
        if (!isset($all['attendee_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $attendee_id = $all['attendee_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/chat_attendees/' . $attendee_id;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $attendee = json_decode($response->getBody(), true);
            return response()->json(['attendee' => $attendee]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function list_1_to_1_chats_from_attendee(Request $request)
    {
        $all = $request->all();
        if (!isset($all['attendee_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $attendee_id = $all['attendee_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/chat_attendees/' . $attendee_id . '/chats?';
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
        if (isset($all['account_id'])) {
            $url .= 'account_id=' . $all['account_id'];
        }
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $chats = json_decode($response->getBody(), true);
            return response()->json(['chats' => $chats]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function list_all_messages_from_attendee(Request $request)
    {
        $all = $request->all();
        if (!isset($all['attendee_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $attendee_id = $all['attendee_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/chat_attendees/' . $attendee_id . '/messages?';
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
        if (isset($all['account_id'])) {
            $url .= 'account_id=' . $all['account_id'];
        }
        try {
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

    public function list_all_invitaions(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/users/invite/sent?account_id=' . $account_id . '&';
        if (isset($all['cursor'])) {
            $url .= 'cursor=' . $all['cursor'] . '&';
        }
        if (isset($all['limit'])) {
            $url .= 'limit=' . $all['limit'];
        }
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $invitaions = json_decode($response->getBody(), true);
            return response()->json(['invitaions' => $invitaions]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function invite_to_connect(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];
        if (isset($all['message'])) {
            $message = $all['message'];
        } else {
            $message = '';
        }
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/users/invite';
        try {
            $response = $client->request('POST', $url, [
                'json' => [
                    'provider_id' => $identifier,
                    'account_id' => $account_id,
                    'message' => $message
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
            $invitaion = json_decode($response->getBody(), true);
            return response()->json(['invitaion' => $invitaion]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function sales_navigator_search(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $query = $all['query'];
        $account_id = $all['account_id'];
        $count = 80;
        $start = 0;
        if (isset($all['count']) && $all['count'] < 80) {
            $count = $all['count'];
        }
        if (isset($all['start'])) {
            $start = $all['start'];
        }
        $client = new Client([
            'verify' => true,
        ]);
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/linkedin', [
                'json' => [
                    'query_params' => [
                        'decorationId' => 'com.linkedin.sales.deco.desktop.searchv2.LeadSearchResult-14',
                        'query' => $query,
                        'count' => $count,
                        'start' => $start,
                        'q' => 'searchQuery',
                    ],
                    'account_id' => $account_id,
                    'method' => 'GET',
                    'request_url' => 'https://www.linkedin.com/sales-api/salesApiLeadSearch',
                    'encoding' => false
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['accounts' => $result['data']['elements']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }

    public function sales_navigator_search_by_url(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['url']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $client = new Client([
            'verify' => true,
        ]);
        $url = $all['url'];
        $account_id = $all['account_id'];
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/linkedin/search?account_id=' . $account_id, [
                'json' => [
                    'url' => $url,
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['accounts' => $result['data']['elements']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }

    private function queryToString($query)
    {
        $string = '';
        foreach ($query as $key => $value) {
            $string .= '(key:' . $key . ',value:List(';
            if (is_array($value)) {
                $string .= implode(',', $value);
            } else {
                $string .= $value;
            }
            $string .= ')),';
        }
        $string = rtrim($string, ',');
        return $string;
    }

    public function linkedin_search(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $query = $all['query'];
        $account_id = $all['account_id'];
        $start = 0;
        $origin = 'FACETED_SEARCH';
        $keywords = '';
        $queryParams = '';
        if (isset($all['start'])) {
            $start = $all['start'];
        }
        if (isset($query['origin'])) {
            $origin = $query['origin'];
            unset($query['origin']);
        }
        if (isset($query['keywords'])) {
            $keywords = 'keywords:' . $query['keywords'] . ',';
            unset($query['keywords']);
        }
        if (!empty($query)) {
            $queryParams = $this->queryToString($query);
        }
        $client = new Client([
            'verify' => true,
        ]);
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/linkedin', [
                'json' => [
                    'query_params' => [
                        'variables' => '(start:' . $start . ',origin:' . $origin . ',query:(' . $keywords . 'flagshipSearchIntent:SEARCH_SRP,queryParameters:List(' . $queryParams . ',(key:resultType,value:List(PEOPLE))),includeFiltersInResponse:false))',
                        'queryId' => 'voyagerSearchDashClusters.838ad2ecdec3b0347f493f93602336e9'
                    ],
                    'account_id' => $account_id,
                    'method' => 'GET',
                    'request_url' => 'https://www.linkedin.com/voyager/api/graphql',
                    'encoding' => false
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['accounts' => $result['data']['data']['searchDashClustersByAll']['elements']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function post_search(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];
        $client = new Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/posts/' . $identifier . '?account_id=' . $account_id;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['post' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function reactions_post_search(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];
        $client = new Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/posts/' . $identifier . '/reactions?account_id=' . $account_id;
        if (isset($all['cursor'])) {
            $url .= '&cursor=' . $all['cursor'];
        }
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['reactions' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function comments_post_search(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];
        $client = new Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/posts/' . $identifier . '/comments?account_id=' . $account_id;
        if (isset($all['cursor'])) {
            $url .= '&cursor=' . $all['cursor'];
        }
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['reactions' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function messages_search(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $keywords = $all['keywords'];
        $client = new Client([
            'verify' => true,
        ]);
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/linkedin', [
                'json' => [
                    'query_params' => [
                        'variables' => '(keyword:' . $keywords . ',types:List(CONNECTIONS))',
                        'queryId' => 'voyagerMessagingDashMessagingTypeahead.47f3aa32ab0b43221f99db7c350a2cc3'
                    ],
                    'account_id' => $account_id,
                    'method' => 'GET',
                    'request_url' => 'https://www.linkedin.com/voyager/api/graphql',
                    'encoding' => false
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['searches' => $result['data']['data']['messagingDashMessagingTypeaheadByTypeaheadKeyword']['elements']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function lead_list_search(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['search_url']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $client = new Client([
            'verify' => true,
        ]);
        $account_id = $all['account_id'];
        $url = config('services.unipile.dsn') . 'api/v1/linkedin/search?account_id=' . $account_id;
        if (isset($all['cursor']) && !is_null($all['cursor'])) {
            $url .= '&cursor=' . $all['cursor'];
        }
        $search_url = $all['search_url'];
        try {
            $response = $client->request('POST', $url, [
                'json' => [
                    'url' => $search_url
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['accounts' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function recruiter_search(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['search_url']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $account_id = $all['account_id'];
        $url = config('services.unipile.dsn') . 'api/v1/linkedin/search?account_id=' . $account_id;
        if (isset($all['cursor']) && !is_null($all['cursor'])) {
            $url .= '&cursor=' . $all['cursor'];
        }
        $search_url = $all['search_url'];
        try {
            $response = $client->request('POST', $url, [
                'json' => [
                    'url' => $search_url
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            return response()->json(['accounts' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function message(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];
        if (isset($all['message'])) {
            $message = $all['message'];
        } else {
            $message = '';
        }
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/chats', [
                'multipart' => [
                    [
                        'name' => 'attendees_ids',
                        'contents' => $identifier
                    ],
                    [
                        'name' => 'account_id',
                        'contents' => $account_id
                    ],
                    [
                        'name' => 'text',
                        'contents' => $message
                    ]
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $message = json_decode($response->getBody(), true);
            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function inmail_message(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];
        if (isset($all['message'])) {
            $message = $all['message'];
        } else {
            $message = '';
        }
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $url = config('services.unipile.dsn') . 'api/v1/users/me?account_id=' . $account_id;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            $profile = json_decode($response->getBody(), true);
            if ($profile['object'] == 'AccountOwnerProfile' && $profile['premium']) {
                $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/chats', [
                    'multipart' => [
                        [
                            'name' => 'attendees_ids',
                            'contents' => $identifier
                        ],
                        [
                            'name' => 'inmail',
                            'contents' => 'true'
                        ],
                        [
                            'name' => 'account_id',
                            'contents' => $account_id
                        ],
                        [
                            'name' => 'text',
                            'contents' => $message
                        ]
                    ],
                    'headers' => [
                        'X-API-KEY' => config('services.unipile.key'),
                        'accept' => 'application/json',
                    ],
                ]);
                $inmail_message = json_decode($response->getBody(), true);
                return response()->json(['inmail_message' => $inmail_message]);
            } else {
                return response()->json(['error' => 'For this feature must have premium account'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function email_message(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['email']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $email = $all['email'];
        if (isset($all['subject'])) {
            $subject = $all['subject'];
        } else {
            $subject = '';
        }
        if (isset($all['message'])) {
            $messageContent = $all['message'];
        } else {
            $messageContent = '';
        }
        $html = 'text/plain';
        if (isset($all['html']) && $all['html']) {
            $html = 'text/html';
        }
        try {
            Mail::send([], [], function ($mail) use ($email, $subject, $messageContent, $html) {
                $mail->to($email)
                    ->subject($subject)
                    ->setBody($messageContent, $html);
            });
            return response()->json(['success' => true, 'message' => 'Email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send email', 'details' => $e->getMessage()], 500);
        }
    }

    public function follow(Request $request)
    {
        $all = $request->all();
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        $request_url = "https://www.linkedin.com/voyager/api/feed/dash/followingStates/urn:li:fsd_followingState:urn:li:fsd_profile:" . $identifier;
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/linkedin', [
                'json' => [
                    'body' => [
                        'patch' => [
                            '$set' => [
                                'following' => true
                            ]
                        ]
                    ],
                    'account_id' => $account_id,
                    'method' => 'POST',
                    'request_url' => $request_url,
                    'encoding' => false
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
            $follow = json_decode($response->getBody(), true);
            return response()->json(['follow' => $follow]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function send_an_email(Request $request)
    {
        $all = $request->all();

        if (!isset($all['account_id']) || !isset($all['email']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        $account_id = $all['account_id'];
        $email = $all['email'];
        $subject = $all['subject'] ?? '';
        $messageContent = $all['message'] ?? '';
        $html = isset($all['html']) && $all['html'] ? 'text/html' : 'text/plain';

        $multipart = [
            [
                'name' => 'account_id',
                'contents' => $account_id
            ],
            [
                'name' => 'subject',
                'contents' => $subject
            ],
            [
                'name' => 'body',
                'contents' => $messageContent
            ],
            [
                'name' => 'to',
                'contents' => json_encode([
                    [
                        'display_name' => '',
                        'identifier' => $email
                    ]
                ])
            ]
        ];

        if ((isset($all['track']) && $all['track']) || (isset($all['link']) && $all['link'])) {
            $multipart[] = [
                'name' => 'tracking_options',
                'contents' => json_encode([
                    'opens' => $all['track'],
                    'links' => $all['link'],
                    'label' => 'tracking'
                ])
            ];
        }

        $client = new \GuzzleHttp\Client(['verify' => true]);

        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/emails', [
                'multipart' => $multipart,
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'Accept' => 'application/json',
                ],
            ]);

            return response()->json(['message' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function list_all_webhook(Request $request)
    {
        if (!config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        $client = new \GuzzleHttp\Client(['verify' => true]);

        /* Construct the API URL using the account_id and DSN from the config */
        $url = config('services.unipile.dsn') . 'api/v1/webhooks';

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'Accept' => 'application/json',
                ],
            ]);

            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete_webhook(Request $request)
    {
        $all = $request->all();
        if (!$all['webhook_id'] || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        $webhook_id = $all['webhook_id'];

        $client = new \GuzzleHttp\Client(['verify' => true]);

        /* Construct the API URL using the account_id and DSN from the config */
        $url = config('services.unipile.dsn') . 'api/v1/webhooks/' . $webhook_id;
        try {
            $response = $client->request('DELETE', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create_messaging_webhook(Request $request)
    {
        $all = $request->all();
        if (!isset($all['request_url']) || !isset($all['name']) || !isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        $client = new \GuzzleHttp\Client(['verify' => true]);
        $events = $all['events'] ?? ['message_received', 'message_read', 'message_reaction'];
        $bodyData = [
            'source' => 'messaging',
            'request_url' => $all['request_url'],
            'name' => $all['name'],
            'account_ids' => [$all['account_id']],
            'headers' => $all['headers'] ?? [],
            'events' => $events,
            'data' => [
                [
                    'name' => 'account_id',
                    'key' => 'account_id'
                ]
            ]
        ];
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/webhooks', [
                'json' => $bodyData,
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create_email_webhook(Request $request)
    {
        $all = $request->all();
        if (!isset($all['request_url']) || !isset($all['name']) || !isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $accountIds = is_array($all['account_id']) ? $all['account_id'] : [$all['account_id']];
        $client = new \GuzzleHttp\Client(['verify' => true]);
        $events = $all['events'] ?? ['mail_sent', 'mail_received'];
        $bodyData = [
            'source' => 'email',
            'request_url' => $all['request_url'],
            'name' => $all['name'],
            'account_ids' => $accountIds,
            'headers' => $all['headers'] ?? [],
            'events' => $events,
            'data' => [
                [
                    'name' => 'account_id',
                    'key' => 'account_id'
                ]
            ]
        ];
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/webhooks', [
                'json' => $bodyData,
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create_tracking_webhook(Request $request)
    {
        $all = $request->all();
        if (!isset($all['request_url']) || !isset($all['name']) || !isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        $accountIds = is_array($all['account_id']) ? $all['account_id'] : [$all['account_id']];
        $client = new \GuzzleHttp\Client(['verify' => true]);
        $events = $all['events'] ?? ['mail_opened', 'mail_link_clicked'];
        $bodyData = [
            'source' => 'email_tracking',
            'request_url' => $all['request_url'],
            'name' => $all['name'],
            'account_ids' => $accountIds,
            'headers' => $all['headers'] ?? [],
            'events' => $events,
            'data' => [
                [
                    'name' => 'account_id',
                    'key' => 'account_id'
                ]
            ]
        ];
        try {
            $response = $client->request('POST', config('services.unipile.dsn') . 'api/v1/webhooks', [
                'json' => $bodyData,
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
