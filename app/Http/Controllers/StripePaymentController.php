<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\Coupon;

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
                'card_expiry_year' => 'required|max:4',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->with('payment_error', true)
                    ->withInput();
            }

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            /* Get the current timezone from the configuration */
            $currentTimezone = config('app.timezone');

            /* Initialize the promo code ID (if provided) */
            $couponId = null;
            if ($request->has('promo_code') && $request->input('promo_code') !== '') {
                try {
                    /* Retrieve promo code */
                    $promoCodes = $stripe->promotionCodes->all([
                        'code' => $request->input('promo_code'),
                        'active' => true,
                        'limit' => 1,
                    ]);
                    if (!empty($promoCodes->data) && $promoCode = $promoCodes->data[0]) {
                        /* Valid promo code */
                        $couponId = $promoCode->coupon->id;
                    } else {
                        return back()->withErrors(['promo_code' => 'Invalid or expired promo code.'])
                            ->with('payment_error', true)
                            ->withInput();
                    }
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    return back()->withErrors(['promo_code' => 'Error validating promo code. Please try again.'])
                        ->with('payment_error', true)
                        ->withInput();
                }
            }

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
                        'global_limit' => [
                            'timezone' => $currentTimezone,
                        ],
                    ]),
                ],
            ]);

            /* Create a subscription for the customer */
            $subscriptionParams = [
                'customer' => $customer->id,
                'items' => [
                    ['price' => config('services.stripe.seat_price_id')],
                ],
            ];

            if ($couponId) {
                $subscriptionParams['coupon'] = $couponId;
            }

            /* Create subscription */
            $subscription = $stripe->subscriptions->create($subscriptionParams);

            return redirect()->route('dashboardPage', ['slug' => $slug])->with('success', 'You will be notified on email when subscription created');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e);
            return back()->withErrors(['payment_error' => 'Payment processing error. Please try again.'])
                ->withInput();
        } catch (Exception $e) {
            Log::error('General Error: ' . $e);
            return back()->withErrors(['payment_error' => 'Something went wrong'])
                ->withInput();
        }
    }

    public function checkPromoCode($slug, Request $request)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $promo_code = $request->input('promo_code');
            $promoCodes = $stripe->promotionCodes->all([
                'code' => $promo_code,
                'active' => true,
                'limit' => 1,
            ]);
            if (!empty($promoCodes->data) && $promoCode = $promoCodes->data[0]) {
                return response()->json([
                    'valid' => true,
                    'promo_code' => [
                        'id' => $promoCode->id,
                        'coupon_id' => $promoCode->coupon->id,
                    ],
                ]);
            } else {
                return response()->json(['valid' => false, 'coupon_error' => 'Invalid Promotion Code']);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e);
            return response()->json(['valid' => false, 'coupon_error' => $e->getMessage()]);
        } catch (Exception $e) {
            Log::error('General Error: ' . $e);
            return response()->json(['valid' => false, 'coupon_error' => 'Something went wrong'], 500);
        }
    }
}
