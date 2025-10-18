<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class TaskStatusChangedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TaskStatusChanged $event): void
    {
        Log::info('Task status changed', [
            'task_id' => $event->task->id,
            'status' => $event->task->status,
            'updated_at' => $event->task->updated_at,
        ]);
    }
}
