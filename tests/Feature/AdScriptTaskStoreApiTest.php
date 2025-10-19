<?php

declare(strict_types=1);

namespace Feature;

use App\Jobs\DispatchAdScriptTaskToN8nJob;
use App\Models\AdScriptTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdScriptTaskStoreApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful task creation with valid data
     */
    public function test_store_creates_task_with_valid_data(): void
    {
        Queue::fake();

        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $payload = [
            'reference_script' => 'Close the door',
            'outcome_description' => 'ask more friendly',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/ad-scripts', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'reference_script',
                    'outcome_description',
                    'new_script',
                    'analysis',
                    'status',
                    'error',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.reference_script', $payload['reference_script'])
            ->assertJsonPath('data.outcome_description', $payload['outcome_description'])
            ->assertJsonPath('data.new_script', null)
            ->assertJsonPath('data.analysis', null)
            ->assertJsonPath('data.error', null);

        $this->assertDatabaseHas('ad_script_tasks', [
            'reference_script' => $payload['reference_script'],
            'outcome_description' => $payload['outcome_description'],
            'status' => 'pending',
        ]);

        Queue::assertPushed(DispatchAdScriptTaskToN8nJob::class);
    }

    /**
     * Test task creation without authentication returns unauthorized
     */
    public function test_store_without_authentication_returns_unauthorized(): void
    {
        $payload = [
            'reference_script' => 'Close the door',
            'outcome_description' => 'ask more friendly',
        ];

        $response = $this->postJson('/api/ad-scripts', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test task creation with invalid token authentication returns unauthorized
     */
    public function test_store_with_invalid_token_returns_unauthorized(): void
    {
        $payload = [
            'reference_script' => 'Close the door',
            'outcome_description' => 'ask more friendly',
        ];

        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->postJson('/api/ad-scripts', $payload);

        $response->assertStatus(401);
    }

    /**
     * Test task creation with missing reference_script
     */
    public function test_store_with_missing_reference_script_returns_validation_error(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $payload = [
            'outcome_description' => 'ask more friendly',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/ad-scripts', $payload);

        $response = $this->postJson('/api/ad-scripts', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reference_script']);

        $this->assertDatabaseCount('ad_script_tasks', 0);
    }

    /**
     * Test task creation with missing outcome_description
     */
    public function test_store_with_missing_outcome_description_returns_validation_error(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $payload = [
            'reference_script' => 'Close the door',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/ad-scripts', $payload);

        $response = $this->postJson('/api/ad-scripts', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['outcome_description']);

        $this->assertDatabaseCount('ad_script_tasks', 0);
    }

    /**
     * Test task creation with minimum reference_script
     */
    public function test_store_with_minimum_reference_script_returns_validation_error(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $payload = [
            'reference_script' => 'Close',
            'outcome_description' => 'ask more friendly',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/ad-scripts', $payload);

        $response = $this->postJson('/api/ad-scripts', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reference_script']);

        $this->assertDatabaseCount('ad_script_tasks', 0);
    }

    /**
     * Test task creation with minimum outcome_description
     */
    public function test_store_with_minimum_outcome_description_returns_validation_error(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $payload = [
            'reference_script' => 'Close the door',
            'outcome_description' => 'ask',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/ad-scripts', $payload);

        $response = $this->postJson('/api/ad-scripts', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['outcome_description']);

        $this->assertDatabaseCount('ad_script_tasks', 0);
    }
}
