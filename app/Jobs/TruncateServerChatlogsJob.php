<?php

namespace App\Jobs;

use App\Models\ServerChatlog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cache;
use Illuminate\Support\Facades\Log;

class TruncateServerChatlogsJob implements ShouldQueue
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
        Log::info('[TruncateServerChatlogsJob] Starting job...');
        Cache::put('dangerzone::truncate_chatlogs', now(), 3600 * 24);

        ServerChatlog::truncate();

        Cache::forget('dangerzone::truncate_chatlogs');
        Log::info('[TruncateServerChatlogsJob] Job completed successfully');
    }
}
