<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Models\JobListing;
use App\Models\JobApplication;
use App\Models\User;
use Faker\Factory as Faker;


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
    protected $job;
    protected $jobApplication;
    protected $newUser;


    //Set up data that can be used within the file
    public function setUp(): void
    {
        parent::setUp();

        // Login using a exsiting credential of employer to get its token and other data 
        $user = $this->postJson('/api/login', [
            'email' => 'employer1@gmail.com',
            'password' => 'employer',
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

        //Create new user
        $faker = Faker::create();

        // Act: Attempt to register with valid details
        $this->newUser = User::create([
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ]);

        //Create new job application
        Storage::fake('public');
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $this->jobApplication = JobApplication::create(
            [
                'title' => 'Job Title',
                'description' => 'test',
                'application_instruction' => 'test',
                'job_id' => $this->job->id,
                'user_id' => $this->newUser->id,
                'resume' => $file
            ]
        );
    }


    //Action : Test employer approve job application related to its company
    public function test_employer_approve_job_application()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-application-approve',
            [
                'jobApplicationId' => $this->jobApplication->id,
            ]
        );
        // Assert: Check if the response is successful and contains data 
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'resume', 'cover_letter', 'user_id', 'job_id', 'is_approved', 'is_rejected', 'status', 'job_title', 'company_name',  'company_email', 'applicant_name', 'applicant_email'
                ]
            ]);
    }

    //Action : Test employer approve job application already approved application related to its company
    public function test_employer_approve_job_application_again()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-application-approve',
            [
                'jobApplicationId' => 1,
            ]
        );
        // Assert: Check if the response is repeated action with status code 400
        $response->assertStatus(400)
            ->assertJson([
                'code' => 400,
                'success' => false,
                'message' => 'Job application has already been approved!!',
            ]);
    }

    //Action : Test employer reject job application related to its company
    public function test_employer_reject_job_application()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post(
            '/api/employer/job-application-reject',
            [
                'jobApplicationId' => $this->jobApplication->id,
            ]
        );
        // Assert: Check if the response is successful and contains data 
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'resume', 'cover_letter', 'user_id', 'job_id', 'is_approved', 'is_rejected', 'status', 'job_title', 'company_name',  'company_email', 'applicant_name', 'applicant_email'
                ]
            ]);
    }

    //Action : Test employer reject job application related to its company again
    public function test_employer_reject_job_application_again()
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
        // Assert: Check if the response is repeated action with status code 400
        $response->assertStatus(400)
            ->assertJson([
                'code' => 400,
                'success' => false,
                'message' => 'Job application has already been rejected!!',
            ]);
    }
}
