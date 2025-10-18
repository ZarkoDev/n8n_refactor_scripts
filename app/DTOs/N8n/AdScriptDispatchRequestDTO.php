<?php

declare(strict_types=1);

namespace App\DTOs\N8n;

final class AdScriptDispatchRequestDTO
{
    public function __construct(
        public readonly int $taskId,
        public readonly string $referenceScript,
        public readonly string $outcomeDescription,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['task_id'],
            $data['reference_script'],
            $data['outcome_description'],
        );
    }

    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'reference_script' => $this->referenceScript,
            'outcome_description' => $this->outcomeDescription,
        ];
    }
}


