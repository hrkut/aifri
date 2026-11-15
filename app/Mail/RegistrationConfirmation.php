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
 * Sent to a participant right after they register (passive participation).
 */
class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration, public Conference $conference)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(EmailTheme::CONTACT_EMAIL, 'Konferencia o AI vo vzdelávaní')],
            subject: 'Potvrdenie registrácie – Konferencia o AI vo vzdelávaní ' . $this->conference->year,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-confirmation',
            text: 'emails.registration-confirmation-text',
            with: [
                'registration' => $this->registration,
                'conference' => $this->conference,
            ],
        );
    }
}
