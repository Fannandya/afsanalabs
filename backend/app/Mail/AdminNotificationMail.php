<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $type, public string $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Notifikasi] '.$this->type,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.admin-notification-text',
            with: ['type' => $this->type, 'bodyMessage' => $this->message],
        );
    }
}
