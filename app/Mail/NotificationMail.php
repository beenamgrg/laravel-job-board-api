<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable implements ShouldQueue
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
        $bladeData = JobApplication::select('job_applications.resume as applicantResume', 'job_applications.cover_letter as applicantCoverLetter', 'applicants.name as applicantName', 'applicants.email as applicantEmail', 'job_listings.title as jobTitle', 'employers.name as employerName', 'companies.name as companyName')
            ->leftJoin('job_listings', 'job_listings.id', 'job_applications.job_id')
            ->leftJoin('companies', 'companies.id', 'job_listings.company_id')
            ->leftJoin('users as applicants', 'applicants.id', 'job_applications.user_id')
            ->leftJoin('users as employers', 'employers.id', 'companies.employer_id')
            ->where('job_applications.user_id', $this->data['user_id'])
            ->where('job_applications.job_id', $this->data['job_id'])
            ->first();

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
