<?php

declare(strict_types=1);

return [
    // Base URL of your n8n instance, e.g. https://n8n.example.com or http://localhost:5678
    'base_url' => env('N8N_BASE_URL'),

    // If using a pre-configured webhook endpoint rather than REST API
    'webhook_url' => env('N8N_WEBHOOK_URL'),

    // Auth mode for inter-service calls: 'bearer' or 'hmac'
    'auth_mode' => env('N8N_AUTH_MODE', 'bearer'),

    // Bearer token for Authorization header (if using bearer auth)
    'bearer_token' => env('N8N_BEARER_TOKEN'),

    // API key if your n8n instance expects it (optional, depends on your setup)
    'api_key' => env('N8N_API_KEY'),

    // HMAC secret used to sign/verify payloads (if using HMAC auth)
    'hmac_secret' => env('N8N_HMAC_SECRET'),

    // Milliseconds timeout for outbound HTTP calls to n8n
    'timeout_ms' => (int) env('N8N_TIMEOUT_MS', 15000),
];


