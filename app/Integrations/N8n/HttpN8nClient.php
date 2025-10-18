<?php

declare(strict_types=1);

namespace App\Integrations\N8n;

use App\Contracts\Integrations\N8nAuthStrategyContract;
use App\Contracts\Integrations\N8nClientContract;
use App\DTOs\N8n\AdScriptDispatchRequestDTO;
use App\DTOs\N8n\AdScriptDispatchResponseDTO;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class HttpN8nClient implements N8nClientContract
{
    public function __construct(private readonly N8nAuthStrategyContract $authStrategy)
    {
    }

    public function dispatchTask(AdScriptDispatchRequestDTO $request): AdScriptDispatchResponseDTO
    {
        $timeoutMs = (int) Config::get('n8n.timeout_ms', 15000);
        $endpoint = (string) Config::get('n8n.webhook_url');
        $payload = $request->toArray();
        $http = Http::timeout($timeoutMs / 1000);
        $this->authStrategy->apply($http);
        $headers = [];

        // If using HMAC, compute signature over the raw payload
        if ($this->authStrategy instanceof HmacAuthStrategy) {
            $raw = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $secret = Config::get('n8n.hmac_secret');
            $signature = base64_encode(hash_hmac('sha256', (string) $raw, (string) $secret, true));
            $headers['X-Signature'] = $signature;
        }

        $response = $http->withHeaders($headers)->post($endpoint, $payload);
        $response->throw();
        $data = $response->json();

        if (!is_array($data)) {
            return new AdScriptDispatchResponseDTO($request->taskId, null, null);
        }

        $taskId = (int) Arr::get($data, 'task_id', $request->taskId);

        return new AdScriptDispatchResponseDTO(
            $taskId,
            Arr::get($data, 'new_script'),
            Arr::get($data, 'analysis')
        );
    }
}


