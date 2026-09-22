<?php

namespace App\Mail;

use App\Models\LoaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoaRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LoaRequest $loa,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "LOA Not Approved — {$this->loa->control_number} | EVSU Ormoc Campus",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.loa-rejected',
            text: 'emails.loa-rejected-text',
        );
    }
}
