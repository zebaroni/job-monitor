<?php

namespace JoseBaroni\JobMonitor\Console\Commands;

use Illuminate\Console\Command;
use JoseBaroni\JobMonitor\Repositories\MonitorRepository;

class JobMonitorResetCommand extends Command
{
    protected $signature = 'jobmonitor:reset {--metrics} {--workers}';

    protected $description = 'This commands deletes saved metadata from active workers or job metrics.';

    private MonitorRepository $monitorRepository;

    public function handle(MonitorRepository $monitorRepository): void
    {
        $this->monitorRepository = $monitorRepository;

        if ($this->option('workers')) {
            $this->resetWorkers();
        }

        if ($this->option('metrics')) {
            $this->resetJobsMetrics();
        }

        if (! $this->option('workers') && ! $this->option('metrics')) {
            $this->resetWorkers();
            $this->resetJobsMetrics();
        }
    }

    private function resetWorkers(): void
    {
        $this->monitorRepository->removeAllWorkers();
        $this->info('Workers Metadata removed');
    }

    private function resetJobsMetrics(): void
    {
        $this->monitorRepository->removeAllJobsMetrics();
        $this->info('Jobs Metrics removed');
    }
}
