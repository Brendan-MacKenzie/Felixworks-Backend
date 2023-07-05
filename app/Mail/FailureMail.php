<?php

namespace App\Mail;

use App\Models\Agency;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FailureMail extends Mailable
{
    use Queueable, SerializesModels;

    public $action;
    public $agency;
    public $type;
    public $data;
    public $exception;

    /**
     * Create a new message instance.
     */
    public function __construct(string $action, Agency $agency, string $type, mixed $data, Throwable $exception)
    {
        $this->action = $action;
        $this->agency = $agency;
        $this->type = $type;
        $this->data = $data;
        $this->exception = $exception;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'FelixWorks - System Failure - ' . $this->action . '-' . Carbon::now()->toDateTimeString()
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.failure',
            with: [
                'action' => $this->action,
                'agency' => $this->agency,
                'type' => $this->type,
                'data' => $this->data,
                'exeption' => $this->exception,
            ]
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
