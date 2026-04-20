<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantAnnouncementPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $title,
        public string $messageBody,
        public ?string $publishedAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.$this->tenantName.'] New announcement: '.$this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant-announcement-published',
        );
    }
}
