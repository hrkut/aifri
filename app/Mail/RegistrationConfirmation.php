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
 * Sent to a participant right after they register (passive or active participation).
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
            subject: __('Potvrdenie registrácie – Konferencia o AI vo vzdelávaní :year', ['year' => $this->conference->year]),
        );
    }

    public function content(): Content
    {
        return new Content(
            // English template for participants who registered in English
            view: $this->template('emails.registration-confirmation'),
            text: $this->template('emails.registration-confirmation-text'),
            with: [
                'registration' => $this->registration,
                'conference' => $this->conference,
            ],
        );
    }

    /** emails.en.* when the mail is rendered in English, otherwise the Slovak template. */
    private function template(string $view): string
    {
        return app()->getLocale() === 'en' ? str_replace('emails.', 'emails.en.', $view) : $view;
    }
}
