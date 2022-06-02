<?php

namespace App\Jobs;

use App\Enums\ServerType;
use App\Models\Server;
use Bus;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class FetchStatsFromAllServersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        // We scan all server except bungee
        $servers = Server::where('type', '<>', ServerType::Bungee)
            ->where('is_stats_tracking_enabled', true)
            ->get();

        $jobChain = collect();
        foreach ($servers as $server)
        {
            $jobChain->push(new FetchStatsFromOneServerJob($server));
        }

        $batch = Bus::batch(
            $jobChain
        )->catch(function (Batch $batch, Throwable $e) {
            Log::error("Some Error in Server Jobs!");
        })->finally(function (Batch $batch) {
            Bus::chain([
                new CalculatePlayersJob(),
                new CalculatePlayersRatingJob()
            ])->onConnection('redis-longtask')->dispatch();
        })->onQueue('longtask')->onConnection('redis-longtask')->allowFailures()->dispatch();
    }
}
