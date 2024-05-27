<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommandQueue;
use App\Queries\Filters\FilterMultipleFields;
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
}
