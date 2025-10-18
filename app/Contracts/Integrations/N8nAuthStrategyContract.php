<?php

declare(strict_types=1);

namespace App\Contracts\Integrations;

use Illuminate\Http\Client\PendingRequest;

interface N8nAuthStrategyContract
{
    public function apply(PendingRequest $request): void;
}


