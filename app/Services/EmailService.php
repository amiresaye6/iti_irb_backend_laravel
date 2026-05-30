<?php

namespace App\Services;

use App\Jobs\SendEmailJob;

class EmailService
{
    /**
     * Send an email by queuing a job.
     *
     * @param string $toEmail
     * @param string $recipientName
     * @param string $subjectLine
     * @param string $messageBody
     * @param string|null $appSerial
     * @param int|null $notificationId Optional ID of the notification model to update on success
     * @return void
     */
    public static function send(
        string $toEmail,
        string $recipientName,
        string $subjectLine,
        string $messageBody,
        ?string $appSerial = null,
        ?int $notificationId = null
    ): void {
        SendEmailJob::dispatch(
            $toEmail,
            $recipientName,
            $subjectLine,
            $messageBody,
            $appSerial,
            $notificationId
        );
    }
}
