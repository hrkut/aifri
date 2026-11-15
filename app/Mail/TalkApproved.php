<?php

namespace App\Mail;

use App\Models\Conference;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the author when the programme committee approves their talk.
 */
class TalkApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration, public Conference $conference)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(EmailTheme::CONTACT_EMAIL, 'Konferencia o AI vo vzdelávaní')],
            subject: 'Výsledok posúdenia príspevku – Konferencia o AI vo vzdelávaní ' . $this->conference->year,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.talk-approved',
            text: 'emails.talk-approved-text',
            with: [
                'registration' => $this->registration,
                'conference' => $this->conference,
            ],
        );
    }
}
