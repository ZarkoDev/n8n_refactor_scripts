<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdScriptTaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'reference_script' => $this->reference_script,
            'outcome_description' => $this->outcome_description,
            'new_script' => $this->new_script,
            'analysis' => $this->analysis,
            'status' => $this->status,
            'error' => $this->error,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}


