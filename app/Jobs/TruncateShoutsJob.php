<?php

namespace App\Jobs;

use App\Models\Shout;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TruncateShoutsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public ?string $beforeDate = null)
    {
        $this->onQueue('longtask');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('[TruncateShoutsJob] Starting job...', ['before_date' => $this->beforeDate]);
        Cache::put('dangerzone::truncate_shouts', now(), 3600 * 24);

        if ($this->beforeDate) {
            Shout::where('created_at', '<', $this->beforeDate)->delete();
        } else {
            Shout::truncate();
        }

        Cache::forget('dangerzone::truncate_shouts');
        Log::info('[TruncateShoutsJob] Job completed successfully');
    }
}
