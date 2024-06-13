<?php

namespace Tests\Unit;

use Tests\TestCase;


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

    //Testing fo getting  all the jobs posted by authorized employer
    public function test_get_job_list()
    {
        // $response = $this->getJson('/api/employer/job-listings'); // Replace with your actual endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])
            ->getJson('/api/employer/job-listings');

        // Assert JSON structure and check for specific fields
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


        // Assert specific values in the response JSON
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


    //Testing for storing the jobs posted by authorized employer

    public function test_store_job()
    {
        // Act: Attempt to store jobs with valid details
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
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'company_id', 'description', 'application_instruction'
                ]
            ]);
    }

    public function test_store_jobs_without_title()
    {
        // Act: Attempt to store jobs without title
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
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        $response->assertStatus(422)
            ->assertJsonFragment([
                'code' => 422,
                'success' => false,
                'message' => [
                    'The title field is required.'
                ]
            ]);
    }


    //Testing for  updating the jobs posted by authorized employer

    public function test_update_job()
    {
        // Act: Attempt to update jobs with valid details
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
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'description', 'application_instruction', 'status'
                ]
            ]);
    }

    public function test_update_job_without_data()
    {
        // Act: Attempt to update jobs without providing job id
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
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        $response->assertStatus(422)
            ->assertJsonFragment([
                'code' => 422,
                'success' => false,
                'message' => [
                    'The job id field is required.'
                ]
            ]);
    }

    public function test_update_unauthorized_job()
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
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        $response->assertStatus(403)
            ->assertJson([
                'code' => 403,
                'success' => false,
                'message' => 'Forbidden Access',
            ]);
    }


    //Testing for  updating the jobs posted by authorized employer

    public function test_delete_job()
    {
        // Act: Attempt to store jobs with valid details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->delete(
            '/api/employer/job-delete',
            [
                'jobId' => 9,
            ]
        );
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        // Assert: Check if the response indicates authentication failure
        $response->assertStatus(200)
            ->assertJson([

                'code' => 200,
                'success' => true,
                'message' => 'The job has been successfully deleted!!',
                'data' => null

            ]);
    }

    public function test_delete_unauthorized_job()
    {
        // Act: Attempt to store jobs with valid details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->delete(
            '/api/employer/job-delete',
            [
                'jobId' => 2,
            ]
        );
        // Assert: Check if the response is successful and contains a token or user data
        // dd($response);
        // Assert: Check if the response indicates authentication failure
        $response->assertStatus(403)
            ->assertJson([
                'code' => 403,
                'success' => false,
                'message' => 'Forbidden Access',
            ]);
    }
}
