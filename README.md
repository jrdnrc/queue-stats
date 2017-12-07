# Queue Stats

Adds commands to laravel to output information about running queue workers.

### Configuration
##### Service Provider:  
If you don't have auto discovery, register this provider in `config/app.php`:  
`\JrdnRc\QueueStats\QueueStatsServiceProvider::class`  
##### Configuration File
`$ php artisan vendor:publish --provider="JrdnRc\\QueueStats\\QueueStatsServiceProvider"`

```php
<?php
    return [
        // ...
    
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
            
            'media-processing' => [
                'connection' => 'redis2',
                'queue'      => ['image-transforming', 'video-transforming'],
                'processes'  => 5,
            ],
        ],
    ];
```
Output:
```
$ php artisan queue:stats 
+--------------------+----------+-----------------------+---------+-----------+
| Queue              | In Queue | Reserved (Processing) | Delayed | Processes |
+--------------------+----------+-----------------------+---------+-----------+
| default            | 20       | 1                     | 0       | 1         |
| image-transforming | 0        | 0                     | 2       | 5         |
| video-transforming | 1        | 2                     | 0       | 5         |
+--------------------+----------+-----------------------+---------+-----------+
| Total:             | 21       | 0                     | 2       | 11        |
+--------------------+----------+-----------------------+---------+-----------+

```

### Supervisor Config
This package can also generate supervisor config for your queue workers.

* User is required
* Log path and directory can be defined in config file

```
$ php artisan queue:supervisor --user=forge --log-path=/var/app/storage/logs --directory=/var/app 
[program:worker-default]
process_name=%(program_name)s_%(process_num)02d
directory=/var/app
command=php artisan queue:work --tries=1 --queue=default
autostart=true
autorestart=true
numprocs=1
username=forge
redirect_stderr=true
stdout_logfile=/var/app/storage/logs/default.log

[program:worker-media-processing]
process_name=%(program_name)s_%(process_num)02d
directory=/var/app
command=php artisan queue:work --tries=1 --queue=image-transforming,video-transforming
autostart=true
autorestart=true
numprocs=5
username=forge
redirect_stderr=true
stdout_logfile=/var/app/storage/logs/media-processing.log

```