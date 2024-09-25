<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StripePaymentController extends Controller
{
    public function stripePayment($slug, Request $request)
    {
        try {
            $user = Auth::user();

            /* Validate the form inputs */
            $validator = Validator::make($request->all(), [
                'street_address' => 'required|string',
                'city' => 'required|string|max:191',
                'state' => 'required|string|max:191',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:100',
                'company' => 'required|string|max:191',
                'email' => 'required|email',
                'phone_number' => 'required',
                'stripe_token' => 'required',
                'card_name' => 'required',
                'card_number' => 'required',
                'card_cvc' => 'required',
                'card_expiry_month' => 'required',
                'card_expiry_year' => 'required',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->with('payment_error', true)
                    ->withInput();
            }

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret_key'));

            $customer = $stripe->customers->create([
                'address' => [
                    'city' => $request->input('city'),
                    'country' => $request->input('country'),
                    'postal_code' => $request->input('postal_code'),
                    'state' => $request->input('state'),
                    'line1' => $request->input('street_address'),
                ],
                'name' => $request->input('company'),
                'source' => $request->input('stripe_token'),
                'email' => $request->input('email'),
                'metadata' => [
                    'creator_id' => $user->id,
                    'team_slug' => $slug,
                    'name' => $request->input('company'),
                    'street_address' => $request->input('street_address'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'postal_code' => $request->input('postal_code'),
                    'country' => $request->input('country'),
                    'tax_id' => $request->input('tax_id') ?? null,
                ],
            ]);

            $subscription = $stripe->subscriptions->create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => config('services.stripe.seat_price_id')],
                ],
            ]);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
