<?php

namespace App\Http\Controllers;

use App\Models\MinecraftPlayerSession;
use App\Models\Player;
use App\Queries\Filters\FilterMultipleFields;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PlayerIntelController extends Controller
{
    // public function overview(Player $player, Request $request)
    // {
    //     $this->authorize('viewIntel', $player);

    //     return [
    //         'hello' => 'world',
    //     ];
    // }

    public function indexSession(Player $player, Request $request)
    {
        $this->authorize('viewIntel', $player);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $selectFields = [
            'id',
            'player_uuid',
            'player_username',
            'player_displayname',
            'session_started_at',
            'session_ended_at',
            'created_at',
            'updated_at',
            'country_id',
            'server_id',
            'play_time',
            'afk_time',
        ];

        $sessions = QueryBuilder::for(MinecraftPlayerSession::class)
            ->where('player_uuid', $player->uuid)
            ->with(['country:id,name,iso_code', 'server:id,name'])
            ->select($selectFields)
            ->allowedFilters([
                ...$selectFields,
                AllowedFilter::custom('q', new FilterMultipleFields(['player_username', 'player_displayname'])),
            ])
            ->allowedSorts($selectFields)
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('PlayerIntel/IndexSession', [
            'sessions' => $sessions,
            'player' => $player->only(['id', 'uuid', 'username']),
            'filters' => request()->all(['perPage', 'sort', 'filter']),
            'canShowPlayerIntel' => true,
        ]);
    }

    public function showSession(Player $player, $session, Request $request)
    {
        $this->authorize('viewIntel', $player);

        $session = MinecraftPlayerSession::where('player_uuid', $player->uuid)
            ->where('id', $session)
            ->firstOrFail();

        $session->load([
            'country:id,name,iso_code',
            'server:id,name',
            'minecraftPlayer:id,player_uuid,player_username,player_displayname',
        ]);

        // event counters
        $eventCounts = DB::table('minecraft_player_events')
            ->where('session_id', $session->id)
            ->selectRaw('SUM(items_used) AS items_used')
            ->selectRaw('SUM(items_mined) AS items_mined')
            ->selectRaw('SUM(items_picked_up) AS items_picked_up')
            ->selectRaw('SUM(items_dropped) AS items_dropped')
            ->selectRaw('SUM(items_broken) AS items_broken')
            ->selectRaw('SUM(items_crafted) AS items_crafted')
            ->selectRaw('SUM(items_placed) AS items_placed')
            ->selectRaw('SUM(items_consumed) AS items_consumed')
            ->selectRaw('SUM(items_enchanted) AS items_enchanted')
            ->selectRaw('SUM(fish_caught) AS fish_caught')
            ->selectRaw('SUM(times_slept_in_bed) AS times_slept_in_bed')
            ->selectRaw('SUM(raids_won) AS raids_won')
            ->selectRaw('SUM(distance_traveled) AS distance_traveled')
            ->selectRaw('SUM(distance_traveled_on_land) AS distance_traveled_on_land')
            ->selectRaw('SUM(distance_traveled_on_water) AS distance_traveled_on_water')
            ->selectRaw('SUM(distance_traveled_on_air) AS distance_traveled_on_air')
            ->selectRaw('SUM(pvp_damage_given) AS pvp_damage_given')
            ->selectRaw('SUM(pvp_damage_taken) AS pvp_damage_taken')
            ->first();

        $showCriticalInfo = false;
        $criticalInfo = null;
        if($request->user() && $request->user()->can('view player_intel_critical'))
        {
            $showCriticalInfo = true;
            $latestEvent = DB::table('minecraft_player_events')
                ->where('session_id', $session->id)
                ->orderBy('id', 'desc')
                ->first();

            $criticalInfo['player_ip_address'] = $session->player_ip_address;
            $criticalInfo['join_address'] = $session->join_address;
            $criticalInfo['minecraft_version'] = $session->minecraft_version;
            $criticalInfo['player_ping'] = $session->player_ping;
            $criticalInfo['is_banned'] = $session->is_banned;
            $criticalInfo['is_kicked'] = $session->is_kicked;
            $criticalInfo['is_op'] = $session->is_op;
            $criticalInfo['vault_groups'] = $session->vault_groups;
            $criticalInfo['vault_balance'] = $session->vault_balance;
            $criticalInfo['world_location'] = $latestEvent?->world_location;
        }

        return Inertia::render('PlayerIntel/ShowSession', [
            'session' => $session,
            'eventCounts' => $eventCounts,
            'criticalInfo' => $criticalInfo,
            'showCriticalInfo' => $showCriticalInfo,
            'player' => $player->only(['id', 'uuid', 'username']),
            'canShowPlayerIntel' => true,
        ]);
    }
}
