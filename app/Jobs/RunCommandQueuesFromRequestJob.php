<?php

namespace App\Jobs;

use App\Enums\CommandQueueStatus;
use App\Models\CommandQueue;
use App\Models\Player;
use App\Models\Server;
use App\Utils\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RunCommandQueuesFromRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Collection $request, private int $userId)
    {
        $this->onQueue('longtask');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $servers = [];
        if (empty($this->request->get('servers'))) {
            $servers = Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->get();
        } else {
            $serverIds = collect($this->request->get('servers'))->pluck('id');
            $servers = Server::select(['id', 'name', 'hostname'])
                ->whereIn('id', $serverIds)
                ->whereNotNull('webquery_port')
                ->get();
        }

        $totalCounter = 0;
        $executeAt = $this->request->get('execute_at') ?? null;
        if ($this->request->get('scope') == 'global') {
            foreach ($servers as $server) {
                $totalCounter++;
                $created = $this->createCommandQueue($this->request->get('command'), $executeAt, null, null, $server->id, $this->userId);
                if ($executeAt == null) {
                    RunCommandQueueJob::dispatch($created);
                }
            }
        } else {
            $playerScope = $this->request->get('players')['scope'];

            switch ($playerScope) {
                case 'all':
                    $players = Player::select(['id', 'uuid', 'username'])->lazy();
                    break;
                case 'linked':
                    $players = Player::whereHas('users')->select(['id', 'uuid', 'username'])->lazy();
                    break;
                case 'unlinked':
                    $players = Player::whereDoesntHave('users')->select(['id', 'uuid', 'username'])->lazy();
                    break;
                case 'custom':
                    $playerIds = collect($this->request->get('players')['id'])->pluck('id');
                    $players = Player::select(['id', 'uuid', 'username'])->whereIn('id', $playerIds)->lazy();
                    break;
            }
            // Create command for each player.

            $commandString = $this->request->get('command');
            foreach ($players as $player) {
                foreach ($servers as $server) {
                    $totalCounter++;
                    $config = [
                        'is_player_online_required' => $this->request->get('players')['is_player_online_required'],
                    ];
                    // Dispatch jobs.
                    $created = $this->createCommandQueue($commandString, $executeAt, $config, $player, $server->id, $this->userId);
                    if ($executeAt == null) {
                        RunCommandQueueJob::dispatch($created);
                    }
                }
            }
        }
        Log::info('[RunCommandQueuesFromRequestJob] Created '.$totalCounter.' command queues.');
    }

    private function createCommandQueue($rawCommand, $executeAt, $config, $player, $serverId, $userId)
    {
        $params = [];
        if ($player) {
            $params = [
                'player_uuid' => $player->uuid,
                'player_username' => $player->username,
            ];
        }
        $parsedCommandString = Helper::replacePlaceholders($rawCommand, $params);
        $commandQueue = CommandQueue::create([
            'server_id' => $serverId,
            'command_id' => null,
            'parsed_command' => $parsedCommandString,
            'config' => $config,
            'params' => $params,
            'status' => CommandQueueStatus::PENDING,
            'max_attempts' => 1,
            'player_uuid' => $player ? $player->uuid : null,
            'user_id' => $userId,
            'player_id' => $player ? $player->id : null,
            'execute_at' => $executeAt,
            'tag' => 'run_command',
        ]);

        return $commandQueue;
    }
}
