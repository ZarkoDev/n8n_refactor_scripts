<?php

declare(strict_types=1);

namespace App\DTOs\N8n;

final class AdScriptDispatchResponseDTO
{
    public function __construct(
        public readonly int $taskId,
        public readonly ?string $newScript,
        public readonly ?string $analysis,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['task_id'],
            $data['new_script'] ?? null,
            $data['analysis'] ?? null,
        );
    }
}


