<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $subjectLine;
    protected $viewName;
    protected $data;

    /**
     * Create a new message instance.
     *
     * @param string $subjectLine
     * @param string $viewName
     * @param array $data
     */
    public function __construct($subjectLine, $viewName, $data = [])
    {
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subjectLine)
            ->to($this->data['applicant_email'])
            ->markdown($this->viewName)
            ->with(['data' => $this->data]);
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
