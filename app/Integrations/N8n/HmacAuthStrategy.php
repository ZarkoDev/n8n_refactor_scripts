<?php

declare(strict_types=1);

namespace App\Integrations\N8n;

use App\Contracts\Integrations\N8nAuthStrategyContract;
use Illuminate\Http\Client\PendingRequest;

class HmacAuthStrategy implements N8nAuthStrategyContract
{
    public function __construct(private readonly ?string $secret)
    {
    }

    public function apply(PendingRequest $request): void
    {
        if (!$this->secret) {
            return;
        }

        $request->withHeaders([
            'X-Signature-Alg' => 'HMAC-SHA256',
        ]);
    }
}


