<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AdScriptTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAdScriptTasksAction
{
    public function execute(?string $status, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return AdScriptTask::query()
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search !== null && $search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_script', 'like', '%'.$search.'%')
                       ->orWhere('outcome_description', 'like', '%'.$search.'%');
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}


