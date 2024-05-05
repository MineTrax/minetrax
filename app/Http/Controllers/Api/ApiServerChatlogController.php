<?php

namespace App\Http\Controllers\Api;

use App\Events\ServerChatlogCreated;
use App\Models\ServerChatlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiServerChatlogController extends ApiController
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'data.chat' => 'nullable',
            'data.causer_username' => 'sometimes|nullable|string',
            'data.causer_uuid' => 'sometimes|nullable|uuid',
            'data.server_id' => 'required|exists:servers,id',
            'data.channel' => 'sometimes|nullable|string',
            'data.type' => 'required|string|max:50',
        ]);

        $channel = $request->input('data.channel');
        $type = $request->input('data.type');
        $serverId = $request->input('data.server_id');
        $log = ServerChatlog::create([
            'server_id' => $request->input('data.server_id'),
            'data' => $request->input('data.chat') ?? null,
            'causer_username' => $request->input('data.causer_username') ?? null,
            'causer_uuid' => $request->input('data.causer_uuid') ?? null,
            'type' => $type,
            'channel' => $channel,
        ]);

        if (! $channel && $request->input('data.chat')) {
            broadcast(new ServerChatlogCreated($log));
        }

        /**
         * If type is of player-join or player-leave then clear the server player count related caches
         * This will ensure a fresh webquery in ServerController is done and player-list show latest data.
         */
        if ($type == 'player-leave' || $type == 'player-join') {
            Cache::forget('server:webquery:'.$serverId);
            Cache::forget('server:query:'.$serverId);
            Cache::forget('server:ping:'.$serverId);
        }

        return $this->success(null, 'Ok', 200);
    }
}
