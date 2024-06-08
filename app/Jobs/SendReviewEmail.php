<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReviewMail;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;


class SendReviewEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;


    protected $subjectLine;
    protected $viewName;
    protected $data;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($subjectLine, $viewName, $data, $user)
    {
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
        $this->data = $data;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user)->send(new ReviewMail($this->subjectLine, $this->viewName, $this->data, $this->user));
    }
}
