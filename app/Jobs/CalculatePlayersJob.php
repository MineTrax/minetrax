<?php

namespace App\Jobs;

use Bus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class CalculatePlayersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('longtask');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Bus::chain([
            new CalculatePlayersScoreJob(),
            new CalculatePlayersRatingJob()
        ])
            ->catch(function (Throwable $e) {
                // A job within the chain has failed...
                Log::error("[Error] CalculatePlayersJob: " . $e->getMessage());
            })
            ->onQueue('longtask')->onConnection('redis-longtask')->dispatch();
    }
}
