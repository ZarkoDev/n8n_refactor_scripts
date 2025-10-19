<?php

declare(strict_types=1);

namespace Feature;

use App\Contracts\Repositories\AdScriptTaskRepositoryContract;
use App\Jobs\DispatchAdScriptTaskToN8nJob;
use App\Models\AdScriptTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RetryTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_task_resets_status_to_pending(): void
    {
        // Create a failed task
        $task = AdScriptTask::factory()->create([
            'status' => 'failed',
            'error' => 'Test error message',
        ]);

        $repository = app(AdScriptTaskRepositoryContract::class);

        // Retry the task
        $retriedTask = $repository->retryTask($task->id);

        // Assert the task status is reset to pending
        $this->assertEquals('pending', $retriedTask->status);
        $this->assertNull($retriedTask->error);
    }

    public function test_retry_task_throws_exception_for_nonexistent_task(): void
    {
        $repository = app(AdScriptTaskRepositoryContract::class);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $repository->retryTask(999);
    }
}
