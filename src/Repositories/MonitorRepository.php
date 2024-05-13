<?php

namespace JoseBaroni\JobMonitor\Repositories;

use JoseBaroni\JobMonitor\JobMetrics;
use JoseBaroni\JobMonitor\JobProcessingState;
use JoseBaroni\JobMonitor\WorkerProcess;

interface MonitorRepository
{
    /**
     * @return string[]
     */
    public function getAllWorkers(): array;

    /**
     * @param string $worker
     * @return WorkerProcess[]
     */
    public function getWorkerProcesses(string $worker): array;

    public function removeWorkerProcess(string $worker, int $pid): void;

    public function removeAllWorkers(): void;

    /**
     * @return JobMetrics[]
     */
    public function getAllJobMetrics(): array;

    public function removeAllJobsMetrics(): void;

    public function startJobProcessing(JobProcessingState $state);

    public function finishJobProcessing(JobProcessingState $state);
}
