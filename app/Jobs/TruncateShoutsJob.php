<?php

namespace App\Jobs;

use App\Models\Shout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cache;
use Illuminate\Support\Facades\Log;

class TruncateShoutsJob implements ShouldQueue
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
        Log::info('[TruncateShoutsJob] Starting job...');
        Cache::put('dangerzone::truncate_shouts', now(), 3600 * 24);

        Shout::truncate();

        Cache::forget('dangerzone::truncate_shouts');
        Log::info('[TruncateShoutsJob] Job completed successfully');
    }
}
