<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\JobApplicationController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\Joblisting;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\JobApplication;

class JobApplicationUnitTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    protected $faker;

    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_job_application_submit()
    {
        $this->faker = Faker::create();
        $user = User::factory()->create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'status' => 1,
            'role' => 'seeker'
        ]);
        $this->actingAs($user);
        Storage::fake('local');
        $resume = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        // Create a job listing uaing an exsisting company id
        $job = JobListing::factory()->create([
            'company_id' => 1,
        ]);
        $request = new Request([
            'coverLetter' => 'This is unit test for job applixation cover letter',
            'jobId' => $job->id,
        ]);
        $request->files->set('resume', $resume);
        $controller = new JobApplicationController();
        $response = $controller->submitApplication($request);
        // dd($response);

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
    }

    public function test_job_application_approve()
    {
        $this->faker = Faker::create();
        $user = User::factory()->create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'status' => 1,
            'role' => 'employer'
        ]);
        $company = Company::factory()->create([
            'employer_id' => $user->id,
        ]);
        $job = JobListing::factory()->create([
            'company_id' => $company->id,
        ]);
        $jobApplication = JobApplication::factory()->create([
            'job_id' => $job->id,
        ]);
        $this->actingAs($user);

        $request = new Request([
            'jobApplicationId' => $jobApplication->id,
        ]);
        $controller = new JobApplicationController();
        $response = $controller->approve($request);
        // dd($response);

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
    }

    public function test_job_application_reject()
    {
        $this->faker = Faker::create();
        $user = User::factory()->create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'status' => 1,
            'role' => 'employer'
        ]);
        $company = Company::factory()->create([
            'employer_id' => $user->id,
        ]);
        $job = JobListing::factory()->create([
            'company_id' => $company->id,
        ]);
        $jobApplication = JobApplication::factory()->create([
            'job_id' => $job->id,
        ]);
        $this->actingAs($user);

        $request = new Request([
            'jobApplicationId' => $jobApplication->id,
        ]);
        $controller = new JobApplicationController();
        $response = $controller->reject($request);
        // dd($response);

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
    }
}
