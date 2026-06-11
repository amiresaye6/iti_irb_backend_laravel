<?php

namespace App\Jobs;

use App\Mail\NotificationMail;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $toEmail;
    public string $recipientName;
    public string $subjectLine;
    public string $messageBody;
    public ?string $appSerial;
    public ?int $notificationId;
    public ?string $ctaText;
    public ?string $ctaUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $toEmail,
        string $recipientName,
        string $subjectLine,
        string $messageBody,
        ?string $appSerial = null,
        ?int $notificationId = null,
        ?string $ctaText = null,
        ?string $ctaUrl = null
    ) {
        $this->toEmail = $toEmail;
        $this->recipientName = $recipientName;
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
        $this->appSerial = $appSerial;
        $this->notificationId = $notificationId;
        $this->ctaText = $ctaText;
        $this->ctaUrl = $ctaUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->toEmail)->send(new NotificationMail(
                $this->recipientName,
                $this->messageBody,
                $this->appSerial,
                $this->subjectLine,
                $this->ctaText,
                $this->ctaUrl
            ));

            if ($this->notificationId) {
                $notification = Notification::find($this->notificationId);
                if ($notification) {
                    $notification->update(['email_sent' => true]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email job.', [
                'toEmail' => $this->toEmail,
                'notificationId' => $this->notificationId,
                'error' => $e->getMessage()
            ]);
            
            // Re-throw the exception so the job can be retried by the queue worker
            throw $e;
        }
    }
}
