<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;

    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->view('emails.send-otp')
            ->with([
                'firstName' => $this->user->first_name,
                'otp' => $this->otp,
            ])
            ->subject('Your OTP Code');
    }
}
