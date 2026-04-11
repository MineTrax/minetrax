<?php

namespace App\Http\Controllers\Api;

use App\Events\MinecraftPlayerEventCreated;
use App\Events\MinecraftPlayerSessionCreated;
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

class ApiMinecraftPlayerIntelController extends ApiController
{
    public function postSessionInit(Request $request, GeolocationService $geolocationService)
    {
        $request->validate([
            'data' => 'required|array',
            'data.server_id' => 'required|numeric|exists:servers,id',
            'data.session_uuid' => 'required|uuid',
            'data.uuid' => 'required|uuid',
            'data.player_id' => 'sometimes|nullable|numeric',
            'data.username' => 'required|string',
            'data.ip_address' => 'required|string',
            'data.join_address' => 'sometimes|nullable|string',
            'data.minecraft_version' => 'sometimes|nullable|string',
            'data.display_name' => 'required|string',
            'data.session_started_at' => 'required|numeric',
            'data.is_op' => 'required|boolean',
            'data.players_world_stat_intel' => 'sometimes|nullable|array',
            'data.skin_property' => 'sometimes|nullable|json',
            'data.skin_texture_id' => 'sometimes|nullable|string',
        ]);

        $server = Server::where('id', $request->input('data.server_id'))->firstOrFail();
        if (! $server->is_player_intel_enabled) {
            return $this->error(__('Player intel is disabled for this server.'), 'player_intel_disabled', 403);
        }

        try {
            $playerCountryId = $geolocationService->getCountryIdFromIP($request->input('data.ip_address'));
            $timezone = config('app.timezone');
            $carbonDate = Carbon::createFromTimestampMs($request->input('data.session_started_at'), $timezone);
            // trim join address last .
            $fixedJoinAddress = $request->input('data.join_address') ? trim($request->input('data.join_address'), '.') : null;
            // Start the session
            $newSession = MinecraftPlayerSession::create([
                'uuid' => $request->input('data.session_uuid'),
                'server_id' => $request->input('data.server_id'),
                'player_uuid' => $request->input('data.uuid'),
                'player_username' => $request->input('data.username'),
                'player_displayname' => $request->input('data.display_name'),
                'session_started_at' => $carbonDate,
                'player_ip_address' => $request->input('data.ip_address'),
                'country_id' => $playerCountryId,
                'is_op' => $request->input('data.is_op'),
                'join_address' => $fixedJoinAddress,
                'minecraft_version' => $request->input('data.minecraft_version'),
            ]);

            if ($request->input('data.players_world_stat_intel')) {
                foreach ($request->input('data.players_world_stat_intel') as $playerWorldStat) {
                    $sWorld = MinecraftServerWorld::where('world_name', $playerWorldStat['world_name'])->where('server_id', $request->input('data.server_id'))->first();
                    if (! $sWorld) {
                        continue;
                    }
                    MinecraftPlayerWorldStat::create([
                        'player_uuid' => $request->input('data.uuid'),
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
            MinecraftPlayerSessionCreated::dispatch($newSession, $request->input('data'));

            return $this->success($newSession);
        } catch (\Exception $e) {
            \Log::error($e);

            return $this->error(
                __('Failed to start player session. :message', [
                    'message' => $e->getMessage(),
                ]),
                'internal_server_error',
                500,
            );
        }
    }

    public function postReportPvpKill(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.session_uuid' => 'required|uuid|exists:minecraft_player_sessions,uuid',
            'data.killer_uuid' => 'required|uuid',
            'data.killer_username' => 'required|string',
            'data.victim_uuid' => 'required|uuid',
            'data.victim_username' => 'required|string',
            'data.weapon' => 'present|nullable|string',
            'data.killed_at' => 'required|numeric',
            'data.world_name' => 'present|nullable|string',
            'data.world_location' => 'present|nullable|string',
        ]);

        $minecraftPlayerSession = MinecraftPlayerSession::where('uuid', $request->input('data.session_uuid'))->firstOrFail();
        try {
            $minecraftServerWorld = MinecraftServerWorld::where('server_id', $minecraftPlayerSession->server_id)->where('world_name', $request->input('data.world_name'))->first();
            $timezone = config('app.timezone');
            $killedAt = Carbon::createFromTimestampMs($request->input('data.killed_at'), $timezone);

            $minecraftPlayerPvpKill = MinecraftPlayerPvpKill::create([
                'session_id' => $minecraftPlayerSession->id,
                'killer_uuid' => $request->input('data.killer_uuid'),
                'killer_username' => $request->input('data.killer_username'),
                'victim_uuid' => $request->input('data.victim_uuid'),
                'victim_username' => $request->input('data.victim_username'),
                'killed_at' => $killedAt,
                'weapon' => $request->input('data.weapon'),
                'world_location' => $request->input('data.world_location'),
                'minecraft_server_world_id' => $minecraftServerWorld?->id,
            ]);

            return $this->success($minecraftPlayerPvpKill);
        } catch (\Exception $e) {
            \Log::error($e);

            return $this->error(__('Failed reporting player pvp kill: :message', [
                'message' => $e->getMessage(),
            ]), 'internal_server_error', 500);
        }
    }

    public function postReportDeath(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.session_uuid' => 'required|uuid|exists:minecraft_player_sessions,uuid',
            'data.player_uuid' => 'required|uuid',
            'data.player_username' => 'required|string',
            'data.cause' => 'present|nullable|string',
            'data.killer_uuid' => 'present|nullable|uuid',
            'data.killer_username' => 'present|nullable|string',
            'data.killer_entity_id' => 'present|nullable|string',
            'data.killer_entity_name' => 'present|nullable|string',
            'data.died_at' => 'required|numeric',
            'data.world_name' => 'present|nullable|string',
            'data.world_location' => 'present|nullable|string',
        ]);

        $minecraftPlayerSession = MinecraftPlayerSession::where('uuid', $request->input('data.session_uuid'))->firstOrFail();
        try {
            $minecraftServerWorld = MinecraftServerWorld::where('server_id', $minecraftPlayerSession->server_id)->where('world_name', $request->input('data.world_name'))->first();
            $timezone = config('app.timezone');
            $diedAt = Carbon::createFromTimestampMs($request->input('data.died_at'), $timezone);

            $minecraftPlayerDeath = MinecraftPlayerDeath::create([
                'session_id' => $minecraftPlayerSession->id,
                'player_uuid' => $request->input('data.player_uuid'),
                'player_username' => $request->input('data.player_username'),
                'cause' => $request->input('data.cause') ?? 'unknown',
                'killer_uuid' => $request->input('data.killer_uuid'),
                'killer_username' => $request->input('data.killer_username'),
                'killer_entity_id' => $request->input('data.killer_entity_id'),
                'killer_entity_name' => $request->input('data.killer_entity_name'),
                'died_at' => $diedAt,
                'world_location' => $request->input('data.world_location'),
                'minecraft_server_world_id' => $minecraftServerWorld?->id,
            ]);

            return $this->success($minecraftPlayerDeath);
        } catch (\Exception $e) {
            \Log::error($e);

            return $this->error(__('Failed reporting player death: :message', [
                'message' => $e->getMessage(),
            ]), 'internal_server_error', 500);
        }
    }

