<?php

namespace Tests\Feature;

use Tests\TestCase;
use Faker\Factory as Faker;

class AuthenticationTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

     // Action :  Attempt to log in with the correct credentials
    public function test_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'employer1@gmail.com',
            'password' => 'employer',
        ]);

        // Assert : Check if the response is successful and contains a token
        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    //Action : Test login using invalid credentials
    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'employer1@gmail.com',
            'password' => 'wrongpassword',
        ]);

        //Assert : Check if the response indicates authentication failure
        $response->assertStatus(401)
            ->assertJson([
                [
                    'code' => 401,
                    'success' => false,
                    'message' => 'Credentials did not match'
                ]
            ]);
    }

    //Action : Test user sign up using correct credentials
    public function test_user_sign_up_with_valid_credential()
    {
        $faker = Faker::create();
        // Act: Attempt to register with valid details
        $response = $this->postJson('/api/sign-up', [
            'firstName' => $faker->name,
            'lastName' => $faker->name,
            'email' => $faker->email,
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ]);

        // Assert: Check if the response is successful and contains a token and user data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'email', 'role'
                ],
                'token'
            ]);
    }

    //Action : Test user sign up with missing email
    public function test_user_sign_up_with_missing_email()
    {
        $response = $this->postJson('/api/sign-up', [
            'firstName' => 'Test',
            'lastName' => 'User',
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ]);

        // Assert: Check if the the response returns validation error 422
        $response->assertStatus(422)
            ->assertJsonFragment([
                'code' => 422,
                'success' => false,
                'message' => [
                    'The email field is required.'
                ]
            ]);
    }

    //Action : Test user sign up with duplicate email
    public function test_user_sign_up_with_duplicate_email()
    {
        $response = $this->postJson('/api/sign-up', [
            'firstName' => 'Test',
            'lastName' => 'User',
            'email' => 'testuser1234@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ]);

        // Assert: Check if the the response returns validation error 422
        $response->assertStatus(422)
            ->assertJsonFragment([
                'code' => 422,
                'success' => false,
                'message' => [
                    'The email has already been taken.'
                ]
            ]);
    }
}
