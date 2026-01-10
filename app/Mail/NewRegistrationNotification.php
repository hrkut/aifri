<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewRegistrationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nová registrácia na konferenciu – '.$this->registration->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-registration-notification',
            with: [
                'registration' => $this->registration,
            ],
        );
    }
}

