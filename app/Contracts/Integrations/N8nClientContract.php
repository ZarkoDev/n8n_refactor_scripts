<?php

declare(strict_types=1);

namespace App\Contracts\Integrations;

use App\DTOs\N8n\AdScriptDispatchRequestDTO;
use App\DTOs\N8n\AdScriptDispatchResponseDTO;

interface N8nClientContract
{
    public function dispatchTask(AdScriptDispatchRequestDTO $request): AdScriptDispatchResponseDTO;
}


