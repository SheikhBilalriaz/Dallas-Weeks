<?php

namespace App\Http\Controllers;

use App\Models\Company_Info;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StripePaymentController extends Controller
{
    public function stripePayment($slug, Request $request)
    {
        /* Validate the form inputs */
        $validator = Validator::make($request->all(), [
            'street_address' => 'required|string',
            'city' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'company' => 'required|string|max:191',
        ]);

        /* Return validation errors if validation fails */
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->with('payment_error', true)
                ->withInput();
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));

        $customer = $stripe->customers->create([
            'address' => [
                'city' => $request->input('city'),
                'country' => $request->input('country'),
                'postal_code' => $request->input('postal_code'),
                'state' => $request->input('state'),
                'line1' => $request->input('street_address'),
            ],
            'name' => $request->input('company'),
        ]);

        $stripe->subscriptions->create([
            'customer' => $customer->id,
            'items' => [['price' => env('SEAT_SUBS_PRICE_ID')]]
        ]);

        dd($request->all());

        $company_info = Company_Info::create([
            'name' => $request->input('company'),
            'street_address' => $request->input('street_address'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'postal_code' => $request->input('postal_code'),
            'country' => $request->input('country'),
            'tax_id' => $request->input('tax_id') ?? null,
        ]);
    }
}
