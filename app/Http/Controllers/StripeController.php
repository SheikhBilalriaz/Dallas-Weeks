<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Company_Info;
use App\Models\Seat;
use App\Models\Seat_Info;
use App\Models\Team;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionSuccessMail;
use App\Models\Invoice;
use App\Models\User;

class StripeController extends Controller
{
    /**
     * Handle the Stripe webhook.
     *
     * @param Illuminate\Http\Request $request
     * @return Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        try {
            $event = Webhook::constructEvent($payload, $signature, config('services.stripe.webhook_key'));

            /* Handle the event based on its type */
            $this->handleEvent($event);

            return response('Webhook handled', 200);
        } catch (\UnexpectedValueException $e) {
            Log::error($e);
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error($e);
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error($e);
            return response('Server Error', 500);
        }
    }

    /**
     * Handle the Stripe webhook event based on its type.
     *
     * @param \Stripe\Event $event
     * @return void
     */
    private function handleEvent($event)
    {
        $subscription = $event->data->object;

        switch ($event->type) {
            case 'customer.subscription.created':
                $this->customerSubscriptionCreated($subscription);
                break;
            case 'customer.subscription.deleted':
            case 'customer.subscription.updated':
                $this->logSubscriptionInfo($event->type, $subscription);
                break;
            case 'invoice.payment_failed':
                Log::info('*******Invoice payment failed*********');
                Log::info('Payment failed for subscription: ' . $subscription->id);
                break;
            case 'invoice.payment_succeeded':
                $this->paymentSucceded($subscription);
                break;
            default:
                Log::info('*******Unknown event type: ' . $event->type . '*********');
                break;
        }
    }

    /**
     * Log subscription information for created, updated, or deleted events.
     *
     * @param string $eventType
     * @param object $subscription
     * @return void
     */
    private function logSubscriptionInfo(string $eventType, $subscription)
    {
        Log::info("*******Subscription {$eventType} successfully*********");
        Log::info('Subscription ID: ' . $subscription->id);
        Log::info('Customer ID: ' . $subscription->customer);
    }

    public function customerSubscriptionCreated($subscription)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        /* Rertreive customer id from subscription */
        $customerId = $subscription->customer;

        /* Get Customer from stripe using customer id */
        $stripeCustomer = \Stripe\Customer::retrieve($customerId);

        /* Convert meta data into array */
        $seat = json_decode($stripeCustomer->metadata->seat);
        $seat_data = $seat;

        /* Retreive team using team id from meta data */
        $team = Team::where('slug', $seat->team_slug)->first();

        $company_info = $seat->company_info;
        $seat_info = $seat->seat_info;

        /* Create company info, seat info, and seat records */
        $company_info = Company_Info::create([
            'name' => $company_info->name ?? '',
            'street_address' => $company_info->street_address ?? '',
            'city' => $company_info->city ?? '',
            'state' => $company_info->state ?? '',
            'postal_code' => $company_info->postal_code ?? '',
            'country' => $company_info->country ?? '',
            'tax_id' => $company_info->tax_id ?? null,
        ]);
        $seat_info = Seat_Info::create([
            'email' => $seat_info->email ?? '',
            'phone_number' => $seat_info->phone_number ?? '',
            'summary' => $seat_info->summary ?? null,
        ]);
        $seat = Seat::create([
            'creator_id' => $seat->creator_id ?? '',
            'team_id' => $team->id ?? '',
            'company_info_id' => $company_info->id ?? '',
            'seat_info_id' => $seat_info->id ?? '',
            'subscription_id' => $subscription->id,
            'customer_id' => $stripeCustomer->id,
            'is_active' => 0,
            'is_connected' => 0,
        ]);
        $seat_data->id = $seat->id;
        \Stripe\Customer::update(
            $customerId,
            [
                'metadata' => [
                    'seat' => json_encode($seat_data),
                ],
            ]
        );
        $team_creator = User::find($team->creator_id);
        $user = User::find($seat->creator_id);
        $emails = array($team_creator->email, $user->email, $seat_info->email);
        $unique_emails = array_unique($emails);
        foreach ($unique_emails as $email) {
            Mail::to($email)->send(new SubscriptionSuccessMail($seat, $email));
        }
    }

    public function paymentSucceded($subscription)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        /* Rertreive customer id from subscription */
        $customerId = $subscription->customer;

        /* Get Customer from stripe using customer id */
        $stripeCustomer = \Stripe\Customer::retrieve($customerId);

        /* Convert meta data into array */
        $seat = json_decode($stripeCustomer->metadata->seat);

        /* Retreive team using team id from meta data */
        $team = Team::where('slug', $seat->team_slug)->first();

        if (Seat::find($seat->id)->exists()) {
            $seat = Seat::find($seat->id);
            $seat->is_active = 1;
            $seat->updated_at = now();
            $seat->save();
        }

        $invoices = \Stripe\Invoice::all([
            'subscription' => $seat->subscription_id,
            'limit' => 1,
        ]);

        if (!empty($invoices->data)) {
            $invoice = $invoices->data[0];
            Invoice::create([
                'invoice_id' => $invoice->id,
                'invoice_url' => $invoice->invoice_pdf,
                'seat_id' => $seat->id,
                'team_id' => $team->id,
            ]);
        }
    }
}
