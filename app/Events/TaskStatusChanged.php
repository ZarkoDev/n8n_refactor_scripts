<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\AdScriptTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly AdScriptTask $task
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('tasks'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'status' => $this->task->status,
                'reference_script' => $this->task->reference_script,
                'outcome_description' => $this->task->outcome_description,
                'new_script' => $this->task->new_script,
                'analysis' => $this->task->analysis,
                'error' => $this->task->error,
                'created_at' => $this->task->created_at,
                'updated_at' => $this->task->updated_at,
            ],
        ];
    }
}
