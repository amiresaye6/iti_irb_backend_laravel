<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $messageBody;
    public ?string $appSerial;
    public string $subjectLine;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $recipientName,
        string $messageBody,
        ?string $appSerial,
        string $subjectLine
    ) {
        $this->recipientName = $recipientName;
        $this->messageBody = $messageBody;
        $this->appSerial = $appSerial;
        $this->subjectLine = $subjectLine;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'recipientName' => $this->recipientName,
                'messageBody' => $this->messageBody,
                'appSerial' => $this->appSerial,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
