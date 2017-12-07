<?php declare(strict_types=1);

namespace JrdnRc\QueueStats;

use Illuminate\Support\ServiceProvider;
use JrdnRc\QueueStats\Console\GenerateSupervisorConfigCommand;
use JrdnRc\QueueStats\Console\QueueStatsCommand;

/**
 * Class QueueStatsServiceProvider
 * @package JrdnRc\QueueStats
 * @author Jordan Crocker <jordan@hotsnapper.com>
 */
final class QueueStatsServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot() : void
    {
        $this->publishes([$this->configPath() => config_path('queue-stats.php')]);

        $this->commands([
            QueueStatsCommand::class,
            GenerateSupervisorConfigCommand::class,
        ]);
    }

    /**
     * @return string/
     */
    protected function configPath() : string
    {
        return __DIR__ . '/../config/queue-stats.php';
    }
}
