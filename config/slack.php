<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Slack Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Slack notifications via n8n webhooks
    |
    */

    'failed_jobs_webhook_url' => env('SLACK_FAILED_JOBS_WEBHOOK_URL'),
    'webhook_secret' => env('SLACK_WEBHOOK_SECRET'),
];
