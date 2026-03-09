<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RedFlagWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public int $redFlagCount;

    public function __construct(User $user, int $redFlagCount)
    {
        $this->user = $user;
        $this->redFlagCount = $redFlagCount;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Multiple High-Risk Activities Detected',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.red-flag-warning',
            with: [
                'user' => $this->user,
                'redFlagCount' => $this->redFlagCount,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
