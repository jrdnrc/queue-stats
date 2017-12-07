<?php declare(strict_types=1);

namespace JrdnRc\QueueStats\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

/**
 * Class GenerateSupervisorConfigCommand
 * @package JrdnRc\QueueStats\Console
 * @author Jordan Crocker <jordan@hotsnapper.com>
 */
final class GenerateSupervisorConfigCommand extends Command
{
    /** @var string */
    protected $signature = 'queue:supervisor {--user=} {--log-path=} {--directory=}';

    /** @var string */
    protected $description = 'Generate Supervisord config';

    /** @var Repository */
    private $config;

    /**
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function handle() : void
    {
        $config = $this->config->get('queue-stats');

        $queues = $config['queues'];
        $directory = $this->option('directory') !== null ? $this->option('directory') : $config['base_path'];

        foreach ($queues as $connection => $properties) {
            $this->output->writeln("[program:worker-{$connection}]");
            $this->output->writeln('process_name=%(program_name)s_%(process_num)02d');
            $this->output->writeln('directory=' . $directory);
            $this->output->writeln('command=php artisan queue:work --tries=1 --queue=' . implode(',', $properties['queue']));

            $this->output->writeln('autostart=true');
            $this->output->writeln('autorestart=true');
            $this->output->writeln('numprocs=' . $properties['processes']);

            $this->output->writeln('username=' . $this->option('user'));

            $this->output->writeln('redirect_stderr=true');
            $this->output->writeln('stdout_logfile=' . $this->logPath($connection));
            $this->output->writeln('');
        }
    }

    /**
     * @param string $filename
     * @return string
     * @throws \InvalidArgumentException
     */
    private function logPath(string $filename) : string
    {
        if (null === $this->option('log-path')) {
            return $this->normalizePath($this->config->get('queue-stats.storage_path') . DIRECTORY_SEPARATOR . $filename . '.log');
        }

        return $this->normalizePath($this->option('log-path')) . DIRECTORY_SEPARATOR . $filename . '.log';
    }

    /**
     * Taken from https://edmondscommerce.github.io/php/php-realpath-for-none-existant-paths.html
     * Replacing 'realpath' as that will return false if file/dir does not exist.
     *
     * @param string $path
     * @return string
     */
    private function normalizePath(string $path) : string
    {
        $reducer = static function ($a, $b) : string {
            if ($a === 0) {
                $a = '/';
            }

            if ($b === '' || $b === '.') {
                return $a;
            }

            if ($b === '..') {
                return dirname($a);
            }

            return preg_replace("/\/+/", '/', "$a/$b");
        };

        return array_reduce(explode('/', $path), $reducer, 0);
    }
}
