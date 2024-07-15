<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class SendPulseService
{
    protected $mail;
    protected $maxRetries;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->maxRetries = 3; // Set the maximum number of retries

        $this->setupMailer();
    }

    protected function setupMailer()
    {
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = env('MAIL_HOST');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = env('MAIL_USERNAME');
        $this->mail->Password = env('MAIL_PASSWORD');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = env('MAIL_PORT');
        $this->mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    }

    public function sendEmail($to, $subject, $htmlContent)
    {
        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $htmlContent;

        $attempts = 0;

        while ($attempts < $this->maxRetries) {
            try {
                $this->mail->send();
                return true;
            } catch (Exception $e) {
                $attempts++;
                Log::error('Error sending email via PHPMailer', [
                    'attempt' => $attempts,
                    'error' => $e->getMessage(),
                    'email' => $to,
                    'subject' => $subject
                ]);

                if ($attempts >= $this->maxRetries) {
                    throw new \Exception('Failed to send email after multiple attempts: ' . $e->getMessage());
                }

                // Wait for a short time before retrying (e.g., 2 seconds)
                sleep(2);
            }
        }

        return false;
    }
}
