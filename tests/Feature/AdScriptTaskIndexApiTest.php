<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdScriptTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdScriptTaskIndexApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index endpoint returns paginated tasks without authentication
     */
    public function test_index_returns_paginated_tasks(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create test tasks
        AdScriptTask::factory()->count(15)->create();

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                ]
            ])
            ->assertJsonCount(10, 'data') // Default per_page is 10
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 15);
    }

    /**
     * Test index endpoint without authentication returns unauthorized
     */
    public function test_index_without_authentication_returns_unauthorized(): void
    {
        // Create some test tasks
        AdScriptTask::factory()->count(3)->create();

        // Access index without authentication
        $response = $this->getJson('/api/ad-scripts');

        $response->assertStatus(401);
    }

    /**
     * Test index endpoint with invalid token returns unauthorized
     */
    public function test_index_with_invalid_token_returns_unauthorized(): void
    {
        // Create some test tasks
        AdScriptTask::factory()->count(3)->create();

        // Access index with invalid token
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/ad-scripts');

        $response->assertStatus(401);
    }

    /**
     * Test index endpoint with empty authorization header returns unauthorized
     */
    public function test_index_with_empty_authorization_header_returns_unauthorized(): void
    {
        // Create some test tasks
        AdScriptTask::factory()->count(3)->create();

        // Access index with empty authorization header
        $response = $this->withHeader('Authorization', '')
            ->getJson('/api/ad-scripts');

        $response->assertStatus(401);
    }

    /**
     * Test index endpoint with valid authentication and filters
     */
    public function test_index_with_valid_authentication_and_filters(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create test tasks with different statuses
        AdScriptTask::factory()->count(3)->create(['status' => 'pending']);
        AdScriptTask::factory()->count(2)->create(['status' => 'completed']);
        AdScriptTask::factory()->count(1)->create(['status' => 'failed']);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Test filtering by status
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);

        // Verify all returned tasks have completed status
        foreach ($response->json('data') as $task) {
            $this->assertEquals('completed', $task['status']);
        }
    }

    /**
     * Test index endpoint with valid authentication and search
     */
    public function test_index_with_valid_authentication_and_search(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create test tasks with specific content
        AdScriptTask::factory()->create([
            'reference_script' => 'Close it',
            'outcome_description' => 'ask friendly',
        ]);
        AdScriptTask::factory()->create([
            'reference_script' => 'Close the door',
            'outcome_description' => 'ask friendly',
        ]);
        AdScriptTask::factory()->create([
            'reference_script' => 'Come here',
            'outcome_description' => 'ask friendly',
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Test search functionality
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts?search=door');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.reference_script', 'Close the door');
    }

    /**
     * Test index endpoint with valid authentication and combined filters
     */
    public function test_index_with_valid_authentication_and_status_filter(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create test tasks with specific status
        AdScriptTask::factory()->create([
            'reference_script' => 'Close it',
            'outcome_description' => 'ask friendly',
            'status' => 'pending',
        ]);
        AdScriptTask::factory()->create([
            'reference_script' => 'Close the door',
            'outcome_description' => 'ask friendly',
            'status' => 'completed',
        ]);
        AdScriptTask::factory()->create([
            'reference_script' => 'Come here',
            'outcome_description' => 'ask friendly',
            'status' => 'pending',
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Test combined filters
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('data.0.status', 'completed')
            ->assertJsonPath('data.0.reference_script', 'Close the door');
    }

    /**
     * Test index endpoint with valid authentication and pagination
     */
    public function test_index_with_valid_authentication_and_pagination(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create test tasks
        AdScriptTask::factory()->count(15)->create();

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Test pagination
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts?per_page=5&page=2');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.from', 6)
            ->assertJsonPath('meta.to', 10);
    }

    /**
     * Test index endpoint with valid authentication returns empty result when no tasks
     */
    public function test_index_with_valid_authentication_returns_empty_when_no_tasks(): void
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

        // Access index with no tasks
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('meta.from', null)
            ->assertJsonPath('meta.to', null);
    }

    /**
     * Test index endpoint with valid authentication and invalid filters
     */
    public function test_index_with_valid_authentication_and_invalid_filters(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create some test tasks
        AdScriptTask::factory()->count(3)->create();

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Test with invalid status filter
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts?status=invalid_status');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // Test with invalid per_page filter
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts?per_page=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);

        // Test with invalid page filter
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts?page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['page']);
    }

    /**
     * Test index endpoint with valid authentication and case insensitive search
     */
    public function test_index_with_valid_authentication_and_case_insensitive_search(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create test tasks
        AdScriptTask::factory()->create([
            'reference_script' => 'Close the door',
            'outcome_description' => 'more friendly',
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Test case insensitive search
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts?search=DOOR');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reference_script', 'Close the door');
    }

    /**
     * Test index endpoint with valid authentication and ordering
     */
    public function test_index_with_valid_authentication_and_ordering(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create test tasks with different creation times
        $task1 = AdScriptTask::factory()->create(['created_at' => now()->subDays(2)]);
        $task2 = AdScriptTask::factory()->create(['created_at' => now()->subDay()]);
        $task3 = AdScriptTask::factory()->create(['created_at' => now()]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Test ordering (should be by created_at desc by default)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ad-scripts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $task3->id)
            ->assertJsonPath('data.1.id', $task2->id)
            ->assertJsonPath('data.2.id', $task1->id);
    }
}
