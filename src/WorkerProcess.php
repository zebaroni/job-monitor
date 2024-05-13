<?php

namespace JoseBaroni\JobMonitor;

class WorkerProcess
{
    private int $pid;
    private string $jobType;
    private string $jobId;
    private float $startTime;

    public function __construct(
        int $pid,
        string $jobType,
        string $jobId,
        float $startTime
    ) {
        $this->pid = $pid;
        $this->jobType = $jobType;
        $this->jobId = $jobId;
        $this->startTime = $startTime;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function getJobType(): string
    {
        return $this->jobType;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getElapsedTimeMs(): float
    {
        return (microtime(true) - $this->startTime) * 1000;
    }
}