<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $invitee,
        public string $setupUrl,
        public string $invitedByName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to LeaveFlow — EVSU Ormoc Campus',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.staff-invitation',
            text: 'emails.staff-invitation-text',
        );
    }
}
