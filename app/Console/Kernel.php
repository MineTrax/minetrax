<?php

namespace App\Console;

use App\Console\Commands\ResetUserPasswordCommand;
use App\Jobs\CalculatePlayersJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ResetUserPasswordCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $playerFetcherInterval = config('minetrax.players_fetcher_cron_interval') ?? 'hourly';
        $schedule->job(new CalculatePlayersJob)->{$playerFetcherInterval}();

        $schedule->command('telescope:prune --hours=48')->daily();
        $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();
        $schedule->command('model:prune')->daily();
        $schedule->command('cache:prune-stale-tags')->hourly();

        $backupEnabled = config('backup.enabled');
        if ($backupEnabled) {
            $schedule->command('backup:clean')->daily()->at('01:00');
            $schedule->command('backup:run')->daily()->at('01:30');
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
