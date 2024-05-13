<?php

namespace JoseBaroni\JobMonitor\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use JoseBaroni\JobMonitor\Repositories\MonitorRepository;

class JobMonitorController extends Controller
{
    private MonitorRepository $monitorRepository;

    public function __construct(MonitorRepository $monitorRepository)
    {
        $this->monitorRepository = $monitorRepository;
    }

    public function index(): View
    {
        return view('jobmonitor::index');
    }

    public function overview(): JsonResponse
    {
        $workers = $this->monitorRepository->getAllWorkers();

        $workersResponse = [];
        foreach ($workers as $worker) {
            $workersResponse[$worker] = [];
            $processes = $this->monitorRepository->getWorkerProcesses($worker);

            foreach ($processes as $process) {
                $workersResponse[$worker][] = [
                    'pid' => $process->getPid(),
                    'job_type' => $process->getJobType(),
                    'job_id' => $process->getJobId(),
                    'time_elapsed_ms' => $process->getElapsedTimeMs()
                ];
            }
        }

        $metricsResponse = [];
        $jobsMetrics = $this->monitorRepository->getAllJobMetrics();

        foreach ($jobsMetrics as $jobMetric) {
            $metricsResponse[] = [
                'job_type' => $jobMetric->getJobType(),
                'total_jobs_processed' => $jobMetric->getCount(),
                'time_peak_ms' => $jobMetric->getPeakTimeMs(),
                'time_avg_ms' => $jobMetric->getAvgTimeMs(),
                'memory_peak_mb' => $jobMetric->getPeakMemoryBytes() / 1024 / 1024,
                'memory_avg_mb' => $jobMetric->getAvgMemoryUsageBytes() / 1024 / 1024,
                'cpu_avg_ms' => $jobMetric->getAvgCpuMs(),
                'cpu_peak_ms' => $jobMetric->getPeakCpuMs(),
                'payload_avg_kb' => $jobMetric->getAvgPayloadKb(),
                'payload_peak_kb' => $jobMetric->getPeakPayloadKb(),
            ];
        }

        return response()->json([
            'metrics' => $metricsResponse,
            'workers' => $workersResponse,
        ]);
    }
}
