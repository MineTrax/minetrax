<?php

namespace App\Jobs;

use App\Models\JsonMinecraftPlayerAdvancement;
use App\Models\JsonMinecraftPlayerStat;
use App\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;

class TruncatePlayerStatsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function() {
            JsonMinecraftPlayerAdvancement::query()->delete();
            JsonMinecraftPlayerStat::query()->delete();
            Player::query()->delete();
        });
    }
}
