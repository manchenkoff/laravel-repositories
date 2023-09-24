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
        $stubFiles = [
            __DIR__ . '/Commands/stubs' => app_path('stubs'),
        ];

        $this->publishes($stubFiles);

        if ($this->app->runningInConsole()) {
            $commands = [
                RepositoryClassMakeCommand::class,
                RepositoryContractMakeCommand::class,
            ];

            $this->commands($commands);
        }
    }
}
