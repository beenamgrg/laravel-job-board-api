<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Models\JobListing;



class SeekerJobApplicationTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }
    protected $token;
    protected $userName;
    protected $userId;
    protected $job;


    public function setUp(): void
    {
        parent::setUp();

        // Seeker logs in for its token to be used
        $user = $this->postJson('/api/login', [
            'email' => 'seeker@gmail.com',
            'password' => 'seeker',
        ]); 

        $data = $user->json(['data']);
        $this->token = $user->json(['token']);
        $this->userName = $data['name'];
        $this->userId = $data['id'];

        //create new job
        $this->job = JobListing::create(
            [
                'title' => 'Job Title',
                'description' => 'test',
                'application_instruction' => 'test',
                'company_id' => 1
            ]
        );
    }

    //Action : Seeker submits job application

    public function test_seeker_submit_job_application()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/submit-job-application',
            [
                'resume' => $file,
                'coverLetter' => 'This is for testing purpose',
                'jobId' => $this->job->id,
            ]
        );

        // Assert response status is 200 OK
        $response->assertStatus(200);

        // Assert the JSON structure of the response
        $response->assertJsonStructure([
            'data' => [
                'id', 'user_id', 'job_id', 'resume', 'cover_letter'
            ]
        ]);
    }
    //Action : Seeker tries to submit application without resume
    public function test_store_job_application_without_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/submit-job-application',
            [
                'coverLetter' => 'This is for testing purpose',
                'jobId' => $this->job->id,
            ]
        );
        //Assert : Check if the response returns validation error
        $response->assertStatus(422)
            ->assertJsonFragment([
                'code' => 422,
                'success' => false,
                'message' => [
                    'The resume field is required.'
                ]
            ]);
    }
}
