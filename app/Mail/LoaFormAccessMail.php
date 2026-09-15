<?php

namespace App\Mail;

use App\Models\LoaAccessToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoaFormAccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LoaAccessToken $accessToken,
        public string $formUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your LeaveFlow LOA form link — EVSU Ormoc Campus',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.loa-form-access',
            text: 'emails.loa-form-access-text',
        );
    }
}
