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

class CompanyRegistrationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $subjectLine;
    protected $viewName;
    protected $data;
    protected $companyId;

    /**
     * Create a new message instance.
     *
     * @param string $subjectLine
     * @param string $viewName
     * @param array $data
     */
    public function __construct($subjectLine, $viewName, $companyId)
    {
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
        $this->companyId = $companyId;
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
        $data = Company::select('companies.name as company_name', 'companies.email as company_email', 'companies.phone as company_phone', 'companies.address as company_address', 'users.name as employer_name', 'users.email as employer_email')
            ->leftJoin('users', 'users.id', 'companies.employer_id')
            ->where('companies.id', $this->companyId)
            ->first();
        $recipient = env('MAIL_FROM_ADDRESS');
        EmailLog::create([
            'recipient_email' => $recipient,
            'subject' => $this->subjectLine,
            'relation' => $data->company_name,
            'sent_at' => now(),
        ]);
        return $this->subject($this->subjectLine)
            ->to($recipient)
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
