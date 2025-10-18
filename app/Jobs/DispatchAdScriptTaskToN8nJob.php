<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Integrations\N8nClientContract;
use App\Contracts\Repositories\AdScriptTaskRepositoryContract;
use App\DTOs\N8n\AdScriptDispatchRequestDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class DispatchAdScriptTaskToN8nJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10; // seconds

    public function __construct(private readonly int $taskId)
    {
    }

    public function handle(
        AdScriptTaskRepositoryContract $repository,
        N8nClientContract $client
    ): void {
        $task = $repository->findById($this->taskId);

        if ($task === null) {
            return;
        }

        $dto = new AdScriptDispatchRequestDTO(
            taskId: $task->id,
            referenceScript: (string) $task->reference_script,
            outcomeDescription: (string) $task->outcome_description,
        );

        try {
            $response = $client->dispatchTask($dto);

            if ($response->newScript !== null && $response->analysis !== null) {
                $repository->markCompleted($response->taskId, $response->newScript, $response->analysis);
            }
        } catch (Throwable $e) {
            $repository->markFailed($this->taskId, $e->getMessage());
            throw $e;
        }
    }
}


