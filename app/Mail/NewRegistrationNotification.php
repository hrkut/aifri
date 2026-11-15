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
 * Sent to the organisers whenever someone registers.
 */
class NewRegistrationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // Replying goes straight to the participant
            replyTo: [new Address($this->registration->email, $this->registration->name)],
            subject: 'Nová registrácia na konferenciu – '.$this->registration->name,
        );
    }

    public function content(): Content
    {
        $conference = $this->registration->conference ?? Conference::current();

        return new Content(
            view: 'emails.new-registration-notification',
            text: 'emails.new-registration-notification-text',
            with: [
                'registration' => $this->registration,
                'conference' => $conference,
                'totalRegistrations' => $conference->registrations()->where('participation_type', '!=', 'break')->count(),
            ],
        );
    }
}
