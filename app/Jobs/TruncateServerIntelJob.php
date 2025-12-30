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
use Illuminate\Support\Facades\Log;

class TruncateServerIntelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('longtask');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('[TruncateServerIntelJob] Starting job...');
        Cache::put('dangerzone::truncate_server_intel_data', now(), 3600 * 24);

        // Delete server intel.
        MinecraftWorldLiveInfo::query()->delete();
        MinecraftServerWorld::query()->delete();
        MinecraftServerLiveInfo::query()->delete();

        Cache::forget('dangerzone::truncate_server_intel_data');
        Log::info('[TruncateServerIntelJob] Job completed successfully');
    }
}
