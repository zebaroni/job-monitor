<?php

namespace JoseBaroni\JobMonitor\Repositories;

use Illuminate\Support\Facades\Redis;
use JoseBaroni\JobMonitor\JobMetrics;
use JoseBaroni\JobMonitor\JobProcessingState;
use JoseBaroni\JobMonitor\WorkerProcess;
use Predis\ClientInterface;

class RedisMonitorRepository implements MonitorRepository
{
    private const ALL_WORKER_PROCESSES_KEY = 'all_workers_processes_keys';
    private const JOB_WORKER_PREFIX_KEY = 'job_worker:';
    private const JOBS_METRICS_KEY = 'jobs_metrics';

    private ClientInterface $redis;

    public function __construct()
    {
        $this->redis = Redis::connection(config('jobmonitor.redis_connection'))->client();
    }

    public function getAllWorkers(): array
    {
        $workersAndPids = $this->redis->smembers(self::ALL_WORKER_PROCESSES_KEY);
        $workers = [];

        foreach ($workersAndPids as $workerAndPid) {
            $workerName = explode(':', $workerAndPid)[0];
            if (! in_array($workerName, $workers)) {
                $workers[] = $workerName;
            }
        }

        return $workers;
    }

    public function getWorkerProcesses(string $worker): array
    {
        $processesKey = $this->redis->hgetall(self::JOB_WORKER_PREFIX_KEY . $worker);

        $workerProcesses = [];
        foreach ($processesKey as $pid => $processDetailsJSON) {
            $processDetails = json_decode($processDetailsJSON, true);

            $workerProcesses[] = new WorkerProcess(
                $pid,
                $processDetails['job_type'],
                $processDetails['job_id'],
                $processDetails['start_time']
            );
        }

        return $workerProcesses;
    }

    public function getAllJobMetrics(): array
    {
        $jobsMetricsKey = $this->redis->hgetall(self::JOBS_METRICS_KEY);

        $jobsMetrics = [];
        foreach ($jobsMetricsKey as $jobType => $jsonPayload) {
            $decodedMetrics = json_decode($jsonPayload, true);
            $jobsMetrics[] = new JobMetrics(
                $jobType,
                $decodedMetrics['count'],
                $decodedMetrics['total_time'],
                $decodedMetrics['peak_time'],
                $decodedMetrics['total_memory'],
                $decodedMetrics['peak_memory'],
                $decodedMetrics['total_cpu'],
                $decodedMetrics['peak_cpu'],
                $decodedMetrics['total_payload'],
                $decodedMetrics['peak_payload'],
            );
        }

        return $jobsMetrics;
    }

    public function removeWorkerProcess(string $worker, int $pid): void
    {
        $pipeline = $this->redis->pipeline();
        $pipeline->hdel(self::JOB_WORKER_PREFIX_KEY . $worker, [$pid]);
        $pipeline->srem(self::ALL_WORKER_PROCESSES_KEY, "{$worker}:{$pid}");
        $pipeline->execute();
    }

    public function startJobProcessing(JobProcessingState $state): void
    {
        $pipeline = $this->redis->pipeline();

        $pipeline->hset(self::JOB_WORKER_PREFIX_KEY . $state->worker, $state->pid, json_encode([
            'job_type' => $state->currentJobType,
            'job_id' => $state->currentJobId,
            'start_time' => $state->currentJobStartTime
        ]));

        $pipeline->sadd(self::ALL_WORKER_PROCESSES_KEY, ["{$state->worker}:{$state->pid}"]);

        $pipeline->execute();
    }

    public function finishJobProcessing(JobProcessingState $state)
    {
        $processingTimeMs = intval((microtime(true) - $state->currentJobStartTime) * 1000);
        $memoryUsage = memory_get_peak_usage(true);
        $cpuAfter = getrusage();
        $cpuRunTime = $this->rutime($cpuAfter, $state->cpuUsageBefore, 'utime');

        $this->removeWorkerProcess($state->worker, $state->pid);

        $script = <<<LUA
local hash = KEYS[1]
local jobType = ARGV[1]
local processingTimeMs = tonumber(ARGV[2])
local memoryUsage = tonumber(ARGV[3])
local cpuUsage = tonumber(ARGV[4])
local payloadSize = tonumber(ARGV[5])

local jobMetrics = redis.call('HGET', hash, jobType)
if not jobMetrics then
    jobMetrics = {
        count = 0,
        total_time = 0,
        total_memory = 0,
        total_cpu = 0,
        total_payload = 0,
        peak_time = 0,
        peak_memory = 0,
        peak_cpu = 0,
        peak_payload = 0,
    }
else
    jobMetrics = cjson.decode(jobMetrics)
end

local count = jobMetrics.count + 1
local total_time = jobMetrics.total_time + processingTimeMs
local total_memory = jobMetrics.total_memory + memoryUsage
local total_cpu = jobMetrics.total_cpu + cpuUsage
local total_payload = jobMetrics.total_payload + payloadSize
local peak_time = math.max(jobMetrics.peak_time, processingTimeMs)
local peak_memory = math.max(jobMetrics.peak_memory, memoryUsage)
local peak_cpu = math.max(jobMetrics.peak_cpu, cpuUsage)
local peak_payload = math.max(jobMetrics.peak_payload, payloadSize)

jobMetrics.count = count
jobMetrics.total_time = total_time
jobMetrics.total_memory = total_memory
jobMetrics.total_cpu = total_cpu
jobMetrics.total_payload = total_payload
jobMetrics.peak_time = peak_time
jobMetrics.peak_memory = peak_memory
jobMetrics.peak_cpu = peak_cpu
jobMetrics.peak_payload = peak_payload

redis.call('HSET', hash, jobType, cjson.encode(jobMetrics))
LUA;

        $this->redis->eval(
            $script,
            1,
            self::JOBS_METRICS_KEY,
            $state->currentJobType,
            $processingTimeMs,
            $memoryUsage,
            $cpuRunTime,
            $state->payloadSizeKb,
        );
    }

    public function removeAllWorkers(): void
    {
        $pipeline = $this->redis->pipeline();

        $workers = $this->getAllWorkers();
        foreach ($workers as $worker) {
            $pipeline->del(self::JOB_WORKER_PREFIX_KEY . $worker);
        }

        $pipeline->del(self::ALL_WORKER_PROCESSES_KEY);
        $pipeline->execute();
    }

    public function removeAllJobsMetrics(): void
    {
        $this->redis->del(self::JOBS_METRICS_KEY);
    }


    private function rutime(array $cpuAfter, array $cpuBefore, string $index): float
    {
        return ($cpuAfter["ru_$index.tv_sec"] * 1000 + intval($cpuAfter["ru_$index.tv_usec"] / 1000)) -
            ($cpuBefore["ru_$index.tv_sec"] * 1000 + intval($cpuBefore["ru_$index.tv_usec"] / 1000));
    }
}
