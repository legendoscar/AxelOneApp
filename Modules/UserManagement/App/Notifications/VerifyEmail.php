<?php

namespace Modules\UserManagement\App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\SendPulseService;

class VerifyEmail extends VerifyEmailBase
{
    protected $sendPulseService;

    public function __construct(SendPulseService $sendPulseService)
    {
        $this->sendPulseService = $sendPulseService;
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $htmlContent = view('emails.verify', ['url' => $verificationUrl])->render();

        $this->sendPulseService->sendEmail(
            $notifiable->email,
            'Verify Email Address',
            $htmlContent
        );

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }
}
