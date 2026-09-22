<?php

namespace App\Mail;

use App\Models\LoaRequest;
use App\Services\LoaPdfGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoaApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LoaRequest $loa,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "LOA Approved — {$this->loa->control_number} | EVSU Ormoc Campus",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.loa-approved',
            text: 'emails.loa-approved-text',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $generator = new LoaPdfGenerator();
        $pdfBinary = $generator->generate($this->loa);

        $filename = 'LOA-' . ($this->loa->control_number ?: $this->loa->id) . '.pdf';

        return [
            Attachment::fromData(fn () => $pdfBinary, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
