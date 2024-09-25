<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Company_Info;

class StripeController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        Log::info('Received webhook payload: ' . $payload);
        Log::info('Received Stripe signature: ' . $signature);
        Log::info('Stripe webhook secret: ' . config('services.stripe.webhook_key'));
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_key')
            );
            switch ($event->type) {
                case 'customer.subscription.created':
                    $subscription = $event->data->object;
                    $customerId = $subscription->customer;
                    $stripeCustomer = \Stripe\Customer::retrieve($customerId);
                    $this->customerSubscriptionCreated($stripeCustomer);
                    break;
                case 'customer.subscription.deleted':
                    Log::info('*******Seat deleted successfully*********');
                    $subscription = $event->data->object;
                    Log::info('Subscription ID: ' . $subscription->id);
                    Log::info('Customer ID: ' . $subscription->customer);
                    break;
                case 'customer.subscription.updated':
                    Log::info('*******Seat updated successfully*********');
                    $subscription = $event->data->object;
                    Log::info('Subscription ID: ' . $subscription->id);
                    Log::info('Customer ID: ' . $subscription->customer);
                    break;
                case 'invoice.payment_failed':
                    Log::info('*******Invoice payment failed*********');
                    $invoice = $event->data->object;
                    Log::info('Payment failed for subscription: ' . $invoice->subscription);
                    break;
                case 'invoice.payment_succeeded':
                    Log::info('*******Invoice payment succeeded*********');
                    $invoice = $event->data->object;
                    Log::info('Payment succeeded for subscription: ' . $invoice->subscription);
                    break;
                default:
                    Log::info('*******Default*********');
                    Log::info($event->type);
                    break;
            }
            return response('Webhook handled', 200);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload: ' . $e->getMessage());
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }
    }

    public function customerSubscriptionCreated($stripeCustomer)
    {
        Log::info($stripeCustomer);
        // $company_info = Company_Info::create([
        //     'name' => $stripeCustomer->metadata->company ?? '',
        //     'street_address' => $stripeCustomer->metadata->street_address ?? '',
        //     'city' => $stripeCustomer->metadata->city ?? '',
        //     'state' => $stripeCustomer->metadata->state ?? '',
        //     'postal_code' => $stripeCustomer->metadata->postal_code ?? '',
        //     'country' => $stripeCustomer->metadata->country ?? '',
        //     'tax_id' => $stripeCustomer->metadata->tax_id ?? null,
        // ]);
    }
}
