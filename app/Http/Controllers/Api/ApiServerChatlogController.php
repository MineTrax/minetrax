<?php

namespace App\Http\Controllers\Api;

use App\Events\ServerChatlogCreated;
use App\Http\Controllers\Controller;
use App\Models\ServerChatlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spirit55555\Minecraft\MinecraftColors;

class ApiServerChatlogController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'chat' => 'nullable',
            'causer_username' => 'sometimes|nullable|string',
            'causer_uuid' => 'sometimes|nullable|uuid',
            'server_id' => 'required|exists:servers,id',
            'channel' => 'sometimes|nullable|string',
            'type' => 'required|string|max:50',
        ]);

        $log = ServerChatlog::create([
            'server_id' => $request->server_id,
            'data' => $request->chat ?? null,
            'causer_username' => $request->causer_username,
            'causer_uuid' => $request->causer_uuid,
            'type' => $request->type,
            'channel' => $request->channel
        ]);

        if (!$request->channel && $request->chat) {
            broadcast(new ServerChatlogCreated($log));
        }

        /**
         * If type is of player-join or player-leave then clear the server player count related caches
         * This will ensure a fresh webquery in ServerController is done and player-list show latest data.
         */
        if ($request->type == 'player-leave' || $request->type == 'player-join') {
            Cache::forget('server:webquery:'.$request->server_id);
            Cache::forget('server:query:'.$request->server_id);
            Cache::forget('server:ping:'.$request->server_id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ok'
        ], 201);
    }
}
