<?php

namespace JoseBaroni\JobMonitor\Console\Commands;

use Illuminate\Console\Command;

class JobMonitorInstallCommand extends Command
{
    protected $signature = 'jobmonitor:install';

    protected $description = 'Installs the package';


    public function handle(): void
    {
        $this->callSilent('vendor:publish', ['--tag' => 'jobmonitor-config']);
        $this->info("Package installed successfully. Please see config/jobmonitor.php for configs.");
    }
}
