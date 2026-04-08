<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BarangayApprovedMail extends Mailable
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
            subject: 'Your barangay portal is approved — '.$this->barangayName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.barangay-approved',
        );
    }
}
