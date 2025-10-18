<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\AdScriptTask;

interface AdScriptTaskRepositoryContract
{
    /**
     * Create new AdScript
     *
     * @param string $referenceScript
     * @param string $outcomeDescription
     * @return AdScriptTask
     */
    public function createPending(string $referenceScript, string $outcomeDescription): AdScriptTask;

    /**
     * Find AdScript by ID
     *
     * @param int $id
     * @return AdScriptTask|null
     */
    public function findById(int $id): ?AdScriptTask;

    /**
     * Complete AdScript
     *
     * @param int $id
     * @param string $newScript
     * @param string $analysis
     * @return AdScriptTask
     */
    public function markCompleted(int $id, string $newScript, string $analysis): AdScriptTask;

    /**
     * Mark AdScript as failed
     *
     * @param int $id
     * @param string $error
     * @return AdScriptTask
     */
    public function markFailed(int $id, string $error): AdScriptTask;

}


