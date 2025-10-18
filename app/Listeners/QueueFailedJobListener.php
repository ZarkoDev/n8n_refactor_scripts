<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class QueueFailedJobListener
{
    public function handle(JobFailed $event): void
    {
        $job = $event->job;
        $exception = $event->exception;
        
        $payload = [
            'job_id' => $job->getJobId(),
            'job_name' => $job->getName(),
            'queue' => $job->getQueue(),
            'failed_at' => now()->toISOString(),
            'exception' => [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ],
            'payload' => $job->payload(),
        ];

        try {
            $webhookUrl = config('slack.failed_jobs_webhook_url');
            if ($webhookUrl) {
                $response = Http::timeout(10)->post($webhookUrl, $payload);
                $response->throw();
                
                Log::info('Failed job notification sent to Slack', [
                    'job_id' => $job->getJobId(),
                    'webhook_url' => $webhookUrl,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Failed to send job failure notification to Slack', [
                'job_id' => $job->getJobId(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
