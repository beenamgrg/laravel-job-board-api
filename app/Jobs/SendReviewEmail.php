<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReviewMail;
use App\Mail\NotificationMail;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;


class SendReviewEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;


    protected $data;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to('beenamgrg089@gmail.com')->send(new NotificationMail($this->data, $this->user));

        // Log that the job has started
        Log::info('SendWelcomeEmail job started');

        try
        {
            // Log before sending the email
            Log::info('Sending email to data');

            Mail::to($this->user)->send(new NotificationMail());

            // Log after the email is sent
            Log::info('Email sent successfully to');
        }
        catch (\Exception $e)
        {
            // Log any exceptions
            dd($e->getMessage());
            Log::error('Failed to send email', ['error' => $e->getMessage()]);
        }
    }
}