    public function postReportEvent(Request $request, GeolocationService $geolocationService)
    {
        $request->validate([
            'data' => 'required|array',
            'data.session_uuid' => 'required|uuid|exists:minecraft_player_sessions,uuid',
            'data.uuid' => 'required|uuid',
            'data.username' => 'required|string',
            'data.server_id' => 'required|exists:servers,id',
            'data.ip_address' => 'required|string',
            'data.display_name' => 'nullable|string',
            'data.is_op' => 'required|boolean',
            'data.session_started_at' => 'required',
            'data.session_ended_at' => 'required|nullable',
            'data.mob_kills' => 'required|numeric',
            'data.player_kills' => 'required|numeric',
            'data.deaths' => 'required|numeric',
            'data.afk_time' => 'required|numeric',
            'data.play_time' => 'sometimes|nullable|integer',
            'data.is_kicked' => 'required|boolean',
            'data.is_banned' => 'required|boolean',

            'data.player_ping' => 'nullable|numeric',
            'data.mob_kills_xmin' => 'required|numeric',
            'data.player_kills_xmin' => 'required|numeric',
            'data.deaths_xmin' => 'required|numeric',
            'data.items_used_xmin' => 'required|numeric',
            'data.items_mined_xmin' => 'required|numeric',
            'data.items_picked_up_xmin' => 'required|numeric',
            'data.items_dropped_xmin' => 'required|numeric',
            'data.items_broken_xmin' => 'required|numeric',
            'data.items_crafted_xmin' => 'required|numeric',
            'data.items_placed_xmin' => 'required|numeric',
            'data.items_consumed_xmin' => 'required|numeric',
            'data.afk_time_xmin' => 'required|numeric',
            'data.play_time_xmin' => 'required|numeric',
            'data.world_location' => 'sometimes|nullable|json',
            'data.world_name' => 'sometimes|nullable|string',
            'data.players_world_stat_intel' => 'sometimes|nullable|array',

            'data.fish_caught_xmin' => 'sometimes|nullable|integer',
            'data.items_enchanted_xmin' => 'sometimes|nullable|integer',
            'data.times_slept_in_bed_xmin' => 'sometimes|nullable|integer',
            'data.jumps_xmin' => 'sometimes|nullable|integer',
            'data.raids_won_xmin' => 'sometimes|nullable|integer',
            'data.distance_traveled_xmin' => 'sometimes|nullable|numeric',
            'data.distance_traveled_on_land_xmin' => 'sometimes|nullable|numeric',
            'data.distance_traveled_on_water_xmin' => 'sometimes|nullable|numeric',
            'data.distance_traveled_on_air_xmin' => 'sometimes|nullable|numeric',
            'data.pvp_damage_given_xmin' => 'sometimes|nullable|numeric',
            'data.pvp_damage_taken_xmin' => 'sometimes|nullable|numeric',
            'data.vault_balance' => 'sometimes|nullable|numeric',
            'data.vault_groups' => 'sometimes|nullable|array',
            'data.inventory' => 'sometimes|nullable|json',
            'data.ender_chest' => 'sometimes|nullable|json',

            'data.skin_property' => 'sometimes|nullable|json',
            'data.skin_texture_id' => 'sometimes|nullable|string',
        ]);

        $inventory = $request->input('data.inventory') ? json_decode($request->input('data.inventory')) : null;
        $enderchest = $request->input('data.ender_chest') ? json_decode($request->input('data.ender_chest')) : null;
        $worldLocation = $request->input('data.world_location') ? json_decode($request->input('data.world_location')) : null;

        $minecraftPlayerSession = MinecraftPlayerSession::where('uuid', $request->input('data.session_uuid'))->firstOrFail();
        DB::beginTransaction();
        try {
            // Report data to MinecraftPlayerSessions table and end the session if condition there.
            $timezone = config('app.timezone');
            $sessionEndedCarbonDate = $request->input('data.session_ended_at') ? Carbon::createFromTimestampMs($request->input('data.session_ended_at'), $timezone) : null;
            MinecraftPlayerSession::where('uuid', $request->input('data.session_uuid'))->update([
                'player_ping' => $request->input('data.player_ping'),
                'mob_kills' => $request->input('data.mob_kills'),
                'player_kills' => $request->input('data.player_kills'),
                'deaths' => $request->input('data.deaths'),
                'afk_time' => $request->input('data.afk_time'),
                'play_time' => $request->input('data.play_time') ?? 0,
                'is_kicked' => $request->input('data.is_kicked'),
                'is_banned' => $request->input('data.is_banned'),
                'is_op' => $request->input('data.is_op') || $minecraftPlayerSession->is_op,
                'session_ended_at' => $sessionEndedCarbonDate,
                'vault_balance' => $request->input('data.vault_balance'),
                'vault_groups' => $request->input('data.vault_groups'),
            ]);

            // Update PlayerWorldStats for the given session for each world
            if ($request->input('data.players_world_stat_intel')) {
                foreach ($request->input('data.players_world_stat_intel') as $playerWorldStat) {
                    $sWorld = MinecraftServerWorld::where('world_name', $playerWorldStat['world_name'])->where('server_id', $request->input('data.server_id'))->first();
                    if (! $sWorld) {
                        continue;
                    }
                    MinecraftPlayerWorldStat::updateOrCreate(
                        [
                            'session_id' => $minecraftPlayerSession->id,
                            'minecraft_server_world_id' => $sWorld->id,
                            'player_uuid' => $request->input('data.uuid'),
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
                'player_uuid' => $request->input('data.uuid'),
                'player_username' => $request->input('data.username'),
                'player_displayname' => $request->input('data.display_name'),
                'ip_address' => $request->input('data.ip_address'),
                'player_ping' => $request->input('data.player_ping'),
                'mob_kills' => $request->input('data.mob_kills_xmin'),
                'player_kills' => $request->input('data.player_kills_xmin'),
                'deaths' => $request->input('data.deaths_xmin'),
                'afk_time' => $request->input('data.afk_time_xmin'),
                'play_time' => $request->input('data.play_time_xmin'),
                'items_used' => $request->input('data.items_used_xmin'),
                'items_mined' => $request->input('data.items_mined_xmin'),
                'items_picked_up' => $request->input('data.items_picked_up_xmin'),
                'items_dropped' => $request->input('data.items_dropped_xmin'),
                'items_broken' => $request->input('data.items_broken_xmin'),
                'items_crafted' => $request->input('data.items_crafted_xmin'),
                'items_placed' => $request->input('data.items_placed_xmin'),
                'items_consumed' => $request->input('data.items_consumed_xmin'),
                'world_location' => $worldLocation,
                'minecraft_server_world_id' => $minecraftServerWorld?->id,
                'fish_caught' => $request->input('data.fish_caught_xmin'),
                'items_enchanted' => $request->input('data.items_enchanted_xmin'),
                'times_slept_in_bed' => $request->input('data.times_slept_in_bed_xmin'),
                'times_jumped' => $request->input('data.jumps_xmin'),
                'raids_won' => $request->input('data.raids_won_xmin'),
                'distance_traveled' => $request->input('data.distance_traveled_xmin'),
                'distance_traveled_on_land' => $request->input('data.distance_traveled_on_land_xmin'),
                'distance_traveled_on_water' => $request->input('data.distance_traveled_on_water_xmin'),
                'distance_traveled_on_air' => $request->input('data.distance_traveled_on_air_xmin'),
                'pvp_damage_given' => $request->input('data.pvp_damage_given_xmin'),
                'pvp_damage_taken' => $request->input('data.pvp_damage_taken_xmin'),
                'vault_balance' => $request->input('data.vault_balance'),
                'vault_groups' => $request->input('data.vault_groups'),
                'enderchest_items' => $enderchest,
                'inventory_items' => $inventory,
            ]);

            DB::commit();

            // Fire Event for this so we can update minecraft_players and players table.
            MinecraftPlayerEventCreated::dispatch($mcPlayerEvent, $minecraftPlayerSession->server_id, $request->input('data'));

            // Return response
            return $this->success(null, __('PlayerIntel event for :username reported successfully.', ['username' => $request->input('data.username')]));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);

            return $this->error(__('Failed to report Event data: :message', [
                'message' => $e->getMessage(),
            ]), 'failed_to_report_event', 500);
        }
    }
}
