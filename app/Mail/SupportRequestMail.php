<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $subjectLine;
    public string $messageBody;
    public ?Order $order;

    public function __construct(User $user, string $subjectLine, string $messageBody, ?Order $order = null)
    {
        $this->user = $user;
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Support Request] {$this->subjectLine}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support-request',
            with: [
                'user' => $this->user,
                'subjectLine' => $this->subjectLine,
                'messageBody' => $this->messageBody,
                'order' => $this->order,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
