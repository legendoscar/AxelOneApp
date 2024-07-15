<?php

namespace Modules\UserManagement\App\Notifications;


use Illuminate\Notifications\Notification;
use App\Services\SendPulseService;

class WelcomeEmail extends Notification
{
    protected $sendPulseService;

    public function __construct(SendPulseService $sendPulseService)
    {
        $this->sendPulseService = $sendPulseService;
    }

    public function toMail($notifiable)
    {
        $htmlContent = view('emails.welcome')->render();

        $this->sendPulseService->sendEmail(
            $notifiable->email,
            'Welcome to Our Platform',
            $htmlContent
        );

        return (new MailMessage)
            ->subject('Welcome to Our Platform')
            ->line('Welcome to our platform! We are glad to have you.')
            ->line('If you have any questions, feel free to reach out to us.');
    }
}
