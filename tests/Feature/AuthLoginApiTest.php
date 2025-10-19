<?php

declare(strict_types=1);

namespace Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login with valid credentials
     */
    public function test_login_with_valid_credentials_returns_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200)
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
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'test@example.com')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('message', 'Login successful');

        // Verify token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Test login with invalid email
     */
    public function test_login_with_invalid_email_returns_validation_error(): void
    {
        $payload = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'The provided credentials are incorrect.')
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
    }

    /**
     * Test login with invalid password
     */
    public function test_login_with_invalid_password_returns_validation_error(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'The provided credentials are incorrect.')
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
    }

    /**
     * Test login with missing email
     */
    public function test_login_with_missing_email_returns_validation_error(): void
    {
        $payload = [
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Email address is required.');
    }

    /**
     * Test login with missing password
     */
    public function test_login_with_missing_password_returns_validation_error(): void
    {
        $payload = [
            'email' => 'test@example.com',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'Password is required.');
    }
}
