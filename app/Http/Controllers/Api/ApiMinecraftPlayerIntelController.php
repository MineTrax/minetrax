<?php

namespace App\Http\Controllers\Api;

use App\Events\MinecraftPlayerEventCreated;
use App\Events\MinecraftPlayerSessionCreated;
use App\Http\Controllers\Controller;
use App\Models\MinecraftPlayerDeath;
use App\Models\MinecraftPlayerEvent;
use App\Models\MinecraftPlayerPvpKill;
use App\Models\MinecraftPlayerSession;
use App\Models\MinecraftPlayerWorldStat;
use App\Models\MinecraftServerWorld;
use App\Models\Server;
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
            'join_address' => 'sometimes|nullable|string',
            'minecraft_version' => 'sometimes|nullable|string',
            'display_name' => 'required|string',
            'session_started_at' => 'required|numeric',
            'is_op' => 'required|boolean',
            'players_world_stat_intel' => 'sometimes|nullable|array',
        ]);

        $server = Server::where('id', $request->server_id)->firstOrFail();
        if (! $server->is_player_intel_enabled) {
            return response()->json([
                'status' => 'error',
                'message' => 'Player intel is disabled for this server.',
            ], 403);
        }

        try {
            $playerCountryId = $geolocationService->getCountryIdFromIP($request->ip_address);
            $carbonDate = Carbon::createFromTimestampMs($request->session_started_at);
            // trim join address last .
            $fixedJoinAddress = $request->join_address ? trim($request->join_address, '.') : null;
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
                'join_address' => $fixedJoinAddress,
                'minecraft_version' => $request->minecraft_version,
            ]);

            if ($request->players_world_stat_intel) {
                foreach ($request->players_world_stat_intel as $playerWorldStat) {
                    $sWorld = MinecraftServerWorld::where('world_name', $playerWorldStat['world_name'])->where('server_id', $request->server_id)->first();
                    if (! $sWorld) {
                        continue;
                    }
                    MinecraftPlayerWorldStat::create([
                        'player_uuid' => $request->uuid,
                        'session_id' => $newSession->id,
                        'minecraft_server_world_id' => $sWorld->id,
                        'survival_time' => 0,
                        'creative_time' => 0,
                        'adventure_time' => 0,
                        'spectator_time' => 0,
                    ]);
                }
            }

            // Create a minecraft_player and player if not exists using event.
            MinecraftPlayerSessionCreated::dispatch($newSession, $request->all());

            return response()->json($newSession, 201);
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'status' => 'error',
                'message' => __('Failed to start player session.'),
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
                'message' => __('Failed reporting player pvp kill.'),
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
                'cause' => $request->cause ?? 'unknown',
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
                'message' => __('Failed reporting player pvp kill.'),
                'debug' => $e->getMessage(),
            ], 500);
        }
    }

    public function postReportEvent(Request $request, GeolocationService $geolocationService)
    {
        $request->validate([
            'session_uuid' => 'required|uuid|exists:minecraft_player_sessions,uuid',
            'uuid' => 'required|uuid',
            'username' => 'required|string',
            'server_id' => 'required|exists:servers,id',
            'ip_address' => 'required|string',
            'display_name' => 'nullable|string',
            'is_op' => 'required|boolean',
            'session_started_at' => 'required',
            'session_ended_at' => 'required|nullable',
            'mob_kills' => 'required|numeric',
            'player_kills' => 'required|numeric',
            'deaths' => 'required|numeric',
            'afk_time' => 'required|numeric',
            'play_time' => 'sometimes|nullable|integer',
            'is_kicked' => 'required|boolean',
            'is_banned' => 'required|boolean',

            'player_ping' => 'nullable|numeric',
            'mob_kills_xmin' => 'required|numeric',
            'player_kills_xmin' => 'required|numeric',
            'deaths_xmin' => 'required|numeric',
            'items_used_xmin' => 'required|numeric',
            'items_mined_xmin' => 'required|numeric',
            'items_picked_up_xmin' => 'required|numeric',
            'items_dropped_xmin' => 'required|numeric',
            'items_broken_xmin' => 'required|numeric',
            'items_crafted_xmin' => 'required|numeric',
            'items_placed_xmin' => 'required|numeric',
            'items_consumed_xmin' => 'required|numeric',
            'afk_time_xmin' => 'required|numeric',
            'play_time_xmin' => 'required|numeric',
            'world_location' => 'sometimes|nullable|json',
            'world_name' => 'sometimes|nullable|string',
            'players_world_stat_intel' => 'sometimes|nullable|array',

            'fish_caught_xmin' => 'sometimes|nullable|integer',
            'items_enchanted_xmin' => 'sometimes|nullable|integer',
            'times_slept_in_bed_xmin' => 'sometimes|nullable|integer',
            'jumps_xmin' => 'sometimes|nullable|integer',
            'raids_won_xmin' => 'sometimes|nullable|integer',
            'distance_traveled_xmin' => 'sometimes|nullable|numeric',
            'distance_traveled_on_land_xmin' => 'sometimes|nullable|numeric',
            'distance_traveled_on_water_xmin' => 'sometimes|nullable|numeric',
            'distance_traveled_on_air_xmin' => 'sometimes|nullable|numeric',
            'pvp_damage_given_xmin' => 'sometimes|nullable|numeric',
            'pvp_damage_taken_xmin' => 'sometimes|nullable|numeric',
            'vault_balance' => 'sometimes|nullable|numeric',
            'vault_groups' => 'sometimes|nullable|array',
            'inventory' => 'sometimes|nullable|json',
            'ender_chest' => 'sometimes|nullable|json',
        ]);

        $inventory = $request->inventory ? json_decode($request->inventory) : null;
        $enderchest = $request->ender_chest ? json_decode($request->ender_chest) : null;
        $worldLocation = $request->world_location ? json_decode($request->world_location) : null;

        $minecraftPlayerSession = MinecraftPlayerSession::where('uuid', $request->session_uuid)->firstOrFail();
        DB::beginTransaction();
        try {
            // Report data to MinecraftPlayerSessions table and end the session if condition there.
            $sessionEndedCarbonDate = $request->session_ended_at ? Carbon::createFromTimestampMs($request->session_ended_at) : null;
            MinecraftPlayerSession::where('uuid', $request->session_uuid)->update([
                'player_ping' => $request->player_ping,
                'mob_kills' => $request->mob_kills,
                'player_kills' => $request->player_kills,
                'deaths' => $request->deaths,
                'afk_time' => $request->afk_time,
                'play_time' => $request->play_time ?? 0,
                'is_kicked' => $request->is_kicked,
                'is_banned' => $request->is_banned,
                'is_op' => $request->is_op || $minecraftPlayerSession->is_op,
                'session_ended_at' => $sessionEndedCarbonDate,
                'vault_balance' => $request->vault_balance,
                'vault_groups' => $request->vault_groups,
            ]);

            // Update PlayerWorldStats for the given session for each world
            if ($request->players_world_stat_intel) {
                foreach ($request->players_world_stat_intel as $playerWorldStat) {
                    $sWorld = MinecraftServerWorld::where('world_name', $playerWorldStat['world_name'])->where('server_id', $request->server_id)->first();
                    if (! $sWorld) {
                        continue;
                    }
                    MinecraftPlayerWorldStat::updateOrCreate(
                        [
                            'session_id' => $minecraftPlayerSession->id,
                            'minecraft_server_world_id' => $sWorld->id,
                            'player_uuid' => $request->uuid,
                        ],
                        [
                            'survival_time' => $playerWorldStat['survival_time'],
                            'creative_time' => $playerWorldStat['creative_time'],
                            'adventure_time' => $playerWorldStat['adventure_time'],
                            'spectator_time' => $playerWorldStat['spectator_time'],
                        ]
                    );
                }
            }

            // Report data to MinecraftPlayerEvents table
            $minecraftServerWorld = MinecraftServerWorld::where('server_id', $minecraftPlayerSession->server_id)->where('world_name', $request->world_name)->first();
            $mcPlayerEvent = MinecraftPlayerEvent::create([
                'session_id' => $minecraftPlayerSession->id,
                'player_uuid' => $request->uuid,
                'player_username' => $request->username,
                'player_displayname' => $request->display_name,
                'ip_address' => $request->ip_address,
                'player_ping' => $request->player_ping,
                'mob_kills' => $request->mob_kills_xmin,
                'player_kills' => $request->player_kills_xmin,
                'deaths' => $request->deaths_xmin,
                'afk_time' => $request->afk_time_xmin,
                'play_time' => $request->play_time_xmin,
                'items_used' => $request->items_used_xmin,
                'items_mined' => $request->items_mined_xmin,
                'items_picked_up' => $request->items_picked_up_xmin,
                'items_dropped' => $request->items_dropped_xmin,
                'items_broken' => $request->items_broken_xmin,
                'items_crafted' => $request->items_crafted_xmin,
                'items_placed' => $request->items_placed_xmin,
                'items_consumed' => $request->items_consumed_xmin,
                'world_location' => $worldLocation,
                'minecraft_server_world_id' => $minecraftServerWorld?->id,
                'fish_caught' => $request->fish_caught_xmin,
                'items_enchanted' => $request->items_enchanted_xmin,
                'times_slept_in_bed' => $request->times_slept_in_bed_xmin,
                'times_jumped' => $request->jumps_xmin,
                'raids_won' => $request->raids_won_xmin,
                'distance_traveled' => $request->distance_traveled_xmin,
                'distance_traveled_on_land' => $request->distance_traveled_on_land_xmin,
                'distance_traveled_on_water' => $request->distance_traveled_on_water_xmin,
                'distance_traveled_on_air' => $request->distance_traveled_on_air_xmin,
                'pvp_damage_given' => $request->pvp_damage_given_xmin,
                'pvp_damage_taken' => $request->pvp_damage_taken_xmin,
                'vault_balance' => $request->vault_balance,
                'vault_groups' => $request->vault_groups,
                'enderchest_items' => $enderchest,
                'inventory_items' => $inventory,
            ]);

            DB::commit();

            // Fire Event for this so we can update minecraft_players and players table.
            MinecraftPlayerEventCreated::dispatch($mcPlayerEvent, $minecraftPlayerSession->server_id, $request->all());

            // Return response
            return response()->json([
                'status' => 'success',
                'message' => __('PlayerIntel event for :username reported successfully.', ['username' => $request->username]),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'status' => 'error',
                'message' => __('Failed to report Event data.'),
                'debug' => $e->getMessage(),
            ], 500);
        }
    }
}
