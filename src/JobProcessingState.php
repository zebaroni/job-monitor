<?php

namespace JoseBaroni\JobMonitor;

class JobProcessingState
{
    public string $worker;
    public int $pid;
    public ?string $currentJobType;
    public ?string $currentJobId;
    public ?float $currentJobStartTime;
    public ?array $cpuUsageBefore;
    public float $payloadSizeKb;

    public function __construct(
        string $worker,
        int $pid
    ) {
        $this->worker = $worker;
        $this->pid = $pid;
    }
}