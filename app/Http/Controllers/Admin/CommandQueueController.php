<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommandQueueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCommandQueueRequest;
use App\Jobs\RunCommandQueueJob;
use App\Models\CommandQueue;
use App\Models\Player;
use App\Models\Server;
use App\Queries\Filters\FilterMultipleFields;
use App\Utils\Helpers\Helper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CommandQueueController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CommandQueue::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'command_id',
            'server_id',
            'parsed_command',
            'config',
            'status',
            'execute_at',
            'max_attempts',
            'attempts',
            'last_attempt_at',
            'output',
            'tag',
            'player_uuid',
            'user_id',
            'player_id',
            'created_at',
            'updated_at',
        ];

        $commandQueues = QueryBuilder::for(CommandQueue::class)
            ->with(['server:id,name,hostname', 'player:id,uuid,username'])
            ->allowedFilters([
                ...$fields,
                'server.name',
                'player.username',
                AllowedFilter::custom('q', new FilterMultipleFields([
                    'id',
                    'parsed_command',
                    'status',
                    'output',
                    'tag',
                    'player_uuid',
                ])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-updated_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/CommandQueue/IndexCommandQueue', [
            'commandQueues' => $commandQueues,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', CommandQueue::class);

        $servers = Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->get();
        $players = Player::select(['id', 'uuid', 'username'])->get();

        return Inertia::render('Admin/CommandQueue/CreateCommandQueue', [
            'servers' => $servers,
            'players' => $players,
        ]);
    }

    public function store(CreateCommandQueueRequest $request)
    {
        // Select servers.
        $servers = [];
        if (empty($request->servers)) {
            $servers = Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->get();
        } else {
            $serverIds = collect($request->servers)->pluck('id');
            $servers = Server::select(['id', 'name', 'hostname'])
                ->whereIn('id', $serverIds)
                ->whereNotNull('webquery_port')
                ->get();
        }

        $commandQueueList = collect();
        foreach ($servers as $server) {
            if ($request->input('scope') == 'global') {
                $created = $this->createCommandQueue($request->input('command'), null, null, $server->id, $request->user()->id);
                $commandQueueList->push($created);
            } else {
                $playerScope = $request->input('players.scope');

                switch ($playerScope) {
                    case 'all':
                        $players = Player::select(['id', 'uuid', 'username'])->get();
                        break;
                    case 'linked':
                        $players = Player::whereHas('users')->select(['id', 'uuid', 'username'])->get();
                        break;
                    case 'unlinked':
                        $players = Player::whereDoesntHave('users')->select(['id', 'uuid', 'username'])->get();
                        break;
                    case 'custom':
                        $playerIds = collect($request->input('players.id'))->pluck('id');
                        $players = Player::select(['id', 'uuid', 'username'])->whereIn('id', $playerIds)->get();
                        break;
                }

                // Create command for each player.
                foreach ($players as $player) {
                    $config = [
                        'is_player_online_required' => $request->input('players.is_player_online_required'),
                    ];
                    $created = $this->createCommandQueue($request->input('command'), $config, $player, $server->id, $request->user()->id);
                    $commandQueueList->push($created);
                }
            }
        }

        // Dispatch jobs.
        $ranCount = 0;
        foreach ($commandQueueList as $commandQueue) {
            RunCommandQueueJob::dispatch($commandQueue);
            $ranCount++;
        }

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __(':count Commands Scheduled!', ['count' => $ranCount]), 'body' => __('Commands has been scheduled for execution. Check the status in Command History.'), 'milliseconds' => 5000]]);
    }

    private function createCommandQueue($rawCommand, $config, $player, $serverId, $userId)
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
            'tag' => 'run_command',
        ]);

        return $commandQueue;
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
        ]);

        $commandQueue = CommandQueue::findOrFail($request->id);
        $this->authorize('delete', $commandQueue);

        $commandQueue->delete();

        return redirect()->route('admin.command-queue.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted!')]]);
    }

    public function retry(Request $request)
    {
        $request->validate([
            'id' => 'sometimes|required|int',
        ]);

        if ($request->id) {
            $commandQueues = CommandQueue::where('id', $request->id)->get();
        } else {
            $commandQueues = CommandQueue::whereIn('status', [
                CommandQueueStatus::FAILED,
                CommandQueueStatus::CANCELLED,
            ])->get();
        }

        foreach ($commandQueues as $commandQueue) {
            if ($request->user()->cannot('retry', $commandQueue)) {
                continue;
            }
            $commandQueue->status = 'pending';
            $commandQueue->save();
            RunCommandQueueJob::dispatch($commandQueue);
        }

        return redirect()->route('admin.command-queue.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Retried!'), 'body' => __('Command has been queued for retrying! Refresh the page after few seconds to check the status.')]]);
    }
}
