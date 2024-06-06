<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $data;
    protected $user;



    /**
     * Create a new message instance.
     */
    public function __construct()
    {
    }


    /**
     * Get the message content definition.
     */
    public function build()
    {
        // dd($this->user);
        return $this->to($this->user)
            ->subject('test')
            ->markdown('emails.notification')
            ->with(['data' => 'test']);
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
