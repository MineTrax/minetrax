<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MinecraftPlayerDeath;
use App\Models\MinecraftPlayerEvent;
use App\Models\MinecraftPlayerPvpKill;
use App\Models\MinecraftPlayerSession;
use App\Models\MinecraftServerWorld;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApiMinecraftPlayerIntelController extends Controller
{
    public function postSessionInit(Request $request, GeolocationService $geolocationService)
    {
        $request->validate([
            'server_id' => 'required|numeric|exists:servers,id',
            'session_uuid' => 'required|uuid',
            'uuid' => 'required|uuid',
            'player_id' => 'sometimes|nullable|numeric',
            'username' => 'required|string',
            'ip_address' => 'required|string',
            'display_name' => 'required|string',
            'session_started_at' => 'required|numeric',
            'is_op' => 'required|boolean'
        ]);

        try {
            $playerCountryId = $geolocationService->getCountryIdFromIP($request->ip_address);
            $carbonDate = Carbon::createFromTimestampMs($request->session_started_at);
            // Start the session
            $newSession = MinecraftPlayerSession::create([
                'uuid' => $request->session_uuid,
                'server_id' => $request->server_id,
                'player_uuid' => $request->uuid,
                'player_username' => $request->username,
                'player_displayname' => $request->display_name,
                'session_started_at' => $carbonDate,
                'player_ip_address' => $request->ip_address,
                'country_id' => $playerCountryId,
                'is_op' => $request->is_op,
            ]);

            return response()->json($newSession, 201);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start player session.',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }

    public function postReportPvpKill(Request $request)
    {
        $request->validate([
            'session_uuid' => 'required|uuid|exists:minecraft_player_sessions,uuid',
            'killer_uuid' => 'required|uuid',
            'killer_username' => 'required|string',
            'victim_uuid' => 'required|uuid',
            'victim_username' => 'required|string',
            'weapon' => 'present|nullable|string',
            'killed_at' => 'required|numeric',
            'world_name' => 'present|nullable|string',
            'world_location' => 'present|nullable|string',
        ]);

        $minecraftPlayerSession = MinecraftPlayerSession::where('uuid', $request->session_uuid)->firstOrFail();
        try {
            $minecraftServerWorld = MinecraftServerWorld::where('server_id', $minecraftPlayerSession->server_id)->where('world_name', $request->world_name)->first();
            $killedAt = Carbon::createFromTimestampMs($request->killed_at);

            $minecraftPlayerPvpKill = MinecraftPlayerPvpKill::create([
                'session_id' => $minecraftPlayerSession->id,
                'killer_uuid' => $request->killer_uuid,
                'killer_username' => $request->killer_username,
                'victim_uuid' => $request->victim_uuid,
                'victim_username' => $request->victim_username,
                'killed_at' => $killedAt,
                'weapon' => $request->weapon,
                'world_location' => $request->world_location,
                'minecraft_server_world_id' => $minecraftServerWorld?->id,
            ]);

            return response()->json($minecraftPlayerPvpKill, 201);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed reporting player pvp kill.',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }

    public function postReportDeath(Request $request)
    {
        $request->validate([
            'session_uuid' => 'required|uuid|exists:minecraft_player_sessions,uuid',
            'player_uuid' => 'required|uuid',
            'player_username' => 'required|string',
            'cause' => 'present|nullable|string',
            'killer_uuid' => 'present|nullable|uuid',
            'killer_username' => 'present|nullable|string',
            'killer_entity_id' => 'present|nullable|string',
            'killer_entity_name' => 'present|nullable|string',
            'died_at' => 'required|numeric',
            'world_name' => 'present|nullable|string',
            'world_location' => 'present|nullable|string',
        ]);

        $minecraftPlayerSession = MinecraftPlayerSession::where('uuid', $request->session_uuid)->firstOrFail();
        try {
            $minecraftServerWorld = MinecraftServerWorld::where('server_id', $minecraftPlayerSession->server_id)->where('world_name', $request->world_name)->first();
            $diedAt = Carbon::createFromTimestampMs($request->died_at);

            $minecraftPlayerDeath = MinecraftPlayerDeath::create([
                'session_id' => $minecraftPlayerSession->id,
                'player_uuid' => $request->player_uuid,
                'player_username' => $request->player_username,
                'cause' => $request->cause,
                'killer_uuid' => $request->killer_uuid,
                'killer_username' => $request->killer_username,
                'killer_entity_id' => $request->killer_entity_id,
                'killer_entity_name' => $request->killer_entity_name,
                'died_at' => $diedAt,
                'world_location' => $request->world_location,
                'minecraft_server_world_id' => $minecraftServerWorld?->id,
            ]);

            return response()->json($minecraftPlayerDeath, 201);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed reporting player pvp kill.',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }

    public function postReportEvent(Request $request, GeolocationService $geolocationService)
    {
        $request->validate([
            "session_uuid" => "required|uuid|exists:minecraft_player_sessions,uuid",
            "uuid" => "required|uuid",
            "username" => "required|string",
            "server_id" => "required|exists:servers,id",
            "ip_address" => "required|string",
            "display_name" => "nullable|string",
            "is_op" => "required|boolean",
            "session_started_at" => "required",
            "session_ended_at" => "required|nullable",
            "mob_kills" => "required|numeric",
            "player_kills" => "required|numeric",
            "deaths" => "required|numeric",
            "afk_time" => "required|numeric",
            "is_kicked" => "required|boolean",
            "is_banned" => "required|boolean",

            "player_ping" => "nullable|numeric",
            "mob_kills_xmin" => "required|numeric",
            "player_kills_xmin" => "required|numeric",
            "deaths_xmin" => "required|numeric",
            "items_used_xmin" => "required|numeric",
            "items_mined_xmin" => "required|numeric",
            "items_picked_up_xmin" => "required|numeric",
            "items_dropped_xmin" => "required|numeric",
            "items_broken_xmin" => "required|numeric",
            "items_crafted_xmin" => "required|numeric",
            "items_placed_xmin" => "required|numeric",
            "items_consumed_xmin" => "required|numeric",
            "afk_time_xmin" => "required|numeric",
            "world_location" => 'sometimes|nullable|json',
            "world_name" => 'sometimes|nullable|string'
        ]);

        $minecraftPlayerSession = MinecraftPlayerSession::where('uuid', $request->session_uuid)->firstOrFail();
        DB::beginTransaction();
        try {
            // Report data to MinecraftPlayerSessions table and end the session if condition there.
            $sessionEndedCarbonDate = $request->session_ended_at ? Carbon::createFromTimestampMs($request->session_ended_at) : null;
            MinecraftPlayerSession::where('uuid', $request->session_uuid)->update([
                "mob_kills" => $request->mob_kills,
                "player_kills" => $request->player_kills,
                "deaths" => $request->deaths,
                "afk_time" => $request->afk_time,
                "is_kicked" => $request->is_kicked,
                "is_banned" => $request->is_banned,
                "is_op" => $request->is_op || $minecraftPlayerSession->is_op,
                "session_ended_at" => $sessionEndedCarbonDate,
            ]);

            // TODO: Create PlayerWorldStats for the given session for each worlds, createOrUpdate

            $minecraftServerWorld = MinecraftServerWorld::where('server_id', $minecraftPlayerSession->server_id)->where('world_name', $request->world_name)->first();
            // Report data to MinecraftPlayerEvents table
            MinecraftPlayerEvent::create([
                "session_id" => $minecraftPlayerSession->id,
                "player_uuid" => $request->uuid,
                "player_username" => $request->username,
                "ip_address" => $request->ip_address,
                "player_ping" => $request->player_ping,
                "mob_kills" => $request->mob_kills_xmin,
                "player_kills" => $request->player_kills_xmin,
                "deaths" => $request->deaths_xmin,
                "afk_time" => $request->afk_time_xmin,
                "items_used" => $request->items_used_xmin,
                "items_mined" => $request->items_mined_xmin,
                "items_picked_up" => $request->items_picked_up_xmin,
                "items_dropped" => $request->items_dropped_xmin,
                "items_broken" => $request->items_broken_xmin,
                "items_crafted" => $request->items_crafted_xmin,
                "items_placed" => $request->items_placed_xmin,
                "items_consumed" => $request->items_consumed_xmin,
                "world_location" => $request->world_location,
                'minecraft_server_world_id' => $minecraftServerWorld?->id,
            ]);

            DB::commit();
            // Return response
            return response()->json([
                'status' => 'success',
                'message' => 'PlayerIntel event for '.$request->username.' reported successfully.',
            ], 201);
        } catch(\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to report Event data.',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }
}
