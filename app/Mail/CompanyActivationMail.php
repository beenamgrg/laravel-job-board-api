<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyActivationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $subjectLine;
    protected $viewName;
    protected $data;
    protected $user;

    /**
     * Create a new message instance.
     *
     * @param string $subjectLine
     * @param string $viewName
     * @param array $data
     */
    public function __construct($subjectLine, $viewName, $user)
    {
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
        $this->user = $user;
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
        $data = Company::select('companies.name as companyName', 'users.name as employerName', 'users.email as employerEmail')
            ->leftJoin('users', 'users.id', 'companies.employer_id')
            ->where('users.email', $this->user)
            ->first();
        EmailLog::create([
            'recipient_email' => $this->user,
            'subject' => $this->subjectLine,
            'relation' => $data->companyName,
            'sent_at' => now(),
        ]);
        return $this->subject($this->subjectLine)
            ->to($this->user)
            ->markdown($this->viewName)
            ->with(['data' => $data]);
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
