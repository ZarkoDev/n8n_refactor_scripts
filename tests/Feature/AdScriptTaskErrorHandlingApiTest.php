<?php

declare(strict_types=1);

namespace Feature;

use App\Models\AdScriptTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdScriptTaskErrorHandlingApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test result endpoint with invalid token
     */
    public function test_result_with_invalid_token(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
            'response' => '```json{invalid json structure}```',
        ];

        $response = $this->withHeader('Authorization', 'Bearer 12345')
            ->postJson("/api/ad-scripts/{$task->id}/result", $payload);

        $response->assertStatus(401);
    }

    /**
     * Test result endpoint with invalid JSON response
     */
    public function test_result_with_invalid_json_response_marks_task_failed(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
            'response' => '```json{invalid json structure}```',
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/result", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.new_script', null)
            ->assertJsonPath('data.analysis', null);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'failed',
            'new_script' => null,
            'analysis' => null,
        ]);
    }

    /**
     * Test result endpoint with empty JSON response
     */
    public function test_result_with_empty_json_response_marks_task_failed(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
            'response' => '```json{}```',
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/result", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.new_script', null)
            ->assertJsonPath('data.analysis', null);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'failed',
            'new_script' => null,
            'analysis' => null,
        ]);
    }

    /**
     * Test result endpoint with array values in JSON
     */
    public function test_result_with_array_values_in_json_marks_task_failed(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
            'response' => '```json{"new_script": ["script1", "script2"], "analysis": ["analysis1"]}```',
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/result", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.new_script', null)
            ->assertJsonPath('data.analysis', null);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'failed',
            'new_script' => null,
            'analysis' => null,
        ]);
    }

    /**
     * Test result endpoint with missing new_script field
     */
    public function test_result_with_missing_new_script_field_marks_task_failed(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
            'response' => '```json{"analysis": "Some analysis"}```',
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/result", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.new_script', null)
            ->assertJsonPath('data.analysis', null);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'failed',
            'new_script' => null,
            'analysis' => null,
        ]);
    }
}
