<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Integrations\N8nAuthStrategyContract;
use App\Contracts\Integrations\N8nClientContract;
use App\Integrations\N8n\BearerAuthStrategy;
use App\Integrations\N8n\HmacAuthStrategy;
use App\Integrations\N8n\HttpN8nClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(N8nAuthStrategyContract::class, function (): N8nAuthStrategyContract {
            $mode = (string) Config::get('n8n.auth_mode', 'bearer');
            return match ($mode) {
                'hmac' => new HmacAuthStrategy(Config::get('n8n.hmac_secret')),
                default => new BearerAuthStrategy(Config::get('n8n.bearer_token')),
            };
        });

        $this->app->bind(N8nClientContract::class, function ($app): N8nClientContract {
            return new HttpN8nClient($app->make(N8nAuthStrategyContract::class));
        });
    }
}


