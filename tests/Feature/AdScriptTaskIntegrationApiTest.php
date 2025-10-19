<?php

declare(strict_types=1);

namespace Feature;

use App\Events\TaskStatusChanged;
use App\Jobs\DispatchAdScriptTaskToN8nJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Broadcasting\BroadcastEvent;

class AdScriptTaskIntegrationApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete workflow: create task -> mark as completed
     */
    public function test_complete_workflow_create_and_complete(): void
    {
        Queue::fake();

        // Step 1: Create a task
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $payload = [
            'reference_script' => 'Close the door',
            'outcome_description' => 'Ask more friendly',
        ];

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/ad-scripts', $payload);
        $createResponse->assertStatus(201);

        $taskId = $createResponse->json('data.id');
        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $taskId,
            'status' => 'pending',
        ]);

        Queue::assertPushed(DispatchAdScriptTaskToN8nJob::class);

        // Step 2: Mark task as completed
        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');
        $newScript = 'new script';
        $newAnalysis = 'new analysis';

        $completePayload = [
            'task_id' => $taskId,
            'response' => '```json{"new_script": "'.$newScript.'", "analysis": "'.$newAnalysis.'"}```',
        ];

        $completeResponse = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$taskId}/result", $completePayload);

        $completeResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.new_script', $newScript)
            ->assertJsonPath('data.analysis', $newAnalysis);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $taskId,
            'status' => 'completed',
            'new_script' => $newScript,
            'analysis' => $newAnalysis,
        ]);

        Queue::assertPushed(BroadcastEvent::class, function ($job) {
            return $job->event instanceof TaskStatusChanged;
        });
    }
}
