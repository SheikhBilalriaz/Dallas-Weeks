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

class StripeController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_key')
            );
            switch ($event->type) {
                case 'customer.subscription.created':
                    $subscription = $event->data->object;
                    $this->customerSubscriptionCreated($subscription);
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

    public function customerSubscriptionCreated($subscription)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $customerId = $subscription->customer;
        $stripeCustomer = \Stripe\Customer::retrieve($customerId);
        $seat = json_decode($stripeCustomer->metadata->seat);
        $team = Team::where('slug', $seat->team_slug)->first();
        $company_info = $seat->company_info;
        $seat_info = $seat->seat_info;
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
    }
}
