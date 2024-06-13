<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\JobListing;



class JobTest extends TestCase
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

        //User logs in as an employer for the data to be used throughout the file
        $user = $this->postJson('/api/login', [
            'email' => 'employer1@gmail.com',
            'password' => 'employer',
        ]);
        $data = $user->json(['data']);
        $this->token = $user->json(['token']);
        $this->userName = $data['name'];
        $this->userId = $data['id'];
    }

    //Action : Employer tries to get the list of all jobs posted by himself
    public function test_employer_get_job_list()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])
            ->getJson('/api/employer/job-listings');

        // Assert: JSON structure and check for specific fields
        $response->assertJsonStructure([
            'code',
            'success',
            'message',
            'data' => [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]
        ]);
        // Assert : specific values in the response JSON
        $response->assertJson([
            'code' => 200,
            'success' => true,
            'message' => 'List of the jobs created by ' . $this->userName,
        ]);

        // Assert that the 'data' field is an array and contains at least one item
        $responseData = $response->json();
        $this->assertTrue(is_array($responseData['data']['data']));
        $this->assertNotEmpty($responseData['data']['data']);
    }


    //Action : Employer tries to store a new job
    public function test_employer_store_job()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-store',
            [
                'title' => 'Test Job ',
                'description' => 'This is for testing purpose',
                'applicationInstruction' => 'This is for testing purpose',
            ]
        );
        // Assert: Check if the response is successful and contains data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'company_id', 'description', 'application_instruction'
                ]
            ]);
    }

    //Action : Employer tries to store a new job without title
    public function test_employer_store_jobs_without_title()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-store',
            [
                'description' => 'This is for testing purpose',
                'applicationInstruction' => 'This is for testing purpose',
            ]
        );
        // Assert: Check if the response has validation error with status code 422
        $response->assertStatus(422)
            ->assertJsonFragment([
                'code' => 422,
                'success' => false,
                'message' => [
                    'The title field is required.'
                ]
            ]);
    }
    //Action : Employer update authorized job
    public function test_employer_update_job()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->put(
            '/api/employer/job-update',
            [
                'jobId' => 1,
                'title' => 'Update Job ',
                'description' => 'This is for testing purpose',
                'applicationInstruction' => 'This is for testing purpose',
            ]
        );
        // Assert: Check if the response is successful and contains data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'description', 'application_instruction', 'status'
                ]
            ]);
    }
    //Action : Employer update authorized job without id
    public function test_employer_update_job_without_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->put(
            '/api/employer/job-update',
            [
                'title' => 'Update Job ',
                'description' => 'This is for testing purpose',
                'applicationInstruction' => 'This is for testing purpose',
            ]
        );
        // Assert: Check if the response returns validation error with 422
        $response->assertStatus(422)
            ->assertJsonFragment([
                'code' => 422,
                'success' => false,
                'message' => [
                    'The job id field is required.'
                ]
            ]);
    }
    //Action : Employer update unauthorized
    public function test_employer_update_unauthorized_job()
    {
        // Act: Attempt to update unauthorized jobs
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->put(
            '/api/employer/job-update',
            [
                'jobId' => 2,
                'title' => 'Update Job ',
                'description' => 'This is for testing purpose',
                'applicationInstruction' => 'This is for testing purpose',
            ]
        );
        // Assert: Check if the response is forbidden accen with 403
        $response->assertStatus(403)
            ->assertJson([
                'code' => 403,
                'success' => false,
                'message' => 'Forbidden Access',
            ]);
    }
    //Action : Employer delets job posted by himself
    public function test_employer_delete_job()
    {
        $job = JobListing::create(
            [
                'title' => 'Job Title',
                'description' => 'test',
                'application_instruction' => 'test',
                'company_id' => 1

            ]
        );
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->delete(
            '/api/employer/job-delete',
            [
                'jobId' => $job->id,
            ]
        );
        // Assert: Check if the response is successful
        $response->assertStatus(200)
            ->assertJson([

                'code' => 200,
                'success' => true,
                'message' => 'The job has been successfully deleted!!',
                'data' => null

            ]);
    }
    //Action : Employer deletes unauthorized job
    public function test_delete_unauthorized_job()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->delete(
            '/api/employer/job-delete',
            [
                'jobId' => 2,
            ]
        );
        // Assert: Check if the response indicates authentication failure with status code 403
        $response->assertStatus(403)
            ->assertJson([
                'code' => 403,
                'success' => false,
                'message' => 'Forbidden Access',
            ]);
    }
}
