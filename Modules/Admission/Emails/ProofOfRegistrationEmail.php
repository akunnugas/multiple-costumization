<?php

namespace Modules\Admission\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Core\Models\Biodata;
use Modules\PMB\Models\Pendaftar;

class ProofOfRegistrationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Pendaftar $registrant;

    public Biodata $person;

    public array $registrationPeriod;

    /**
     * Create a new message instance.
     */
    public function __construct(Pendaftar $registrant, Biodata $person, array $registrationPeriod)
    {
        $this->registrant = $registrant;
        $this->person = $person;
        $this->registrationPeriod = $registrationPeriod;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bukti Pendaftaran SPMB ' . config('app.name'),
            tags: ['bukti-pendaftaran', 'spmb']
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'admission::emails.proof-of-registration',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
