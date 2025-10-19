<?php

declare(strict_types=1);

namespace Feature;

use App\Models\AdScriptTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdScriptTaskFailedApiTest extends TestCase
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
        ];

        $response = $this->withHeader('Authorization', 'Bearer 12345')
            ->postJson("/api/ad-scripts/{$task->id}/failed", $payload);

        $response->assertStatus(401);
    }

    /**
     * Test successful task failure with valid data
     */
    public function test_failed_updates_task_with_valid_data(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/failed", $payload);

        $response->assertStatus(200)
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
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.error', 'The n8n workflow failed.')
            ->assertJsonPath('data.new_script', null)
            ->assertJsonPath('data.analysis', null);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'failed',
            'error' => 'The n8n workflow failed.',
            'new_script' => null,
            'analysis' => null,
        ]);
    }

    /**
     * Test failed with task_id mismatch
     */
    public function test_failed_with_task_id_mismatch_returns_error(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => 999,
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/failed", $payload);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'The selected task id is invalid.');

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test failed with missing task_id
     */
    public function test_failed_with_missing_task_id_returns_validation_error(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/failed", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_id']);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test failed with invalid task_id type
     */
    public function test_failed_with_invalid_task_id_type_returns_validation_error(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => 'not-a-number',
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/failed", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_id']);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test failed with already failed task
     */
    public function test_failed_with_already_failed_task_updates_anyway(): void
    {
        $task = AdScriptTask::factory()->create([
            'status' => 'failed',
            'error' => 'Previous error',
        ]);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/failed", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.error', 'The n8n workflow failed.');

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'failed',
            'error' => 'The n8n workflow failed.',
        ]);
    }

    /**
     * Test failed with extra fields (should be ignored)
     */
    public function test_failed_with_extra_fields_ignores_them(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
            'extra_field' => 'This should be ignored',
            'error_message' => 'Custom error message',
            'status' => 'completed',
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/failed", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.error', 'The n8n workflow failed.');

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'failed',
            'error' => 'The n8n workflow failed.',
        ]);
    }

    /**
     * Test failed returns correct response structure
     */
    public function test_failed_returns_correct_response_structure(): void
    {
        $task = AdScriptTask::factory()->create(['status' => 'pending']);

        config()->set('n8n.auth_mode', 'bearer');
        config()->set('n8n.bearer_token', 'secret-token');

        $payload = [
            'task_id' => $task->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer secret-token')
            ->postJson("/api/ad-scripts/{$task->id}/failed", $payload);

        $response->assertStatus(200)
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
            ]);

        $responseData = $response->json('data');
        $this->assertIsInt($responseData['id']);
        $this->assertEquals('failed', $responseData['status']);
        $this->assertEquals('The n8n workflow failed.', $responseData['error']);
        $this->assertNull($responseData['new_script']);
        $this->assertNull($responseData['analysis']);
        $this->assertNotNull($responseData['created_at']);
        $this->assertNotNull($responseData['updated_at']);
    }
}
