<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $barangayName,
        public string $portalDomainHint,
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tenant application update — '.$this->barangayName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-application-rejected',
        );
    }
}
