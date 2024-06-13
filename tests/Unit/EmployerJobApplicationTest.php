<?php

namespace Tests\Unit;

use Tests\TestCase;

class EmployerJobApplicationTest extends TestCase
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
            'email' => 'employer1@gmail.com',
            'password' => 'employer',
        ]);
        // dd($user['data']);

        // Assert: Check if the response is successful and contains a token
        $data = $user->json(['data']);
        $this->token = $user->json(['token']);
        $this->userName = $data['name'];
        $this->userId = $data['id'];
    }


    //Testing for approving the job-applicatiions posted by authorized employer

    public function test_job_application_approve()
    {
        // Act: Attempt to store jobs with valid details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-application-approve',
            [
                'jobApplicationId' => 1,
            ]
        );
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'resume', 'cover_letter', 'user_id', 'job_id', 'is_approved', 'is_rejected', 'status', 'job_title', 'company_name',  'company_email', 'applicant_name', 'applicant_email'
                ]
            ]);
    }

    //Testing for re-approving the job-applicatiions posted by authorized employer

    public function test_job_application_approve_again()
    {
        // Act: Attempt to store jobs with valid details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-application-approve',
            [
                'jobApplicationId' => 1,
            ]
        );
        // Assert: Check if the response is successful and contains a token or user data
        // Assert the JSON structure of the response
        $response->assertStatus(400)
            ->assertJson([
                'code' => 400,
                'success' => false,
                'message' => 'Job application has already been approved!!',
            ]);
    }

    //Testing for rejecting the job-applicatiions posted by authorized employer

    public function test_job_application_reject()
    {
        // Act: Attempt to store jobs with valid details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-application-reject',
            [
                'jobApplicationId' => 1,
            ]
        );
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'resume', 'cover_letter', 'user_id', 'job_id', 'is_approved', 'is_rejected', 'status', 'job_title', 'company_name',  'company_email', 'applicant_name', 'applicant_email'
                ]
            ]);
    }

    //Testing for re-rejecting the job-applicatiions posted by authorized employer

    public function test_job_application_reject_again()
    {
        // Act: Attempt to store jobs with valid details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-application-reject',
            [
                'jobApplicationId' => 1,
            ]
        );
        // Assert: Check if the response is successful and contains a token or user data
        // Assert the JSON structure of the response
        $response->assertStatus(400)
            ->assertJson([
                'code' => 400,
                'success' => false,
                'message' => 'Job application has already been rejected!!',
            ]);
    }
}
