<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class JobListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = JobListing::class;

    public function definition()
    {
        return [
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'application_instruction' => $this->faker->sentence,
            'company_id' => Company::factory(),
        ];
    }
}
