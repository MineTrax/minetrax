<?php

namespace App\Http\Controllers\Api;

use App\Enums\CommandQueueStatus;
use App\Jobs\RunCommandQueuesFromRequestJob;
use App\Models\CommandQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApiCommandQueueController extends ApiController
{
    /**
     * Queue a command for execution using the command queue.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'scope' => 'required|string|in:global,player',
            'command' => 'required|string',
            'execute_at' => 'nullable|date|after:now',
            'servers' => 'nullable|array',
            'servers.*.id' => 'required|int|exists:servers,id',
            'players' => 'required_if:scope,player|array',
            'players.scope' => 'required_if:scope,player|in:all,linked,unlinked,custom',
            'players.is_player_online_required' => 'required_if:scope,player|boolean',
            'players.id' => [
                Rule::requiredIf(fn () => $request->input('scope') === 'player' && $request->input('players.scope') === 'custom'),
                'array',
            ],
        ]);

        $requestId = Str::uuid()->toString();
        $payload = $request->collect()->put('request_id', $requestId);

        Cache::put($this->requestCacheKey($requestId), true, now()->addDay());
        RunCommandQueuesFromRequestJob::dispatch($payload, null);

        return $this->success([
            'request_id' => $requestId,
        ], 'Command queued successfully.');
    }

    /**
     * Get the status of all command queues created by an API request.
     */
    public function show(string $requestId): JsonResponse
    {
        if (! Str::isUuid($requestId)) {
            return $this->error('Command queue request not found.', 'request_not_found', 404);
        }

        $commandQueues = CommandQueue::query()
            ->where('request_id', $requestId)
            ->get(['id', 'server_id', 'player_id', 'status', 'output']);

        if ($commandQueues->isEmpty()) {
            if (Cache::has($this->requestCacheKey($requestId))) {
                return $this->success([
                    'request_id' => $requestId,
                    'status' => CommandQueueStatus::PENDING->value,
                    'summary' => [],
                    'commands' => [],
                ], 'Ok');
            }

            return $this->error('Command queue request not found.', 'request_not_found', 404);
        }

        $statusCounts = $commandQueues
            ->countBy(fn (CommandQueue $commandQueue) => $commandQueue->status->value);

        $status = match (true) {
            $commandQueues->contains('status', CommandQueueStatus::RUNNING) => CommandQueueStatus::RUNNING->value,
            $commandQueues->contains(fn (CommandQueue $commandQueue) => in_array($commandQueue->status, [CommandQueueStatus::PENDING, CommandQueueStatus::DEFERRED], true)) => CommandQueueStatus::PENDING->value,
            $commandQueues->contains(fn (CommandQueue $commandQueue) => in_array($commandQueue->status, [CommandQueueStatus::FAILED, CommandQueueStatus::CANCELLED], true)) => CommandQueueStatus::FAILED->value,
            default => CommandQueueStatus::COMPLETED->value,
        };

        return $this->success([
            'request_id' => $requestId,
            'status' => $status,
            'summary' => $statusCounts,
            'commands' => $commandQueues,
        ], 'Ok');
    }

    private function requestCacheKey(string $requestId): string
    {
        return "command-queue-request:{$requestId}";
    }
}
