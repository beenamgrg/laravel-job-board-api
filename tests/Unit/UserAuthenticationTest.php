<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\SessionController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class UserAuthenticationTest extends TestCase
{

    /**
     * A basic unit test example.
     */

    protected $faker;
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_user_login()
    {
        // Arrange
        $this->faker = Faker::create();
        $user = User::factory()->make([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'status' => 1
        ]);

        // Mock the Auth facade
        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => $user->email, 'password' => 'password'])
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $request = new Request([
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Create an instance of the controller
        $controller = new SessionController();

        // Act
        $response = $controller->postLogin($request);

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
    }

    public function test_user_sign_up()
    {
        // Arrange
        $this->faker = Faker::create();

        $userData = [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ];

        // Create a new Request instance with signup data
        $request = new Request($userData);

        // Create an instance of the controller
        $controller = new SessionController();

        // Act: Call the store method which handles signup
        $response = $controller->store($request);

        // Assert: Verify the response
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }
}
