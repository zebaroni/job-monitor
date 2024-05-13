<?php

use JoseBaroni\JobMonitor\Http\JobMonitorController;

Route::group([
    'prefix' => 'job-monitor',
    'middleware' => config('jobmonitor.middleware')
], function () {
    Route::get('/', [JobMonitorController::class, 'index']);
    Route::get('/overview', [JobMonitorController::class, 'overview']);
});
