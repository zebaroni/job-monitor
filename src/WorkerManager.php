<?php

namespace JoseBaroni\JobMonitor;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use JoseBaroni\JobMonitor\Repositories\MonitorRepository;
use Throwable;

class WorkerManager
{
    private string $worker;
    private int $pid;
    private MonitorRepository $monitorRepository;

    public function __construct(MonitorRepository $monitorRepository)
    {
        $this->monitorRepository = $monitorRepository;
        $this->worker = gethostname();
        $this->pid = getmypid();
    }

    public function boot(): void
    {
        $this->registerSignalHooks();
        $this->removeOrphanProcesses();
        $this->bindQueueHooks();
    }

    private function bindQueueHooks(): void
    {
        $jobProcessingState = new JobProcessingState(
            $this->worker,
            $this->pid,
        );

        Queue::before(function (JobProcessing $event) use (&$jobProcessingState) {
            $jobProcessingState->currentJobId = $event->job->getJobId();
            $jobProcessingState->currentJobType = $event->job->resolveName();
            $jobProcessingState->currentJobStartTime = microtime(true);
            $jobProcessingState->cpuUsageBefore = getrusage();

            $payloadSize = strlen(serialize($event->job->payload()));
            $payloadSizeKB = round($payloadSize / 1024, 2);
            $jobProcessingState->payloadSizeKb = $payloadSizeKB;

            $this->monitorRepository->startJobProcessing($jobProcessingState);
        });

        Queue::after(function (JobProcessed $event) use (&$jobProcessingState) {
            $this->monitorRepository->finishJobProcessing($jobProcessingState);
        });

        Queue::failing(function (JobFailed $event) use (&$jobProcessingState) {
            $this->monitorRepository->finishJobProcessing($jobProcessingState);
        });
    }

    private function removeOrphanProcesses(): void
    {
        $processes = $this->monitorRepository->getWorkerProcesses($this->worker);

        foreach ($processes as $process) {
            $pid = $process->getPid();

            if (! posix_getpgid($pid)) {
                $this->monitorRepository->removeWorkerProcess($this->worker, $pid);
            }
        }
    }

    private function signalCallback(): void
    {
        try {
            $this->monitorRepository->removeWorkerProcess($this->worker, $this->pid);
        } catch (Throwable $e) {
        } finally {
            exit(1);
        }
    }

    private function registerSignalHooks(): void
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGHUP, fn() => $this->signalCallback());
        pcntl_signal(SIGTERM, fn() => $this->signalCallback());
        pcntl_signal(SIGINT, fn() => $this->signalCallback());
    }
}