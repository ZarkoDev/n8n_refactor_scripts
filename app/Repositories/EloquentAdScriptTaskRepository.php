<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\AdScriptTaskRepositoryContract;
use App\Models\AdScriptTask;

class EloquentAdScriptTaskRepository implements AdScriptTaskRepositoryContract
{
    public function createPending(string $referenceScript, string $outcomeDescription): AdScriptTask
    {
        return AdScriptTask::query()->create([
            'reference_script' => $referenceScript,
            'outcome_description' => $outcomeDescription,
            'status' => 'pending',
        ]);
    }

    public function findById(int $id): ?AdScriptTask
    {
        return AdScriptTask::query()->find($id);
    }

    public function markCompleted(int $id, string $newScript, string $analysis): AdScriptTask
    {
        $task = AdScriptTask::query()->findOrFail($id);
        $task->fill([
            'new_script' => $newScript,
            'analysis' => $analysis,
            'status' => 'completed',
            'error' => null,
        ])->save();
        $task->refresh();

        return $task;
    }

    public function markFailed(int $id, string $error): AdScriptTask
    {
        $task = AdScriptTask::query()->findOrFail($id);
        $task->fill([
            'status' => 'failed',
            'error' => $error,
        ])->save();
        $task->refresh();

        return $task;
    }
}


