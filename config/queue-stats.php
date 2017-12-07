<?php declare(strict_types=1);

return [
    /**
     * Redis connection to use
     */
    'redis_connection' => 'redis',

    /**
     * The base installation path of your Laravel application
     * This tells supervisor where to look
     */
    'base_path' => base_path(),

    /**
     * Where to store logs, if no --log-path argument is provided
     * With the default storage path, they will be stored in
     * storage/logs/{worker}.log
     */
    'storage_path' => storage_path('logs'),

    /**
     * Define your workers here, including the redis connection,
     * the queues they are running, and how many
     * processes each worker should run
     */
    'queues' => [
        'default' => [
            'connection' => 'redis',
            'queue'      => ['default'],
            'processes'  => 1,
        ],
    ],
];