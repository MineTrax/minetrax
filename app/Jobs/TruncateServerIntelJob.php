<?php

namespace App\Jobs;

use App\Models\MinecraftServerLiveInfo;
use App\Models\MinecraftServerWorld;
use App\Models\MinecraftWorldLiveInfo;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cache;

class TruncateServerIntelJob implements ShouldQueue
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
        Cache::put('dangerzone::truncate_server_intel_data', now(), 3600 * 24);

        DB::transaction(function () {
            // Delete server intel.
            MinecraftWorldLiveInfo::query()->delete();
            MinecraftServerWorld::query()->delete();
            MinecraftServerLiveInfo::query()->delete();
        }, 3);

        Cache::forget('dangerzone::truncate_server_intel_data');
    }
}
