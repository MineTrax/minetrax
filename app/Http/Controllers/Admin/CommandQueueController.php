<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommandQueueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCommandQueueRequest;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\RunCommandQueuesFromRequestJob;
use App\Models\CommandQueue;
use App\Models\Player;
use App\Models\Server;
use App\Queries\Filters\FilterMultipleFields;
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
            ->with(['server:id,name,hostname', 'player:id,uuid,username,skin_texture_id', 'user:id,name,username,profile_photo_path'])
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
                    'player.username',
                    'user.username',
                    'user.name',
                ])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-created_at')
            ->simplePaginate($perPage)
            ->through(function ($commandQueue) {
                if ($commandQueue->tag === 'player_password_reset') {
                    $commandQueue->parsed_command = array_key_exists('viewable_parsed_command', $commandQueue->config)
                        ? $commandQueue->config['viewable_parsed_command']
                        : $commandQueue->parsed_command;
                }
                return $commandQueue;
            })
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
        RunCommandQueuesFromRequestJob::dispatch($request->collect(), $request->user()->id);

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Commands Scheduled!'), 'body' => __('Commands has been scheduled for execution. Check the status in Command History.'), 'milliseconds' => 5000]]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
        ]);

        $commandQueue = CommandQueue::findOrFail($request->id);
        $this->authorize('delete', $commandQueue);

        $commandQueue->delete();

        return redirect()->back()
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

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Retried!'), 'body' => __('Command has been queued for retrying! Refresh the page after few seconds to check the status.')]]);
    }
}
