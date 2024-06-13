<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;


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

    public function setUp(): void
    {
        parent::setUp();

        // Act: Attempt to log in with the correct credentials
        $user = $this->postJson('/api/login', [
            'email' => 'seeker@gmail.com',
            'password' => 'seeker',
        ]);
        // dd($user['data']);

        // Assert: Check if the response is successful and contains a token
        $data = $user->json(['data']);
        $this->token = $user->json(['token']);
        $this->userName = $data['name'];
        $this->userId = $data['id'];
    }

    //Testing for submitting application

    public function test_store_job_application()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        // Act: Attempt to store jobs with valid details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/submit-job-application',
            [
                'resume' => $file,
                'coverLetter' => 'This is for testing purpose',
                'jobId' => 2,
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

    //Testing for submitting application without data

    public function test_store_job_application_without_data()
    {


        // Act: Attempt to store jobs with valid details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/submit-job-application',
            [
                'coverLetter' => 'This is for testing purpose',
                'jobId' => 2,
            ]
        );

        // Assert response status is 200 OK
        $response->assertStatus(422);

        // Assert the JSON structure of the response
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
