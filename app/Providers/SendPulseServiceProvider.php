<?php

namespace App\Providers;

use Sendpulse\RestApi\ApiClient;
use Sendpulse\RestApi\Storage\FileStorage;

class SendPulseServiceProvider
{
    protected $apiClient;

    public function __construct()
    {
        $this->apiClient = new ApiClient(
            env('SENDPULSE_USER_ID'),
            env('SENDPULSE_SECRET'),
            new FileStorage()
        );
    }

    public function sendEmail($to, $subject, $htmlContent)
    {
        $email = [
            'html' => $htmlContent,
            'text' => '',
            'subject' => $subject,
            'from' => [
                'name' => env('SENDPULSE_FROM_NAME'),
                'email' => env('SENDPULSE_FROM_EMAIL'),
            ],
            'to' => [
                [
                    'name' => '',
                    'email' => $to,
                ],
            ],
        ];

        $this->apiClient->smtpSendMail($email);
    }
}
