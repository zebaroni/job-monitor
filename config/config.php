<?php

use JoseBaroni\JobMonitor\Repositories\RedisMonitorRepository;

return [
    /*
    |--------------------------------------------------------------------------
    | JobMonitor Enabled
    |--------------------------------------------------------------------------
    |
    | Status of the monitoring. If enabled jobs and workers will be logged.
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the redis connection used by JobMonitor
    | to store workers metadata and jobs metrics.
    |
    */

    'redis_connection' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Middlewares
    |--------------------------------------------------------------------------
    |
    | Middlewares applied to JobMonitor routes (eg: dashboard view, auth)
    |
    */

    'middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Metadata Driver
    |--------------------------------------------------------------------------
    |
    | Implementation of MonitorRepository interface.
    | Driver responsible for storing metadata about jobs metrics and workers.
    |
    */

    'driver' => RedisMonitorRepository::class,
];
