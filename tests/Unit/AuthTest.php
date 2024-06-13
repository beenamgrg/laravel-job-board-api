<?php

namespace Tests\Unit;

use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }
    public function test_login_with_valid_credentials()
    {
        // Act: Attempt to log in with the correct credentials
        $response = $this->postJson('/api/login', [
            'email' => 'employer1@gmail.com',
            'password' => 'employer',
        ]);

        // Assert: Check if the response is successful and contains a token
        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }
    public function test_login_with_invalidvalid_credentials()
    {
        // Act: Attempt to log in with the correct credentials
        $response = $this->postJson('/api/login', [
            'email' => 'employer1@gmail.com',
            'password' => 'wrongpassword',
        ]);
        // dd($response);

        // Assert: Check if the response indicates authentication failure
        $response->assertStatus(401)
            ->assertJson([
                [
                    'code' => 401,
                    'success' => false,
                    'message' => 'Credentials did not match'
                ]
            ]);
    }

    public function test_user_sign_up()
    {
        // Act: Attempt to register with valid details
        $response = $this->postJson('/api/sign-up', [
            'firstName' => 'Test ',
            'lastName' => 'User',
            'email' => 'testuser6@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ]);
        // dd($response);

        // Assert: Check if the response is successful and contains a token or user data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'email', 'role'
                ],
                'token'
            ]);
    }
    public function test_user_cannot_register_with_missing_email()
    {
        $response = $this->postJson('/api/sign-up', [
            'firstName' => 'Test',
            'lastName' => 'User',
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ]);
        // dd($response);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'code' => 422,
                'success' => false,
                'message' => [
                    'The email field is required.'
                ]
            ]);
    }

    public function test_user_cannot_register_with_duplicate_email()
    {
        $response = $this->postJson('/api/sign-up', [
            'firstName' => 'Test',
            'lastName' => 'User',
            'email' => 'testuser1234@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ]);

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
