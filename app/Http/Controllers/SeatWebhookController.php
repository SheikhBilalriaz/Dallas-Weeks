<?php

namespace App\Http\Controllers;

use App\Models\Email_Integraion;
use App\Models\Linkedin_Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;
use App\Models\Seat;
use App\Models\Webhook;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class SeatWebhookController extends Controller
{
    public function webhook($slug, $seat_slug)
    {
        try {
            /* Retrieve the team and seat by their slugs */
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $seat_webhooks = Webhook::where('seat_id', $seat->id)->get();
            $seat_webhook_map = $seat_webhooks->keyBy('webhook_id');
            $uc = new UnipileController();
            $final_webhooks = [];
            do {
                $webhooks = $uc->list_all_webhook(new \Illuminate\Http\Request([]))->getData(true)['webhook'];
                $items = $webhooks['items'];
                foreach ($items as $webhook) {
                    if (isset($seat_webhook_map[$webhook['id']])) {
                        $seat_webhook_map[$webhook['id']]->webhook = $webhook;
                    }
                }
            } while (isset($webhooks['cursor']) && $webhooks['cursor'] !== null);
            $final_webhooks = $seat_webhook_map->values();
            /* Retrieve all email integrations for the seat */
            $integrated_emails = Email_Integraion::where('seat_id', $seat->id)->get();

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
            }

            /* Prepare data to pass to the view */
            $data = [
                'title' => 'Dashboard - Webhook',
                'team' => $team,
                'seat' => $seat,
                'webhooks' => $final_webhooks,
                'emails' => $integrated_emails,
            ];

            /* Return the view with the seat data */
            return view('back.webhook', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function deleteWebhook($slug, $seat_slug, $id)
    {
        try {
            $seat = Seat::where('slug', $seat_slug)->first();

            /* Begin a database transaction */
            DB::beginTransaction();

            $uc = new UnipileController();

            $webhook = Webhook::where('id', $id)->where('seat_id', $seat->id)->firstOrFail();

            $delete_webhook = $uc->delete_webhook(new \Illuminate\Http\Request(['webhook_id' => $webhook->webhook_id]))->getData(true);

            if (isset($delete_webhook['error'])) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
            }

            $webhook->delete();

            /* Commit the transaction */
            DB::commit();

            /* Return a success response */
            return response()->json(['success' => true, 'message' => 'Blacklist item deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            /* Return a 404 Not Found response if the item does not exist */
            return response()->json(['error' => 'Webhook item not found.'], 404);
        } catch (Exception $e) {
            /* Rollback the transaction if something went wrong */
            DB::rollBack();

            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function createWebhook($slug, $seat_slug, Request $request)
    {

        try {
            $seat = Seat::where('slug', $seat_slug)->first();
            $validator = Validator::make($request->all(), [
                'call_back' => 'required',
                'name' => 'required|max:191',
                'desc' => 'required|max:191',
                'webhook_selection' => 'required|max:191',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with(['webhook_model' => true]);
            }
            if ($request->input('webhook_selection') !== 'messaging' && !$request->has('accounts')) {
                return back()->withErrors(['webhook_selection' => 'Required accounts selection if webhook is for email'])
                    ->withInput()
                    ->with(['webhook_model' => true]);
            }
            $uc = new UnipileController();
            $webhook_response = null;
            switch ($request->input('webhook_selection')) {
                case 'messaging':
                    $linkedin_integration = Linkedin_Integration::where('seat_id', $seat->id)->first();
                    $webhook_request['account_id'] = $linkedin_integration['account_id'];
                    $webhook_request['request_url'] = $request->input('call_back');
                    $webhook_request['name'] = $request->input('name');
                    $webhook_response = $uc->create_messaging_webhook(new \Illuminate\Http\Request($webhook_request))->getData(true);
                    break;
                case 'mailing':
                    $email_integartion = Email_Integraion::where('seat_id', $seat->id)
                        ->whereIn('id', $request->input('accounts'))
                        ->get();
                    $webhook_request['account_id'] = $email_integartion->pluck('account_id')->toArray();
                    $webhook_request['request_url'] = $request->input('call_back');
                    $webhook_request['name'] = $request->input('name');
                    $webhook_response = $uc->create_email_webhook(new \Illuminate\Http\Request($webhook_request))->getData(true);
                    break;
                case 'mail_tracking':
                    $email_integartion = Email_Integraion::where('seat_id', $seat->id)
                        ->whereIn('id', $request->input('accounts'))
                        ->get();
                    $webhook_request['account_id'] = $email_integartion->pluck('account_id')->toArray();
                    $webhook_request['request_url'] = $request->input('call_back');
                    $webhook_request['name'] = $request->input('name');
                    $webhook_response = $uc->create_tracking_webhook(new \Illuminate\Http\Request($webhook_request))->getData(true);
                    break;
            }
            if (!isset($webhook_response['error'])) {
                Webhook::create([
                    'creator_id' => Auth::user()->id,
                    'seat_id' => $seat->id,
                    'name' => $request->input('name'),
                    'reason' => $request->input('desc'),
                    'webhook_id' => $webhook_response['webhook']['webhook_id']
                ]);
                return redirect()->route('webhookPage', ['slug' => $slug, 'seat_slug' => $seat_slug])->with(['success', 'Webhook created successfully']);
            }
            return redirect()->route('webhookPage', ['slug' => $slug, 'seat_slug' => $seat_slug])->with(['error', 'Something went wrong']);
        } catch (Exception $e) {
            /* Rollback the transaction if something went wrong */
            DB::rollBack();

            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a generic error response */
            return redirect()->route('webhookPage', ['slug' => $slug, 'seat_slug' => $seat_slug])->with(['error', 'Something went wrong']);
        }
    }
}
