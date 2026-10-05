<?php

namespace App\Mail;

use App\Models\LoaRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoaResubmittedStaffMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LoaRequest $loa,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "LOA Resubmitted — {$this->loa->control_number} | Action Required",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.loa-resubmitted-staff',
            text: 'emails.loa-resubmitted-staff-text',
        );
    }
}
