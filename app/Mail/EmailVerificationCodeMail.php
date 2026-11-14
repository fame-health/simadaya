<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode Verifikasi Email - SIMADAYA');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.verify-email-code',
            with: [
                'user' => $this->user,
                'code' => $this->code,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
