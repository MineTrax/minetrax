<?php

namespace App\Http\Controllers\Api;

use App\Jobs\RunCommandQueuesFromRequestJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        RunCommandQueuesFromRequestJob::dispatch($request->collect(), null);

        return $this->success(null, 'Command queued successfully.');
    }
}
