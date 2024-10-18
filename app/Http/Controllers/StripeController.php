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
use App\Models\Account_Health;
use App\Models\Account_Health_Limit;
use App\Models\Global_Limit;
use App\Models\Invoice;
use App\Models\Seat_Time;
use App\Models\Seat_Timezone;
use App\Models\User;
use PDF;
use Storage;
use Illuminate\Support\Str;

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
        /* Retrieve the raw payload from the incoming request */
        $payload = $request->getContent();

        /* Get the 'Stripe-Signature' header to validate the webhook's authenticity */
        $signature = $request->header('Stripe-Signature');

        try {
            /* Verify the Stripe webhook signature and construct the event */
            $event = Webhook::constructEvent($payload, $signature, config('services.stripe.webhook_key'));

            /* Handle the event based on its type */
            $this->handleEvent($event);

            /* Return a 200 OK response to Stripe, indicating the webhook was handled successfully */
            return response('Webhook handled', 200);
        } catch (\UnexpectedValueException $e) {
            /* Catch and log errors related to invalid payloads (e.g., malformed JSON) */
            Log::error($e);

            /* Return a 400 Bad Request response if the payload is invalid */
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            /* Catch and log errors related to invalid signatures */
            Log::error($e);

            /* Return a 400 Bad Request response if the signature verification fails */
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            /* Catch any other exceptions, log the error for debugging */
            Log::error($e);

            /* Return a 500 Internal Server Error response if any other server error occurs */
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
        /* Retrieve the subscription object from the event data */
        $subscription = $event->data->object;

        /* Use a switch statement to handle different event types */
        switch ($event->type) {
            case 'customer.subscription.created':
                /* Call method to handle actions when a subscription is created */
                $this->customerSubscriptionCreated($subscription);
                break;
            case 'customer.subscription.deleted':
                /* Log the deletion of the subscription for record-keeping */
                Log::info("*******Subscription {$event->type} successfully*********");
                Log::info('Subscription ID: ' . $subscription->id);
                Log::info('Customer ID: ' . $subscription->customer);
                break;
            case 'customer.subscription.updated':
                /* Call method to handle actions when a subscription is updated */
                $this->customerSubscriptionUpdated($subscription);
                break;
            case 'invoice.payment_failed':
                /* Call method to handle actions when a payment fails */
                $this->paymentFailed($subscription);
                break;
            case 'invoice.payment_succeeded':
                /* Call method to handle actions when a payment is successful */
                $this->paymentSucceded($subscription);
                break;
            default:
                /* Log information about unknown event types for debugging */
                Log::info('*******Unknown event type: ' . $event->type . '*********');
                break;
        }
    }

    /**
     * Handle the Stripe webhook event based on its type.
     *
     * @param \Stripe\Event $event The Stripe event object containing the event data.
     * @return void
     */
    public function customerSubscriptionCreated($subscription)
    {
        /* Set the Stripe API key for authentication */
        Stripe::setApiKey(config('services.stripe.secret'));

        /* Retrieve the customer ID from the subscription object */
        $customerId = $subscription->customer;

        /* Get the Customer object from Stripe using the customer ID */
        $stripeCustomer = \Stripe\Customer::retrieve($customerId);

        /* Decode the seat metadata into an array */
        $seat = json_decode($stripeCustomer->metadata->seat);
        $seat_data = $seat;

        /* Retrieve the associated team using the team slug from the seat metadata */
        $team = Team::where('slug', $seat->team_slug)->first();

        /* Extract company info and seat info from the seat metadata */
        $company_info = $seat->company_info;
        $seat_info = $seat->seat_info;
        $global_limit = $seat->global_limit;

        /* Create a new company_info record in the database */
        $company_info = Company_Info::create([
            'name' => $company_info->name ?? '',
            'street_address' => $company_info->street_address ?? '',
            'city' => $company_info->city ?? '',
            'state' => $company_info->state ?? '',
            'postal_code' => $company_info->postal_code ?? '',
            'country' => $company_info->country ?? '',
            'tax_id' => $company_info->tax_id ?? null,
        ]);

        /* Create a new seat_info record in the database */
        $seat_info = Seat_Info::create([
            'email' => $seat_info->email ?? '',
            'phone_number' => $seat_info->phone_number ?? '',
            'summary' => $seat_info->summary ?? null,
        ]);

        /* Create a new seat record in the database, linking all previously created records */
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

        /* Create a new seat time of start and end with default record in the database, linking all previously created records */
        $seat_time['start'] = Seat_Time::create([
            'seat_id' => $seat->id,
            'time_status' => 'start',
            'time' => '09:00:00',
        ]);
        $seat_time['end'] = Seat_Time::create([
            'seat_id' => $seat->id,
            'time_status' => 'end',
            'time' => '17:00:00',
        ]);

        /* Create a new seat timezone with default record in the database, linking all previously created records */
        $seat_timezone = Seat_Timezone::create([
            'seat_id' => $seat->id,
            'timezone' => $global_limit->timezone
        ]);

        /* Create a new 'run_on_weekends' health check record for the seat */
        $run_on_weekends = Account_Health::create([
            'seat_id' => $seat->id,
            'health_slug' => 'run_on_weekends',
            'value' => 0,
        ]);

        /* Create a new 'oldest_pending_invitations' health check record for the seat */
        $oldest_pending_invitations = Account_Health::create([
            'seat_id' => $seat->id,
            'health_slug' => 'oldest_pending_invitations',
            'value' => 0,
        ]);

        $pending_connections = Account_Health_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'pending_connections',
            'value' => 0,
        ]);

        $profile_views = Global_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'profile_views',
            'value' => 0,
        ]);

        $follows = Global_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'follows',
            'value' => 0,
        ]);

        $invite = Global_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'invite',
            'value' => 0,
        ]);

        $message = Global_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'message',
            'value' => 0,
        ]);

        $inmail = Global_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'inmail',
            'value' => 0,
        ]);

        $discover = Global_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'discover',
            'value' => 0,
        ]);

        $email_message = Global_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'email_message',
            'value' => 0,
        ]);

        $email_delay = Global_Limit::create([
            'seat_id' => $seat->id,
            'health_slug' => 'email_delay',
            'value' => 0,
        ]);

        /* Update the seat metadata in Stripe with the newly created seat ID */
        $seat_data->id = $seat->id;
        \Stripe\Customer::update(
            $customerId,
            [
                'metadata' => [
                    'seat' => json_encode($seat_data),
                ],
            ]
        );

        /* Retrieve the team creator and the user associated with the seat */
        $team_creator = User::find($team->creator_id);
        $user = User::find($seat->creator_id);

        /* Prepare a list of unique email addresses to notify */
        $emails = array($team_creator->email, $user->email, $seat_info->email);
        $unique_emails = array_unique($emails);

        /* Send notification emails to all unique email addresses */
        foreach ($unique_emails as $email) {
            Mail::to($email)->send(new SubscriptionSuccessMail($seat, $email));
        }
    }

    /**
     * Handle the Stripe event for an updated customer subscription.
     *
     * @param \Stripe\Subscription $subscription The updated subscription object from Stripe.
     * @return void
     */
    public function customerSubscriptionUpdated($subscription)
    {
        /* Set the Stripe API key for authentication */
        Stripe::setApiKey(config('services.stripe.secret'));

        /* Retrieve the customer ID from the updated subscription */
        $customerId = $subscription->customer;

        /* Retrieve the customer details from Stripe using the customer ID */
        $stripeCustomer = \Stripe\Customer::retrieve($customerId);

        /* Decode the seat metadata from the Stripe customer object into an array */
        $seat = json_decode($stripeCustomer->metadata->seat);

        /* Retrieve the team associated with the seat using the team slug from the metadata */
        $team = Team::where('slug', $seat->team_slug)->first();

        /* Check if the seat exists in the database */
        if (Seat::find($seat->id)->exists()) {
            /* Retrieve the existing seat record */
            $seat = Seat::find($seat->id);

            /* Mark the seat as inactive */
            $seat->is_active = 0;

            /* Update the timestamp for when the seat was modified */
            $seat->updated_at = now();

            /* Save the changes to the seat record in the database */
            $seat->save();
        }
    }

    /**
     * Handle the event when a payment succeeds for a subscription.
     *
     * @param \Stripe\Subscription $subscription The subscription object associated with the payment.
     * @return void
     */
    public function paymentSucceded($subscription)
    {
        /* Set the Stripe API key for authentication */
        Stripe::setApiKey(config('services.stripe.secret'));

        /* Retrieve the customer ID from the successful subscription */
        $customerId = $subscription->customer;

        /* Get the customer details from Stripe using the customer ID */
        $stripeCustomer = \Stripe\Customer::retrieve($customerId);

        /* Convert the seat metadata from the customer into an array */
        $seat = json_decode($stripeCustomer->metadata->seat);

        /* Retrieve the team associated with the seat using the team slug from metadata */
        $team = Team::where('slug', $seat->team_slug)->first();

        /* Check if the seat exists in the database */
        if (Seat::find($seat->id)->exists()) {
            /* Retrieve the existing seat record */
            $seat = Seat::find($seat->id);

            /* Mark the seat as active */
            $seat->is_active = 1;

            /* Update the timestamp for when the seat was modified */
            $seat->updated_at = now();

            /* Save the changes to the seat record in the database */
            $seat->save();
        }

        /* Retrieve the latest invoice for the subscription */
        $invoices = \Stripe\Invoice::all([
            'subscription' => $seat->subscription_id,
            'limit' => 1,
        ]);

        /* Check if any invoices were returned */
        if (!empty($invoices->data)) {
            /* Get the first invoice from the response */
            $invoice = $invoices->data[0];

            /* Generate a unique filename for the invoice PDF */
            $fileName = $this->createUniqueFileName();

            /* Extract the invoice ID from the filename */
            $invoiceId = str_replace('public/invoices/', '', $fileName);
            $invoiceId = str_replace('.pdf', '', $invoiceId);

            /* Calculate the new billing end date based on the invoice period */
            $startDateTime = new \DateTime('@' . $invoice->period_start);
            $startDateTime->modify('+1 month');
            $newBillingEndDate = $startDateTime->format('M - y');

            /* Prepare the invoice data for PDF generation */
            $invoiceData = [
                'user' => User::find($seat->creator_id),
                'invoiceId' => $invoiceId,
                'invoice' => $invoice,
                'subscription' => $subscription,
                'seat' => $seat,
                'company_info' => Company_Info::find($seat->company_info_id),
                'invoiceDate' => date('Y-m-d', $invoice->created),
                'dueDate' => date('Y-m-d', $invoice->due_date),
                'items' => $invoice->lines->data,
                'billingStart' => date('M - y', $invoice->period_start),
                'billingEnd' => $newBillingEndDate,
            ];

            /* Generate the PDF invoice using the prepared data */
            $pdf = PDF::loadView('pdf.subscription_invoice', $invoiceData);

            /* Store the generated PDF in the public storage */
            Storage::disk('public')->put('invoices/' . $fileName, $pdf->output());

            /* Create a new invoice record in the database */
            Invoice::create([
                'invoice_id' => $invoice->id,
                'invoice_url' => $fileName,
                'seat_id' => $seat->id,
                'team_id' => $team->id,
            ]);
        }
    }

    /**
     * Handle the event when a payment fails for a subscription.
     *
     * @param \Stripe\Subscription $subscription The subscription object associated with the failed payment.
     * @return void
     */
    public function paymentFailed($subscription)
    {
        /* Set the Stripe API key for authentication */
        Stripe::setApiKey(config('services.stripe.secret'));

        /* Retrieve the customer ID from the failed subscription */
        $customerId = $subscription->customer;

        /* Get the customer details from Stripe using the customer ID */
        $stripeCustomer = \Stripe\Customer::retrieve($customerId);

        /* Convert the seat metadata from the customer into an array */
        $seat = json_decode($stripeCustomer->metadata->seat);

        /* Retrieve the team associated with the seat using the team slug from metadata */
        $team = Team::where('slug', $seat->team_slug)->first();

        /* Check if the seat exists in the database */
        if (Seat::find($seat->id)->exists()) {
            /* Retrieve the existing seat record */
            $seat = Seat::find($seat->id);

            /* Mark the seat as inactive due to payment failure */
            $seat->is_active = 0;

            /* Update the timestamp for when the seat was modified */
            $seat->updated_at = now();

            /* Save the changes to the seat record in the database */
            $seat->save();
        }
    }

    /**
     * Generate a unique filename for an invoice PDF.
     *
     * @return string The unique filename for the invoice.
     */
    private function createUniqueFileName()
    {
        do {
            /* Generate a unique filename using UUID */
            $uniqueFileName = 'invoice_' . Str::uuid() . '.pdf';
        } while (Storage::exists($uniqueFileName));

        /* Return the unique filename */
        return $uniqueFileName;
    }
}
