<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\JobListing;
use Illuminate\Support\Str;



/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'resume' => '/job-applications/resume/' . Str::random(5) . '.pdf',
            'cover_letter' => $this->faker->paragraph,
            'user_id' => User::factory(),
            'job_id' => Joblisting::factory(),
            'is_approved' => 0,
            'is_rejected' => 0,
            'status' => 1,
        ];
    }
}
