<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use App\Models\EmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\JobApplication;


class ReviewMail extends Mailable implements ShouldQueue
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
    public function __construct($subjectLine, $viewName, $data = [], $user)
    {
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
        $this->data = $data;
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
        $bladeData = JobApplication::select('job_applications.*', 'users.name as applicant_name', 'users.email as applicant_email', 'companies.name as company_name', 'companies.email as company_email', 'job_listings.title as job_title')
            ->leftjoin('job_listings', 'job_listings.id', 'job_applications.job_id')
            ->leftjoin('users', 'users.id', 'job_applications.user_id')
            ->leftjoin('companies', 'companies.employer_id', 'users.id')
            ->where('job_applications.id', $this->data['id'])
            ->first();
        EmailLog::create([
            'recipient_email' => $this->user,
            'subject' => $this->subjectLine,
            'relation' => $bladeData->company_name . " - " . $bladeData->job_title,
            'sent_at' => now(),
        ]);

        return $this->subject($this->subjectLine)
            ->to($this->user)
            ->markdown($this->viewName)
            ->with(['data' => $bladeData]);
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
