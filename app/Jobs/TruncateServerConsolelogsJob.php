<?php

namespace App\Jobs;

use App\Models\ServerConsolelog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cache;

class TruncateServerConsolelogsJob implements ShouldQueue
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
        Cache::put('dangerzone::truncate_consolelogs', now(), 3600 * 24);

        ServerConsolelog::truncate();

        Cache::forget('dangerzone::truncate_consolelogs');
    }
}
