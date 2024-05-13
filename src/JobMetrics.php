<?php

namespace JoseBaroni\JobMonitor;

class JobMetrics
{
    private string $jobType;
    private int $count;
    private float $totalTimeMs;
    private float $peakTimeMs;
    private float $totalMemoryBytes;
    private float $peakMemoryBytes;
    private float $totalCpuMs;
    private float $peakCpuMs;
    private float $totalPayloadKb;
    private float $peakPayloadKb;

    public function __construct(
        string $jobType,
        int $count,
        float $totalTimeMs,
        float $peakTimeMs,
        float $totalMemoryBytes,
        float $peakMemoryBytes,
        float $totalCpuMs,
        float $peakCpuMs,
        float $totalPayloadKb,
        float $peakPayloadKb
    ) {
        $this->jobType = $jobType;
        $this->count = $count;
        $this->totalTimeMs = $totalTimeMs;
        $this->peakTimeMs = $peakTimeMs;
        $this->totalMemoryBytes = $totalMemoryBytes;
        $this->peakMemoryBytes = $peakMemoryBytes;
        $this->totalCpuMs = $totalCpuMs;
        $this->peakCpuMs = $peakCpuMs;
        $this->totalPayloadKb = $totalPayloadKb;
        $this->peakPayloadKb = $peakPayloadKb;
    }

    public function getJobType(): string
    {
        return $this->jobType;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getTotalTimeMs(): float
    {
        return $this->totalTimeMs;
    }

    public function getPeakTimeMs(): float
    {
        return $this->peakTimeMs;
    }

    public function getTotalMemoryBytes(): float
    {
        return $this->totalMemoryBytes;
    }

    public function getPeakMemoryBytes(): float
    {
        return $this->peakMemoryBytes;
    }

    public function getTotalCpuMs(): float
    {
        return $this->totalCpuMs;
    }

    public function getPeakCpuMs(): float
    {
        return $this->peakCpuMs;
    }

    public function getAvgCpuMs(): float
    {
        return $this->totalCpuMs / $this->count;
    }

    public function getAvgPayloadKb(): float
    {
        return $this->totalPayloadKb / $this->count;
    }

    public function getPeakPayloadKb(): float
    {
        return $this->peakPayloadKb;
    }

    public function getAvgMemoryUsageBytes(): float
    {
        return $this->totalMemoryBytes / $this->count;
    }

    public function getAvgTimeMs(): float
    {
        return $this->totalTimeMs / $this->count;
    }
}