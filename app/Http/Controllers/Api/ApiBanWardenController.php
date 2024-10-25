<?php

namespace App\Http\Controllers\Api;

use App\Events\BanWardenPlayerPardoned;
use App\Events\BanWardenPlayerPunished;
use App\Jobs\GeneratePunishmentInsightsJob;
use App\Models\Player;
use App\Models\PlayerPunishment;
use App\Models\Server;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Cache;

class ApiBanWardenController extends ApiController
{
    public function postSyncPunishments(Request $request, GeolocationService $geolocationService)
    {
        $request->validate([
            'data' => 'required|array',
            'data.server_id' => 'required|numeric|exists:servers,id',
            'data.punishments' => 'required|array',
            'data.punishments.*.plugin_name' => 'required|in:litebans,libertybans',
            'data.punishments.*.plugin_punishment_id' => 'required|string',
            'data.punishments.*.type' => 'required|in:ban,kick,mute,warn',
            'data.punishments.*.uuid' => 'nullable|string',
            'data.punishments.*.ip_address' => 'nullable|ip',
            'data.punishments.*.is_ipban' => 'required|boolean',
            'data.punishments.*.start_at' => 'required|numeric',
            'data.punishments.*.end_at' => 'nullable|numeric',
            'data.punishments.*.reason' => 'nullable|string',
            'data.punishments.*.server_scope' => 'nullable|string',
            'data.punishments.*.origin_server_name' => 'nullable|string',
            'data.punishments.*.creator_uuid' => 'nullable|string',
            'data.punishments.*.creator_username' => 'nullable|string',
            'data.punishments.*.remover_uuid' => 'nullable|string',
            'data.punishments.*.remover_username' => 'nullable|string',
            'data.punishments.*.removed_reason' => 'nullable|string',
            'data.punishments.*.removed_at' => 'nullable|numeric',
            'data.punishments.*.from_event' => 'required|in:punish,pardon,sync',
        ]);

        $banwardenEnabledInConfig = config('minetrax.banwarden_enabled');
        $server = Server::where('id', $request->input('data.server_id'))->firstOrFail();
        if (!$banwardenEnabledInConfig || !$server->settings['is_banwarden_enabled']) {
            return $this->error(__('BanWarden is disabled globally or on this server.'), 'banwarden_disabled', 403);
        }

        DB::beginTransaction();
        try {
            $punishments = $request->input('data.punishments');
            foreach ($punishments as $punishment) {
                $playerUuid = Str::isUuid($punishment['uuid']) ? $punishment['uuid'] : null;
                $creatorUuid = Str::isUuid($punishment['creator_uuid']) ? $punishment['creator_uuid'] : null;
                $removerUuid = Str::isUuid($punishment['remover_uuid']) ? $punishment['remover_uuid'] : null;
                // Find player id
                $player = $playerUuid ? Player::where('uuid', $punishment['uuid'])->first() : null;

                $startAtDate = Carbon::createFromTimestampMs($punishment['start_at']);
                $endAtDate = $punishment['end_at'] ? Carbon::createFromTimestampMs($punishment['end_at']) : null;
                $removedAtDate = $punishment['removed_at'] ? Carbon::createFromTimestampMs($punishment['removed_at']) : null;

                // Server ID for scope and origin
                $serverScope = $punishment['server_scope'];
                $scopeServer = null;
                if ($serverScope != "*" && $serverScope != null) {
                    $scopeServer = Server::where('settings->server_identifier', $serverScope)
                        ->orWhere('name', $serverScope)
                        ->first();
                }
                $originServerName = $punishment['origin_server_name'];
                $originServer = null;
                if ($originServerName != null) {
                    $originServer = Server::where('settings->server_identifier', $originServerName)
                        ->orWhere('name', $originServerName)
                        ->first();
                }
                if (!$originServer) {
                    $originServer = $server;
                }

                // Get country id from IP
                $forGeoIp = str_replace(['%', '*'], '0', $punishment['ip_address']);
                $countryId = $geolocationService->getCountryIdFromIP($forGeoIp);

                PlayerPunishment::updateOrCreate([
                    'type' => $punishment['type'],
                    'plugin_punishment_id' => $punishment['plugin_punishment_id'],
                    'plugin_name' => $punishment['plugin_name'],
                ], [
                    'type' => $punishment['type'],
                    'uuid' => $playerUuid,
                    'ip_address' => $punishment['ip_address'],
                    'player_id' => $player?->id,
                    'is_ipban' => $punishment['is_ipban'],
                    'country_id' => $countryId,
                    'start_at' => $startAtDate,
                    'end_at' => $endAtDate,
                    'reason' => $punishment['reason'],
                    'server_scope' => $punishment['server_scope'],
                    'scope_server_id' => $scopeServer?->id,
                    'origin_server_name' => $punishment['origin_server_name'],
                    'origin_server_id' => $originServer?->id,
                    'creator_uuid' => $creatorUuid,
                    'creator_username' => $punishment['creator_username'],
                    'remover_uuid' => $removerUuid,
                    'remover_username' => $punishment['remover_username'],
                    'removed_reason' => $punishment['removed_reason'],
                    'removed_at' => $removedAtDate,
                ]);
            }

            DB::commit();

            // Forget Metrics Cache
            Cache::forget('banwarden.public.metrics');

            return $this->success(null, __('Punishments synced successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);

            return $this->error(__('Failed to sync punishments: :message', [
                'message' => $e->getMessage(),
            ]), 'failed_to_sync_punishments', 500);
        }
    }

    public function postReportPunishment(Request $request, GeolocationService $geolocationService)
    {
        $request->validate([
            'data' => 'required|array',
            'data.server_id' => 'required|numeric|exists:servers,id',
            'data.plugin_name' => 'required|in:litebans,libertybans',
            'data.plugin_punishment_id' => 'required|string',
            'data.type' => 'required|in:ban,kick,mute,warn',
            'data.uuid' => 'nullable|string',
            'data.ip_address' => 'nullable|ip',
            'data.is_ipban' => 'required|boolean',
            'data.start_at' => 'required|numeric',
            'data.end_at' => 'nullable|numeric',
            'data.reason' => 'nullable|string',
            'data.server_scope' => 'nullable|string',
            'data.origin_server_name' => 'nullable|string',
            'data.creator_uuid' => 'nullable|string',
            'data.creator_username' => 'nullable|string',
            'data.remover_uuid' => 'nullable|string',
            'data.remover_username' => 'nullable|string',
            'data.removed_reason' => 'nullable|string',
            'data.removed_at' => 'nullable|numeric',
            'data.from_event' => 'required|in:punish,pardon,sync',
        ]);

        $banwardenEnabledInConfig = config('minetrax.banwarden_enabled');
        $server = Server::where('id', $request->input('data.server_id'))->firstOrFail();
        if (!$banwardenEnabledInConfig || !$server->settings['is_banwarden_enabled']) {
            return $this->error(__('BanWarden is disabled globally or on this server.'), 'banwarden_disabled', 403);
        }

        try {
            $punishment = $request->input('data');
            $playerUuid = Str::isUuid($punishment['uuid']) ? $punishment['uuid'] : null;
            $creatorUuid = Str::isUuid($punishment['creator_uuid']) ? $punishment['creator_uuid'] : null;
            $removerUuid = Str::isUuid($punishment['remover_uuid']) ? $punishment['remover_uuid'] : null;

            // Find player id
            $player = $playerUuid ? Player::where('uuid', $playerUuid)->first() : null;

            $startAtDate = Carbon::createFromTimestampMs($punishment['start_at']);
            $endAtDate = $punishment['end_at'] ? Carbon::createFromTimestampMs($punishment['end_at']) : null;
            $removedAtDate = $punishment['removed_at'] ? Carbon::createFromTimestampMs($punishment['removed_at']) : null;

            // Server ID for scope and origin
            $serverScope = $punishment['server_scope'];
            $scopeServer = null;
            if ($serverScope != "*" && $serverScope != null) {
                $scopeServer = Server::where('settings->server_identifier', $serverScope)
                    ->orWhere('name', $serverScope)
                    ->first();
            }
            $originServerName = $punishment['origin_server_name'];
            $originServer = null;
            if ($originServerName != null) {
                $originServer = Server::where('settings->server_identifier', $originServerName)
                    ->orWhere('name', $originServerName)
                    ->first();
            }
            if (!$originServer) {
                $originServer = $server;
            }

            // Get country id from IP
            $forGeoIp = str_replace(['%', '*'], '0', $punishment['ip_address']);
            $countryId = $geolocationService->getCountryIdFromIP($forGeoIp);

            $punishment = PlayerPunishment::updateOrCreate([
                'type' => $punishment['type'],
                'plugin_punishment_id' => $punishment['plugin_punishment_id'],
                'plugin_name' => $punishment['plugin_name'],
            ], [
                'type' => $punishment['type'],
                'uuid' => $playerUuid,
                'ip_address' => $punishment['ip_address'],
                'player_id' => $player?->id,
                'is_ipban' => $punishment['is_ipban'],
                'country_id' => $countryId,
                'start_at' => $startAtDate,
                'end_at' => $endAtDate,
                'reason' => $punishment['reason'],
                'server_scope' => $punishment['server_scope'],
                'scope_server_id' => $scopeServer?->id,
                'origin_server_name' => $punishment['origin_server_name'],
                'origin_server_id' => $originServer?->id,
                'creator_uuid' => $creatorUuid,
                'creator_username' => $punishment['creator_username'],
                'remover_uuid' => $removerUuid,
                'remover_username' => $punishment['remover_username'],
                'removed_reason' => $punishment['removed_reason'],
                'removed_at' => $removedAtDate,
            ]);

            // Dispatch Events
            if ($punishment->from_event == 'punish') {
                BanWardenPlayerPunished::dispatch($punishment);
            } else if ($punishment->from_event == 'pardon') {
                BanWardenPlayerPardoned::dispatch($punishment);
            }

            // AI Insights
            $insightEnabled = config('minetrax.banwarden_ai_insights_enabled');
            if ($insightEnabled && !$punishment->insights) {
                GeneratePunishmentInsightsJob::dispatch($punishment);
            }

            // Forget Metrics Cache
            Cache::forget('banwarden.public.metrics');

            return $this->success(null, __('Punishment reported successfully.'));
        } catch (\Exception $e) {
            \Log::error($e);

            return $this->error(__('Failed to report punishment: :message', [
                'message' => $e->getMessage(),
            ]), 'failed_to_report_punishment', 500);
        }
    }
}
