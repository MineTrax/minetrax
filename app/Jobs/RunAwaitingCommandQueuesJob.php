<?php

namespace App\Jobs;

use App\Enums\CommandQueueStatus;
use App\Models\CommandQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        // FAILED
        $failedCommands = CommandQueue::where('status', CommandQueueStatus::FAILED)
            ->whereNotNull('max_attempts')
            ->whereColumn('attempts', '<', 'max_attempts')
            ->where(function ($q) {
                $q->whereNull('execute_at')
                    ->orWhere('execute_at', '<=', now());
            })
            ->limit(50)
            ->get();

        if ($failedCommands->count() > 0) {
            Log::info('[RunAwaitingCommandQueuesJob] Running Failed commands: ' . $failedCommands->count());
            foreach ($failedCommands as $command) {
                RunCommandQueueJob::dispatch($command);
            }
        }

        // DELAYED
        $awaitingCommands = CommandQueue::where('status', CommandQueueStatus::PENDING)
            ->whereNotNull('execute_at')
            ->where('execute_at', '<=', now())
            ->limit(50)
            ->get();

        if ($awaitingCommands->count() > 0) {
            Log::info('[RunAwaitingCommandQueuesJob] Running Delayed commands: ' . $awaitingCommands->count());
            foreach ($awaitingCommands as $command) {
                RunCommandQueueJob::dispatch($command);
            }
        }
    }
}
