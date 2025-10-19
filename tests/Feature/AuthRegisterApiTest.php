<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRegisterApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration with valid data
     */
    public function test_register_with_valid_data_returns_success(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'token',
                'token_type',
            ])
            ->assertJsonPath('user.email', 'test@example.com')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('message', 'Registration successful');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Verify password is hashed
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * Test registration with missing email
     */
    public function test_register_with_missing_email_returns_validation_error(): void
    {
        $payload = [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Email address is required.');
    }

    /**
     * Test registration with invalid email format
     */
    public function test_register_with_invalid_email_returns_validation_error(): void
    {
        $payload = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Please provide a valid email address.');
    }

    /**
     * Test registration with duplicate email
     */
    public function test_register_with_duplicate_email_returns_validation_error(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'This email address is already registered.');
    }

    /**
     * Test registration with missing password
     */
    public function test_register_with_missing_password_returns_validation_error(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'Password is required.');
    }

    /**
     * Test registration with password too short
     */
    public function test_register_with_short_password_returns_validation_error(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'Password must be at least 8 characters.');
    }

    /**
     * Test registration with password confirmation mismatch
     */
    public function test_register_with_password_confirmation_mismatch_returns_validation_error(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'Password confirmation does not match.');
    }

}
