<?php

declare(strict_types=1);

use Larafony\Framework\Config\Environment\EnvReader;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default queue driver that will be used by the
    | framework. Supported drivers: "database", "redis"
    |
    */
    'default' => EnvReader::read('QUEUE_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Throttling
    |--------------------------------------------------------------------------
    |
    | Controls the rate at which emails are sent per hour to avoid being
    | flagged as spam by email providers.
    |
    */
    'throttle_per_hour' => (int) EnvReader::read('QUEUE_THROTTLE_EMAILS_PER_HOUR', '100'),

    /*
    |--------------------------------------------------------------------------
    | Inter-message Delay
    |--------------------------------------------------------------------------
    |
    | Fixed delay in seconds between consecutive email sends within the queue
    | worker. When set to 0, the system falls back to the hourly throttle and
    | derives a minimum spacing automatically.
    |
    */
    'send_delay_seconds' => (float) EnvReader::read('QUEUE_SEND_DELAY_SECONDS', '0'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each queue driver
    | that is used by your application.
    |
    */
    'connections' => [
        'database' => [
            'driver' => 'database',
            'table' => 'queue_jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'retry_after' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | will be able to control which table and connection are used to store
    | the jobs that have failed.
    |
    */
    'failed' => [
        'driver' => 'database',
        'table' => 'failed_jobs',
    ],
];
