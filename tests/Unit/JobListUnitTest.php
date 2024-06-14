<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\JobController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Joblisting;
use Illuminate\Support\Facades\Auth;

class JobListUnitTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    protected $user;
    protected $company;
    protected $job;


    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function setUp(): void
    {
        parent::setUp();
        // Create a test user for storing a new job
        $this->company = Company::factory()->create();
        $this->user = User::where('id', $this->company->employer_id)->first();
        $this->actingAs($this->user);
    }

    public function test_job_store()
    {
        // Simulate user login
        $request = new Request([
            'title' => 'test job',
            'description' => 'description for the job',
            'applicationInstruction' => 'application instruction for the job',
        ]);

        $controller = new JobController();
        $response = $controller->store($request);

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
    }
    public function test_job_update()
    {

        // Create a job listing for the company
        $job = JobListing::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $request = new Request([
            'title' => 'Updated Software Developer',
            'description' => 'This is the updated job',
            'applicationInstruction' => 'Application instruction for the updated job',
            'jobId' => $job->id
        ]);
        $controller = new JobController();
        $response = $controller->update($request);
        // dd($this->data->data->id);

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
    }
    public function test_job_delete()
    {

        // Create a job listing for the company
        $job = JobListing::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $request = new Request([
            'jobId' => $job->id
        ]);
        $controller = new JobController();
        $response = $controller->delete($request);
        // dd($this->data->data->id);

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
    }
}
