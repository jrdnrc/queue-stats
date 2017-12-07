<?php declare(strict_types=1);

namespace JrdnRc\QueueStats\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Class QueueStatsCommand
 * @package JrdnRc\QueueStats\Console
 * @author Jordan Crocker <jordan@hotsnapper.com>
 */
final class QueueStatsCommand extends Command
{
    /** @var string */
    protected $signature = 'queue:stats';

    /** @var string */
    protected $description = 'Statistics for your Queue Workers';

    /** @var Repository */
    private $config;
    /** @var Connection */
    private $redis;

    /**
     * @param Repository $config
     * @param RedisManager $redis
     */
    public function __construct(Repository $config, RedisManager $redis)
    {
        parent::__construct();

        $this->config = $config;
        $this->redis = $redis->connection($this->getRedisConnection());
    }

    /**
     * @return void
     */
    public function handle() : void
    {
        $queues = $this->config->get('queue-stats.queues');

        $key = function (string $queue, string $type = null) : string {
            return "queues:{$queue}" . ($type ? ":{$type}" : '');
        };

        $count = function (string $key, string $operation = 'zcard') : int {
            return $this->redis->$operation($key);
        };

        $headers = ['Queue', 'In Queue', 'Reserved (Processing)', 'Delayed', 'Processes'];
        $data    = [];

        foreach ($queues as $connection => $properties) {
            foreach ($properties['queue'] as $queue) {
                $data[] = [
                    'name'      => $queue,
                    'in_queue'  => $count($key($queue), 'llen'),
                    'reserved'  => $count($key($queue, 'reserved')),
                    'delayed'   => $count($key($queue, 'delayed')),
                    'processes' => $properties['processes'],
                ];
            }
        }

        $data[] = new TableSeparator;

        $data[] = [
            'Total:',
            collect($data)->sum('in_queue'),
            collect($data)->sum('reserved'),
            collect($data)->sum('delayed'),
            collect($data)->sum('processes'),
        ];

        (new Table($this->output))->setHeaders($headers)->setRows($data)->render();
    }

    /**
     * @return string
     */
    private function getRedisConnection() : string
    {
        $connections = array_except($this->config->get('database.redis'), ['client']);

        $defaults = array_keys($connections);

        $connection = $this->config->get('queue-stats.redis_connection');

        if (false === in_array($connection, $defaults, true)) {
            return $defaults[0];
        }

        return $connection;
    }
}
