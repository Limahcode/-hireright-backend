<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SendGrid\Mail\Mail as SendGridMail;

class SendOtpEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $otp;
    
    public $tries = 3;
    public $timeout = 60;

    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    public function handle()
    {
        $email = new SendGridMail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));
        $email->setSubject('Your HireRight OTP Code');
        $email->addTo($this->user->email, $this->user->first_name . ' ' . $this->user->last_name);
        $email->addContent(
            "text/html",
view('emails.send-otp', ['otp' => $this->otp, 'user' => $this->user])->render()        );

        $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));
        
        try {
            $response = $sendgrid->send($email);
            \Log::info('OTP Email sent successfully', [
                'status' => $response->statusCode(),
                'email' => $this->user->email,
                'user_id' => $this->user->id
            ]);
        } catch (\Exception $e) {
            \Log::error('SendGrid API error', [
                'error' => $e->getMessage(),
                'email' => $this->user->email
            ]);
            throw $e;
        }
    }
}