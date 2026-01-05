<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralMail extends Mailable
{
    use Queueable, SerializesModels;
    private $data;
    private $page;
    public $subject;
    /**
     * Create a new message instance.
     */
    public function __construct($data, $page, $subject = null)
    {
        $this->data = $data;
        $this->page = $page;
        $this->subject = $subject;
    }

    /**
     * Get the message envelope.
     */
   public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }
    /**
     * Get the message content definition.
     */
  public function content(): Content
    {
        return new Content(
            view: "mail.$this->page",
            with: [
                'data' => $this->data,
            ],
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
