<?php

namespace App\Jobs;

use App\Enums\CommandQueueStatus;
use App\Models\CommandQueue;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunCommandQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private CommandQueue $commandQueue)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! in_array($this->commandQueue->status->value, [
            CommandQueueStatus::PENDING,
            CommandQueueStatus::FAILED,
            CommandQueueStatus::DEFERRED,
        ])) {
            Log::info('SKIPPED! CommandQueue '.$this->commandQueue->id.' is not in pending, failed or deferred status');

            return;
        }

        // change status to running so that it won't be picked up by another worker.
        $this->commandQueue->update([
            'status' => CommandQueueStatus::RUNNING,
        ]);

        // load missing relations
        $this->commandQueue->loadMissing([
            'server',
            'command',
        ]);
        $isPlayerOnlineRequired = $this->commandQueue->config['is_player_online_required'];
        $server = $this->commandQueue->server;
        if (! $server->webquery_port) {
            $this->commandQueue->update([
                'status' => CommandQueueStatus::CANCELLED,
                'output' => 'Server does not have webquery port',
            ]);
            // Log::info('CANCELLED! CommandQueue '.$this->commandQueue->id.' is cancelled because server does not have webquery port');

            return;
        }

        try {
            $webQuery = new MinecraftWebQuery($server->ip_address, $server->webquery_port);
            if ($isPlayerOnlineRequired) {
                $playerUuid = $this->commandQueue->player_uuid;
                $isOnline = $webQuery->checkPlayerOnline($playerUuid);

                if (! $isOnline) {
                    $this->commandQueue->update([
                        'status' => CommandQueueStatus::DEFERRED,
                        'output' => 'Player not online',
                    ]);
                    // Log::info('DEFERRED! CommandQueue '.$this->commandQueue->id.' is deferred because player is not online');

                    return;
                }
            }

            $status = $webQuery->runCommand($this->commandQueue->parsed_command);
            $this->commandQueue->update([
                'status' => CommandQueueStatus::COMPLETED,
                'output' => $status,
                'last_attempt_at' => now(),
                'attempts' => $this->commandQueue->attempts + 1,
            ]);
            // Log::info('COMPLETED! CommandQueue '.$this->commandQueue->id.' is completed');
        } catch (\Exception $e) {
            $this->commandQueue->update([
                'status' => CommandQueueStatus::FAILED,
                'output' => $e->getMessage(),
                'last_attempt_at' => now(),
                'attempts' => $this->commandQueue->attempts + 1,
            ]);
            // Log::error('OOPS! CommandQueue '.$this->commandQueue->id.' failed: '.$e->getMessage());
        }
    }
}
