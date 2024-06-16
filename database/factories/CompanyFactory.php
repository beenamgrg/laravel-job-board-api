<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name;
        return [
            'name' => $name,
            'slug' => str_replace(' ', '-', strtolower($name)),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => rand(1000000, 200000),
            'status' => 1,
            'employer_id' => User::factory(),
            'address' => fake()->address(),
            'description' => 'Description for test',
            'status' => 1
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
