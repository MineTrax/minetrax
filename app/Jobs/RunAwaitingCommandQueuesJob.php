<?php

namespace App\Jobs;

use App\Enums\CommandQueueStatus;
use App\Models\CommandQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunAwaitingCommandQueuesJob implements ShouldQueue
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
        $failedCommands = CommandQueue::where('status', CommandQueueStatus::FAILED)
            ->whereNotNull('max_attempts')
            ->whereColumn('attempts', '<', 'max_attempts')
            ->where(function ($q) {
                $q->whereNull('execute_at')
                    ->orWhere('execute_at', '<=', now());
            })
            ->limit(50)
            ->get();
        foreach ($failedCommands as $command) {
            RunCommandQueueJob::dispatch($command);
        }

        $awaitingCommands = CommandQueue::where('status', CommandQueueStatus::PENDING)
            ->whereNotNull('execute_at')
            ->where('execute_at', '<=', now())
            ->limit(50)
            ->get();
        foreach ($awaitingCommands as $command) {
            RunCommandQueueJob::dispatch($command);
        }
    }
}
