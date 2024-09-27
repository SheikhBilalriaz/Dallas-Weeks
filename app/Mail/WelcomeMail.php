<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $password = null;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.verify_email')
            ->subject('Please Verify Your Email Address')
            ->with([
                'user' => $this->user,
                'password' => $this->password,
            ]);
    }
}
