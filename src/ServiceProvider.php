<?php

declare(strict_types=1);

namespace Manchenkoff\Laravel\Repositories;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Manchenkoff\Laravel\Repositories\Commands\RepositoryClassMakeCommand;
use Manchenkoff\Laravel\Repositories\Commands\RepositoryContractMakeCommand;

final class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/repositories.php', 'repositories');

        $this->publishes([
            __DIR__ . '/../config/repositories.php' => config_path('repositories.php'),
            __DIR__ . '/Commands/stubs' => base_path('stubs'),
        ]);

        if ($this->app->runningInConsole()) {
            $commands = [
                RepositoryClassMakeCommand::class,
                RepositoryContractMakeCommand::class,
            ];

            $this->commands($commands);
        }
    }
}
