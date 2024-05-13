<?php

namespace JoseBaroni\JobMonitor;

use Illuminate\Support\ServiceProvider;
use JoseBaroni\JobMonitor\Console\Commands\JobMonitorInstallCommand;
use JoseBaroni\JobMonitor\Console\Commands\JobMonitorResetCommand;
use JoseBaroni\JobMonitor\Repositories\MonitorRepository;

class JobMonitorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('jobmonitor.php'),
            ], 'jobmonitor-config');

            $this->commands([
                JobMonitorResetCommand::class,
                JobMonitorInstallCommand::class
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'jobmonitor');

        $shouldBootMonitor =
            config('jobmonitor.enabled', false) &&
            ! $this->app->runningUnitTests() &&
            $this->app->runningInConsole();

        if ($shouldBootMonitor) {
            $workerManager = resolve(WorkerManager::class);
            $workerManager->boot();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'jobmonitor');

        $driver = config('jobmonitor.driver');
        $this->app->bind(MonitorRepository::class, $driver);
    }
}
