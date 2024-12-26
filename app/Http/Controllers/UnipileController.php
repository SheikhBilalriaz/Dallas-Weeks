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
        $url = config('services.unipile.dsn') . '/api/v1/accounts/' . $account_id . '?port=13443';
        
        try {
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
        $url = config('services.unipile.dsn') . '/api/v1/users/me?account_id=' . $account_id . '&port=13443';

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
        $url = config('services.unipile.dsn') . '/api/v1/accounts/' . $account_id . '?port=13443';

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
        $url = config('services.unipile.dsn') . '/api/v1/chats?account_id=' . $account_id . '&port=13443';

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
            $url = config('services.unipile.dsn') . '/api/v1/users/' . $profile_url . '?linkedin_api=sales_navigator&linkedin_sections=%2A&notify=' . $notify . '&account_id=' . $account_id . '&port=13443';
        } else {
            if (strpos($profile_url, 'https://www.linkedin.com/company/') !== false) {
                $profile_url = str_replace('https://www.linkedin.com/company/', config('services.unipile.dsn') . '/api/v1/linkedin/company/', $profile_url);
            } else if (strpos($profile_url, 'https://www.linkedin.com/in/') !== false) {
                $profile_url = str_replace('https://www.linkedin.com/in/', config('services.unipile.dsn') . '/api/v1/users/', $profile_url);
            } else {
                $profile_url =  config('services.unipile.dsn') . '/api/v1/users/' . $profile_url;
            }
            $url = $profile_url . '?linkedin_sections=%2A&notify=' . $notify . '&account_id=' . $account_id . '&port=13443';
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
            $url .= 'sender_id=' . $all['sender'] . '&';
        }
        $url .= 'port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/users/relations' . '?account_id=' . $account_id . '&';
        if (isset($all['cursor'])) {
            $url .= 'cursor=' . $all['cursor'] . '&';
        }
        if (isset($all['limit'])) {
            $url .= 'limit=' . $all['limit'] . '&';
        }
        $url .= 'port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/accounts?';
        if (isset($all['cursor'])) {
            $url .= 'cursor=' . $all['cursor'] . '&';
        }
        if (isset($all['limit'])) {
            $url .= 'limit=' . $all['limit'] . '&';
        }
        $url .= 'port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/accounts/' . $account_id . '/restart' . '?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chats?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chats/' . $chat_id . '?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chats/' . $chat_id . '/messages?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chats/' . $chat_id . '/attendees?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chats/' . $chat_id . '?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/messages/' . $message_id . '?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/messages?account_id=' . $account_id . '&';
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
            $url .= 'sender_id=' . $all['sender_id'] . '&';
        }
        $url .= 'port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/messages/' . $message_id . '/attachments' . '/' . $attachment_id . '?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chat_attendees?account_id=' . $account_id . '&';
        if (isset($all['cursor'])) {
            $url .= 'cursor=' . $all['cursor'] . '&';
        }
        if (isset($all['limit'])) {
            $url .= 'limit=' . $all['limit'] . '&';
        }
        $url .= 'port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chat_attendees/' . $attendee_id . '?port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chat_attendees/' . $attendee_id . '/chats?';
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
            $url .= 'account_id=' . $all['account_id'] . '&';
        }
        $url .= 'port=13443';
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
        $url = config('services.unipile.dsn') . '/api/v1/chat_attendees/' . $attendee_id . '/messages?';
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
            $url .= 'account_id=' . $all['account_id'] . '&';
        }
        $url .= 'port=13443';
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
        // Retrieve all input parameters from the request
        $all = $request->all();
        
        // Check if required parameters are set in the request (account_id, Unipile API key, and DSN URL)
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        // Get the 'account_id' from the request
        $account_id = $all['account_id'];
        
        // Create a new Guzzle HTTP client with SSL verification enabled
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        
        // Construct the URL for the Unipile API's 'list all invitations' endpoint
        $url = config('services.unipile.dsn') . '/api/v1/users/invite/sent?account_id=' . $account_id . '&';
        
        // If a 'cursor' parameter is provided, append it to the URL for pagination
        if (isset($all['cursor'])) {
            $url .= 'cursor=' . $all['cursor'] . '&';
        }
        
        // If a 'limit' parameter is provided, append it to the URL to limit the number of results
        if (isset($all['limit'])) {
            $url .= 'limit=' . $all['limit'] . '&';
        }
        
        // Append the 'port' parameter to the URL
        $url .= 'port=13443';
        
        try {
            // Send a GET request to the Unipile API to fetch the list of sent invitations
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            
            // Decode the JSON response from the API into an associative array
            $invitaions = json_decode($response->getBody(), true);
            
            // Return the list of invitations in a JSON response
            return response()->json(['invitaions' => $invitaions]);
        } catch (\Exception $e) {
            // Catch any exceptions during the API request and return an error response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function invite_to_connect(Request $request)
    {
        // Retrieve all input parameters from the request
        $all = $request->all();
        
        // Check if required parameters are set in the request (account_id, identifier, Unipile API key, and DSN URL)
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        // Get the 'account_id' and 'identifier' from the request
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];
        
        // If 'message' is provided in the request, use it; otherwise, set it to an empty string
        if (isset($all['message'])) {
            $message = $all['message'];
        } else {
            $message = '';
        }
        
        // Create a new Guzzle HTTP client with SSL verification enabled
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        
        // Construct the URL for the Unipile API's 'invite' endpoint
        $url = config('services.unipile.dsn') . '/api/v1/users/invite?port=13443';
        
        try {
            // Send a POST request to the Unipile API to send an invitation to connect
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
            
            // Decode the JSON response from the API into an associative array
            $invitaion = json_decode($response->getBody(), true);
            
            // Return the invitation data in a JSON response
            return response()->json(['invitaion' => $invitaion]);
        } catch (\Exception $e) {
            // Catch any exceptions during the API request and return an error response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function sales_navigator_search(Request $request)
    {
        // Retrieve all input parameters from the request
        $all = $request->all();
        
        // Check if required parameters are set in the request (account_id, Unipile API key, and DSN URL)
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        // Get the 'query' parameter and 'account_id' from the request
        $query = $all['query'];
        $account_id = $all['account_id'];
        
        // Set default values for 'count' (max number of results) and 'start' (pagination offset)
        $count = 80;
        $start = 0;
        
        // If 'count' is provided and is less than 80, update the 'count' value
        if (isset($all['count']) && $all['count'] < 80) {
            $count = $all['count'];
        }
        
        // If 'start' is provided, update the 'start' value for pagination
        if (isset($all['start'])) {
            $start = $all['start'];
        }
        
        // Create a new HTTP client with SSL verification enabled
        $client = new Client([
            'verify' => true,
        ]);
        
        // Construct the URL for the LinkedIn Sales Navigator search API endpoint
        $url = config('services.unipile.dsn') . '/api/v1/linkedin?port=13443';
        
        try {
            // Send a POST request to the LinkedIn Sales Navigator API with the provided query parameters
            $response = $client->request('POST', $url, [
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
            
            // Decode the JSON response from the API into an associative array
            $result = json_decode($response->getBody(), true);
            
            // Return the list of accounts from the API response
            return response()->json(['accounts' => $result['data']['elements']]);
        } catch (\Exception $e) {
            // Return an error message if there was an exception during the API request
            return response()->json(['error' => $e], 400);
        }
    }

    public function sales_navigator_search_by_url(Request $request)
    {
        // Retrieve all input parameters from the request
        $all = $request->all();
        
        // Check if required parameters are set in the request, otherwise return an error
        if (!isset($all['account_id']) || !isset($all['url']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        // Create a new HTTP client with SSL verification enabled
        $client = new Client([
            'verify' => true,
        ]);
        
        // Get the URL and account_id from the request
        $url = $all['url'];
        $account_id = $all['account_id'];
        
        // Construct the new URL for the LinkedIn search API endpoint
        $new_url = config('services.unipile.dsn') . '/api/v1/linkedin/search?account_id=' . $account_id . '&port=13443';
        
        try {
            // Send a POST request to the LinkedIn search API with the provided URL
            $response = $client->request('POST', $new_url, [
                'json' => [
                    'url' => $url,
                ],
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ],
            ]);

            // Decode the JSON response from the API into an associative array
            $result = json_decode($response->getBody(), true);
            
            // Return the accounts data from the response
            return response()->json(['accounts' => $result['data']['elements']]);
        } catch (\Exception $e) {
            // Return an error message if there was an exception during the API request
            return response()->json(['error' => $e], 400);
        }
    }

    private function queryToString($query)
    {
        // Initialize an empty string to store the formatted query string
        $string = '';
        
        // Iterate over each key-value pair in the $query array
        foreach ($query as $key => $value) {
            $string .= '(key:' . $key . ',value:List(';
            if (is_array($value)) {
                $string .= implode(',', $value);
            } else {
                $string .= $value;
            }
            $string .= ')),';
        }
        
        // Remove the trailing comma from the end of the string (if any)
        $string = rtrim($string, ',');
        
        // Return the formatted query string
        return $string;
    }

    public function linkedin_search(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();
        
        // Validate required parameters: account_id and necessary configuration values
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        // Extract the 'query' and 'account_id' from the request
        $query = $all['query'];
        $account_id = $all['account_id'];
        
        // Set default values for 'start', 'origin', and 'keywords'
        $start = 0;
        $origin = 'FACETED_SEARCH';
        $keywords = '';
        $queryParams = '';
        
        // Check if 'start' is provided in the request, otherwise use the default value
        if (isset($all['start'])) {
            $start = $all['start'];
        }
        
        // If 'origin' is present in the query, use it and remove it from the query array
        if (isset($query['origin'])) {
            $origin = $query['origin'];
            unset($query['origin']);
        }
        
        // If there are remaining query parameters, convert them to a query string
        if (isset($query['keywords'])) {
            $keywords = 'keywords:' . $query['keywords'] . ',';
            unset($query['keywords']);
        }
        
        // If there are remaining query parameters, convert them to a query string
        if (!empty($query)) {
            $queryParams = $this->queryToString($query);
        }
        
        // Initialize a Guzzle HTTP client to make API requests
        $client = new Client([
            'verify' => true,
        ]);
        
        // Build the URL for the Unipile API request
        $url = config('services.unipile.dsn') . '/api/v1/linkedin?port=13443';
        
        try {
            // Make a POST request to the Unipile API to search for LinkedIn accounts using the constructed URL
            $response = $client->request('POST', $url, [
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
            
            // Decode the JSON response from the Unipile API
            $result = json_decode($response->getBody(), true);
            
            // Return the search results (LinkedIn accounts) from the response
            return response()->json(['accounts' => $result['data']['data']['searchDashClustersByAll']['elements']]);
        } catch (\Exception $e) {
            // Catch any exceptions (e.g., network issues, API errors) and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function post_search(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();
        
        // Validate required parameters: account_id, identifier, and necessary configuration values
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract the 'account_id' from the request
        $account_id = $all['account_id'];

        // Extract the 'identifier' for the specific post to search
        $identifier = $all['identifier'];

        // Initialize a Guzzle HTTP client to make API requests
        $client = new Client([
            'verify' => true,
        ]);
        
        // Build the URL for the Unipile API request, appending the identifier and account_id
        $url = config('services.unipile.dsn') . '/api/v1/posts/' . $identifier . '?account_id=' . $account_id . '&port=13443';
        
        try {
            // Make a GET request to the Unipile API to fetch the post details using the constructed URL
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);
            
            // Decode the JSON response from the Unipile API
            $result = json_decode($response->getBody(), true);
            
            // Return the post details from the response
            return response()->json(['post' => $result]);
        } catch (\Exception $e) {
            // Catch any exceptions (e.g., network issues, API errors) and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function reactions_post_search(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters: account_id, identifier, and necessary configuration values
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract the 'account_id' from the request
        $account_id = $all['account_id'];

        // Extract the 'identifier' for the specific post to search reactions
        $identifier = $all['identifier'];

        // Initialize a Guzzle HTTP client to make API requests
        $client = new Client([
            'verify' => true,
        ]);

        // Build the URL for the Unipile API request, appending the identifier and account_id
        $url = config('services.unipile.dsn') . '/api/v1/posts/' . $identifier . '/reactions?account_id=' . $account_id;
        
        // If 'cursor' parameter is provided in the request, append it to the URL for pagination
        if (isset($all['cursor'])) {
            $url .= '&cursor=' . $all['cursor'];
        }

        // Append the port to the URL
        $url .= '&port=13443';
        
        try {
            // Make a GET request to the Unipile API to fetch reactions for the specified post
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);

            // Decode the JSON response from the Unipile API
            $result = json_decode($response->getBody(), true);

            // Return the reactions from the response
            return response()->json(['reactions' => $result]);
        } catch (\Exception $e) {
            // Catch any exceptions and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function comments_post_search(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters: account_id, identifier, and necessary configuration values
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract the 'account_id' from the request
        $account_id = $all['account_id'];

        // Extract the 'identifier' for the specific post to search comments
        $identifier = $all['identifier'];

        // Initialize a Guzzle HTTP client to make API requests
        $client = new Client([
            'verify' => true,
        ]);

        // Build the URL for the Unipile API request, appending the identifier and account_id
        $url = config('services.unipile.dsn') . '/api/v1/posts/' . $identifier . '/comments?account_id=' . $account_id;
        
        // If 'cursor' parameter is provided in the request, append it to the URL for pagination
        if (isset($all['cursor'])) {
            $url .= '&cursor=' . $all['cursor'];
        }

        // Append the port to the URL
        $url .= '&port=13443';
        
        try {
            // Make a GET request to the Unipile API to fetch comments for the specified post
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);

            // Decode the JSON response from the Unipile API
            $result = json_decode($response->getBody(), true);

            // Return the comments (reactions) from the response
            return response()->json(['reactions' => $result]);
        } catch (\Exception $e) {
            // Catch any exceptions and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function messages_search(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters: account_id, and necessary configuration values
        if (!isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract the 'account_id' from the request
        $account_id = $all['account_id'];

        // Extract the 'keywords' for searching from the request
        $keywords = $all['keywords'];

        // Initialize a Guzzle HTTP client to make API requests
        $client = new Client([
            'verify' => true,
        ]);

        // Build the URL for the Unipile LinkedIn API request
        $url = config('services.unipile.dsn') . '/api/v1/linkedin?port=13443';
        
        try {
            // Make a POST request to the Unipile API to search messages using the provided keywords
            $response = $client->request('POST', $url, [
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

            // Decode the JSON response from the Unipile API
            $result = json_decode($response->getBody(), true);

            // Return the search results from the response
            return response()->json(['searches' => $result['data']['data']['messagingDashMessagingTypeaheadByTypeaheadKeyword']['elements']]);
        } catch (\Exception $e) {
            // Catch any exceptions and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function lead_list_search(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters: account_id, search_url, and necessary configuration values
        if (!isset($all['account_id']) || !isset($all['search_url']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Initialize a Guzzle HTTP client to make API requests
        $client = new Client([
            'verify' => true,
        ]);

        // Extract the 'account_id' from the request
        $account_id = $all['account_id'];

        // Build the URL for the LinkedIn search API request
        $url = config('services.unipile.dsn') . '/api/v1/linkedin/search?account_id=' . $account_id;
        
        // If a cursor is provided in the request, append it to the URL for pagination
        if (isset($all['cursor']) && !is_null($all['cursor'])) {
            $url .= '&cursor=' . $all['cursor'];
        }

        // Append port parameter to the URL
        $url .= '&port=13443';

        // Extract the 'search_url' from the request, which specifies the LinkedIn search URL
        $search_url = $all['search_url'];
        
        try {
            // Make a POST request to the Unipile API to perform the LinkedIn lead search
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

            // Decode the JSON response from the Unipile API
            $result = json_decode($response->getBody(), true);

            // Return the search results as a JSON response
            return response()->json(['accounts' => $result]);
        } catch (\Exception $e) {
            // Catch any exceptions and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function recruiter_search(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters: account_id, search_url, and necessary configuration values
        if (!isset($all['account_id']) || !isset($all['search_url']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Initialize a Guzzle HTTP client to make API requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);

        // Extract the 'account_id' from the request
        $account_id = $all['account_id'];

        // Build the URL for the LinkedIn search API request
        $url = config('services.unipile.dsn') . '/api/v1/linkedin/search?account_id=' . $account_id;
        
        // If a cursor is provided in the request, append it to the URL for pagination
        if (isset($all['cursor']) && !is_null($all['cursor'])) {
            $url .= '&cursor=' . $all['cursor'];
        }

        // Append port parameter to the URL
        $url .= '&port=13443';

        // Extract the 'search_url' from the request
        $search_url = $all['search_url'];
        
        try {
            // Make a POST request to the Unipile API to perform the LinkedIn recruiter search
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

            // Decode the JSON response from the Unipile API
            $result = json_decode($response->getBody(), true);

            // Return the search results as a JSON response
            return response()->json(['accounts' => $result]);
        } catch (\Exception $e) {
            // Catch any exceptions and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function message(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters: account_id, identifier, and necessary configuration values
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract the 'account_id' and 'identifier' from the request
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];

        // Extract the 'message' parameter from the request, defaulting to an empty string if not provided
        $message = isset($all['message']) ? $all['message'] : '';

        // Initialize a Guzzle HTTP client to make API requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);

        // Construct the URL
        $url = config('services.unipile.dsn') . '/api/v1/chats?port=13443';
        
        try {
            // Make a POST request to the Unipile API to send a message in a chat
            $response = $client->request('POST', $url, [
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

            // Decode the response body as JSON
            $message = json_decode($response->getBody(), true);

            // Return the message in the response
            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            // Catch any exceptions and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function inmail_message(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();
        
        // Validate required parameters: account_id, identifier, and necessary configuration values
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract the 'account_id' and 'identifier' from the request
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];

        // Extract the 'message' parameter from the request, defaulting to an empty string if not provided
        $message = isset($all['message']) ? $all['message'] : '';

        // Initialize a Guzzle HTTP client to make API requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);

        // Construct the URL to get the profile details for the provided 'account_id'
        $url = config('services.unipile.dsn') . '/api/v1/users/me?account_id=' . $account_id . '&port=13443';
        
        try {
            // Make a GET request to retrieve the profile information
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);

            // Decode the response body as JSON
            $profile = json_decode($response->getBody(), true);

            // Check if the profile is of type 'AccountOwnerProfile' and the account is premium
            if ($profile['object'] == 'AccountOwnerProfile' && $profile['premium']) {
                // Construct a new URL for sending the InMail message
                $new_url = config('services.unipile.dsn') . '/api/v1/chats?port=13443';

                // Make a POST request to send the InMail message
                $response = $client->request('POST', $new_url, [
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

                // Decode the InMail response body as JSON
                $inmail_message = json_decode($response->getBody(), true);

                // Return the InMail message in the response
                return response()->json(['inmail_message' => $inmail_message]);
            } else {
                // Return an error response if the account is not premium
                return response()->json(['error' => 'For this feature must have premium account'], 400);
            }
        } catch (\Exception $e) {
            // Catch any exceptions and return the error message in the response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function email_message(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters
        if (!isset($all['account_id']) || !isset($all['email']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract required parameters from the request
        $account_id = $all['account_id'];
        $email = $all['email'];

        // Extract optional parameters
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

        // Default email content type is 'text/plain'
        $html = 'text/plain';
        // If 'html' is set and true, use 'text/html' as the content type
        if (isset($all['html']) && $all['html']) {
            $html = 'text/html';
        }
        
        try {
            // Send the email using Laravel's Mail facade
            Mail::send([], [], function ($mail) use ($email, $subject, $messageContent, $html) {
                $mail->to($email)
                    ->subject($subject)
                    ->setBody($messageContent, $html);
            });

            // Return a success response if the email is sent successfully
            return response()->json(['success' => true, 'message' => 'Email sent successfully']);
        } catch (\Exception $e) {
            // Catch any exceptions that occur during email sending
            return response()->json(['error' => 'Failed to send email', 'details' => $e->getMessage()], 500);
        }
    }

    public function follow(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters
        if (!isset($all['account_id']) || !isset($all['identifier']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract required parameters from the request
        $account_id = $all['account_id'];
        $identifier = $all['identifier'];

        // Initialize the Guzzle HTTP client for making external API requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);

        // Construct the LinkedIn API request URL using the profile identifier
        $request_url = "https://www.linkedin.com/voyager/api/feed/dash/followingStates/urn:li:fsd_followingState:urn:li:fsd_profile:" . $identifier;
        
        // Construct the API URL dynamically using the DSN from the configuration
        $url = config('services.unipile.dsn') . '/api/v1/linkedin?port=13443';
        
        try {
            // Send a POST request to the Unipile API to follow the LinkedIn profile
            $response = $client->request('POST', $url, [
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

            // Decode the API response body from JSON format
            $follow = json_decode($response->getBody(), true);

            // Return a success response with the follow action result
            return response()->json(['follow' => $follow]);
        } catch (\Exception $e) {
            // Catch any exceptions that occur during the API request
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function send_an_email(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters
        if (!isset($all['account_id']) || !isset($all['email']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract required parameters from the request
        $account_id = $all['account_id'];
        $email = $all['email'];
        
        // Extract optional parameters with defaults
        $subject = $all['subject'] ?? '';
        $messageContent = $all['message'] ?? '';
        $html = isset($all['html']) && $all['html'] ? 'text/html' : 'text/plain';
        
        // Construct the multipart data for the email request
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
        
        // Add tracking options if provided in the request
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
        
        // Initialize the Guzzle HTTP client for making external API requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        
        // Construct the API URL dynamically using the DSN from the configuration
        $url = config('services.unipile.dsn') . '/api/v1/emails?port=13443';
        
        try {
            // Send a POST request to the email API with the constructed multipart data
            $response = $client->request('POST', $url, [
                'multipart' => $multipart,
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'Accept' => 'application/json',
                ],
            ]);
            
            // Decode the API response body from JSON format and return it as a JSON response
            return response()->json(['message' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            // Catch any exceptions that occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function list_all_webhook(Request $request)
    {
        // Ensure that the required Unipile API key and DSN are available in the configuration
        if (!config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
         // Initialize the Guzzle HTTP client for sending API requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);

        /* Construct the API URL using the account_id and DSN from the config */
        $url = config('services.unipile.dsn') . '/api/v1/webhooks?port=13443';

        try {
            // Send a GET request to the Unipile API to fetch the list of all webhooks
            $response = $client->request('GET', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'Accept' => 'application/json',
                ],
            ]);
            
            // Decode the API response body from JSON to an array and return it as a JSON response
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            // Catch any exceptions that occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete_webhook(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();

        // Validate required parameters
        if (!$all['webhook_id'] || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Extract the webhook ID from the request
        $webhook_id = $all['webhook_id'];

        // Initialize the Guzzle HTTP client for making external API requests
        $client = new \GuzzleHttp\Client(['verify' => true]);

        // Construct the API URL dynamically using the DSN from the configuration and the provided webhook ID
        $url = config('services.unipile.dsn') . '/api/v1/webhooks/' . $webhook_id . '?port=13443';
        
        try {
            // Send a DELETE request to the API to delete the specified webhook
            $response = $client->request('DELETE', $url, [
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                ],
            ]);

            // Decode the API response body from JSON and return it as a JSON response
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            // Catch any exceptions that occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create_messaging_webhook(Request $request)
    {
        // Retrieve all input data from the incoming request
        $all = $request->all();
        
        // Validate required parameters and configuration values
        if (!isset($all['request_url']) || !isset($all['name']) || !isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        // Initialize the Guzzle HTTP client for making external API requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        
        // Set default event types if 'events' are not provided in the request
        $events = $all['events'] ?? ['message_received', 'message_read', 'message_reaction'];
        
        // Prepare the request payload for creating the webhook
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
        
        // Construct the API URL using the DSN from the configuration
        $url = config('services.unipile.dsn') . '/api/v1/webhooks?port=13443';
        
        try {
            // Send a POST request to the Unipile API to create the webhook
            $response = $client->request('POST', $url, [
                'json' => $bodyData,
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
            
            // Decode the API response body from JSON and return it as a JSON response
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            // Catch any exceptions that occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create_email_webhook(Request $request)
    {
        // Retrieve all input data from the incoming request
        $all = $request->all();
        
        // Validate required parameters and configuration values
        if (!isset($all['request_url']) || !isset($all['name']) || !isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        // Convert 'account_id' into an array if it is not already an array
        $accountIds = is_array($all['account_id']) ? $all['account_id'] : [$all['account_id']];
        
        // Initialize the Guzzle HTTP client for making external API requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        
        // Set default event types if 'events' are not provided in the request
        $events = $all['events'] ?? ['mail_sent', 'mail_received'];
        
        // Prepare the request payload for creating the webhook
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

        // Construct the API URL using the DSN from the configuration
        $url = config('services.unipile.dsn') . '/api/v1/webhooks?port=13443';
        
        try {
            // Send a POST request to the Unipile API to create the webhook
            $response = $client->request('POST', $url, [
                'json' => $bodyData,
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            // Decode the API response body from JSON and return it as a JSON response
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            // Catch any exceptions that occur during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create_tracking_webhook(Request $request)
    {
        // Retrieve all input data from the request
        $all = $request->all();
        
        // Validate required parameters and configuration values
        if (!isset($all['request_url']) || !isset($all['name']) || !isset($all['account_id']) || !config('services.unipile.key') || !config('services.unipile.dsn')) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }
        
        // Convert 'account_id' into an array if it's not already an array
        $accountIds = is_array($all['account_id']) ? $all['account_id'] : [$all['account_id']];
        
        // Initialize the Guzzle HTTP client for sending requests
        $client = new \GuzzleHttp\Client([
            'verify' => true,
        ]);
        
        // Set default events if 'events' is not provided in the request
        $events = $all['events'] ?? ['mail_opened', 'mail_link_clicked'];
        
        // Prepare the data payload for the webhook request
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

        // Define the Unipile API URL, including the necessary port number
        $url = config('services.unipile.dsn') . '/api/v1/webhooks?port=13443';
        
        try {
            // Send a POST request to the Unipile API to create the webhook
            $response = $client->request('POST', $url, [
                'json' => $bodyData,
                'headers' => [
                    'X-API-KEY' => config('services.unipile.key'),
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);

            // Parse the response body and return it as a JSON response
            return response()->json(['webhook' => json_decode($response->getBody(), true)]);
        } catch (\Exception $e) {
            // Catch and handle any exceptions that occur during the request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
