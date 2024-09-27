<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StripePaymentController extends Controller
{
    /**
     * Create new customer and add subscription using Stripe
     *
     * @param  String  $slug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                'phone_number' => 'required|string',
                'stripe_token' => 'required',
                'card_name' => 'required|string',
                'card_number' => 'required|string',
                'card_cvc' => 'required|string',
                'card_expiry_month' => 'required|max:2',
                'card_expiry_year' => 'required|max:2',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->with('payment_error', true)
                    ->withInput();
            }

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            /* Create a Stripe customer */
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
                    'seat' => json_encode([
                        'creator_id' => $user->id,
                        'team_slug' => $slug,
                        'company_info' => [
                            'name' => $request->input('company'),
                            'street_address' => $request->input('street_address'),
                            'city' => $request->input('city'),
                            'state' => $request->input('state'),
                            'postal_code' => $request->input('postal_code'),
                            'country' => $request->input('country'),
                            'tax_id' => $request->input('tax_id') ?? null,
                        ],
                        'seat_info' => [
                            'email' => $request->input('email'),
                            'phone_number' => $request->input('phone_number'),
                            'summary' => $request->input('summary') ?? null,
                        ],
                    ]),
                ],
            ]);

            /* Create a subscription for the customer */
            $stripe->subscriptions->create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => config('services.stripe.seat_price_id')],
                ],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e);
            return back()->withErrors(['payment_error' => 'Payment processing error. Please try again.'])
                ->withInput();
        } catch (Exception $e) {
            Log::error('General Error: ' . $e);
            return back()->withErrors(['payment_error' => 'An unexpected error occurred. Please try again.'])
                ->withInput();
        }
    }
}
