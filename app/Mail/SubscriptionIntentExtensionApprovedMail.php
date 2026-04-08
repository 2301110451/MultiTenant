<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionIntentExtensionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $barangayName,
        public string $domain,
        public string $portalUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Extension approved — you can use your barangay portal again — '.$this->barangayName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-intent-extension-approved',
        );
    }
}
