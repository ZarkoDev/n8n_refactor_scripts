<?php

declare(strict_types=1);

namespace App\Integrations\N8n;

use App\Contracts\Integrations\N8nAuthStrategyContract;
use Illuminate\Http\Client\PendingRequest;

class BearerAuthStrategy implements N8nAuthStrategyContract
{
    public function __construct(private readonly ?string $token)
    {
    }

    public function apply(PendingRequest $request): void
    {
        if ($this->token) {
            $request->withToken($this->token);
        }
    }
}


