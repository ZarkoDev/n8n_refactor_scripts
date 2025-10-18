<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestFailureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // Fail immediately after first attempt

    public function __construct(
        private readonly string $failureReason = 'Test failure'
    ) {
    }

    public function handle(): void
    {
        // Always throw an exception to force failure
        throw new \Exception($this->failureReason);
    }
}
