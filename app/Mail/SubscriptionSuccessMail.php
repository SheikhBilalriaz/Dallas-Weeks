<?php

namespace App\Mail;

use App\Models\Company_Info;
use App\Models\Seat_Info;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Team;

class SubscriptionSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    private $seat;
    private $email;

    /**
     * Create a new message instance.
     */
    public function __construct($seat, $email)
    {
        $this->seat = $seat;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $user = User::where('email', $this->email)->first() ?? (object) ['name' => $this->email];
        $company_info = Company_Info::find($this->seat->company_info_id);
        $creator = User::find($this->seat->creator_id);
        $seat_info = Seat_Info::find($this->seat->seat_info_id);
        $price = \Stripe\Price::retrieve(config('services.stripe.seat_price_id'));
        $customer = \Stripe\Customer::retrieve($this->seat->customer_id);
        $paymentMethod = \Stripe\PaymentMethod::retrieve($customer->default_source);

        return $this->view('email.subscribe_success')
            ->subject('Your Subscription Was Successfully Created!')
            ->with([
                'user' => $user,
                'company_info' => $company_info,
                'creator' => $creator,
                'seat_info' => $seat_info,
                'price' => $price,
                'seat' => $this->seat,
                'paymentMethod' => $paymentMethod
            ]);
    }
}
