<?php

namespace App\Jobs;

use App\Enums\CommandQueueStatus;
use App\Models\CommandQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDeferredPlayerCommandQueuesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $playerUuid)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $deferredJobs = CommandQueue::with(['command', 'server'])
            ->where('status', CommandQueueStatus::DEFERRED)
            ->where('player_uuid', $this->playerUuid)
            ->get();

        foreach ($deferredJobs as $deferredJob) {
            RunCommandQueueJob::dispatch($deferredJob);
        }
    }
}
