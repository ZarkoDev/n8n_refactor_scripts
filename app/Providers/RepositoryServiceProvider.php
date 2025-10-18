<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\AdScriptTaskRepositoryContract;
use App\Repositories\EloquentAdScriptTaskRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdScriptTaskRepositoryContract::class, EloquentAdScriptTaskRepository::class);
    }
}


