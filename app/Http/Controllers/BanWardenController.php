<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePunishmentInsightsJob;
use App\Models\Country;
use App\Models\MinecraftPlayerSession;
use App\Models\Player;
use App\Models\PlayerPunishment;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class BanWardenController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', PlayerPunishment::class);
        $canViewCritical = Gate::allows('viewCritical', PlayerPunishment::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'type',
            'plugin_punishment_id',
            'uuid',
            'ip_address',
            'player_id',
            'is_ipban',
            'country_id',
            'reason',
            'start_at',
            'end_at',
            'removed_at',
            'creator_uuid',
            'creator_username',
            'server_scope',
        ];
        $punishments = QueryBuilder::for(PlayerPunishment::class)->with([
            'country:id,name,iso_code',
            'victimPlayer:id,uuid,username,skin_texture_id',
            'creatorPlayer:id,uuid,username,skin_texture_id',
        ])
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                'country.name',
                'victimPlayer.username',
                AllowedFilter::scope('status'),
                AllowedFilter::custom('q', new FilterMultipleFields([
                    'id',
                    'type',
                    'plugin_punishment_id',
                    'uuid',
                    'reason',
                    'creator_uuid',
                    'creator_username',
                    'remover_username',
                    'remover_uuid',
                ])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-id')
            ->simplePaginate(perPage: $perPage)
            ->through(fn($punishment) => $punishment->makeVisibleIf($canViewCritical, [
                'ip_address',
                'plugin_punishment_id',
                'origin_server_name',
            ]))
            ->withQueryString();

        $countries = Country::select(['id', 'name'])->get()->pluck('name');

        # Metrics
        $metrics = Cache::remember('banwarden.public.metrics', 120, function () {
            return [
                'total_bans' => PlayerPunishment::where('type', 'ban')->count(),
                'total_active_bans' => PlayerPunishment::where('type', 'ban')->status('active')->count(),
                'total_mutes' => PlayerPunishment::where('type', 'mute')->count(),
                'total_active_mutes' => PlayerPunishment::where('type', 'mute')->status('active')->count(),
                'total_warns' => PlayerPunishment::where('type', 'warn')->count(),
                'total_active_warns' => PlayerPunishment::where('type', 'warn')->status('active')->count(),
                'total_kicks' => PlayerPunishment::where('type', 'kick')->count(),
            ];
        });

        return Inertia::render('BanWarden/IndexPunishment', [
            'countries' => $countries,
            'punishments' => $punishments,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
            'metrics' => $metrics,
        ]);
    }

    public function show(PlayerPunishment $playerPunishment, Request $request)
    {
        $this->authorize('view', $playerPunishment);
        $canViewCritical = Gate::allows('viewCritical', PlayerPunishment::class);

        $playerPunishment->load([
            'country:id,name,iso_code',
            'victimPlayer:id,uuid,username,skin_texture_id',
            'creatorPlayer:id,uuid,username,skin_texture_id',
            'removerPlayer:id,uuid,username,skin_texture_id',
        ]);

        // Generate insights if enabled and not already generated
        $insightEnabled = config('minetrax.banwarden_ai_insights_enabled');
        if ($insightEnabled && !$playerPunishment->insights) {
            GeneratePunishmentInsightsJob::dispatch($playerPunishment);
        }

        $playerPunishment->victimPlayer?->append('render_url');
        $playerPunishment->makeVisibleIf(
            $canViewCritical,
            [
                'ip_address',
                'plugin_punishment_id',
                'origin_server_name',
            ]
        );
        $response = [
            'punishment' => $playerPunishment,
            'permissions' => [
                'canViewSessions' => Gate::allows('viewAnyIntel', Player::class),
                'canViewAlts' => Gate::allows('viewAlts', $playerPunishment),
                'canViewCritical' => $canViewCritical,
            ],
        ];
        return Inertia::render('BanWarden/ShowPunishment', $response);
    }

    public function indexLastPunishments(PlayerPunishment $playerPunishment)
    {
        $this->authorize('view', $playerPunishment);
        $canViewCritical = Gate::allows('viewCritical', PlayerPunishment::class);

        $perPage = request()->query('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        if ($playerPunishment->uuid) {
            $lastPunishments = PlayerPunishment::with([
                'country:id,name,iso_code',
                'victimPlayer:id,uuid,username,skin_texture_id',
                'creatorPlayer:id,uuid,username,skin_texture_id',
                'removerPlayer:id,uuid,username,skin_texture_id',
            ])
                ->where('uuid', $playerPunishment->uuid)
                ->where('id', '!=', $playerPunishment->id)
                ->orderByDesc('start_at')
                ->simplePaginate($perPage)
                ->through(fn($punishment) => $punishment->makeVisibleIf($canViewCritical, [
                    'ip_address',
                    'plugin_punishment_id',
                    'origin_server_name',
                ]));
        } else {
            $lastPunishments = PlayerPunishment::with([
                'country:id,name,iso_code',
                'victimPlayer:id,uuid,username,skin_texture_id',
                'creatorPlayer:id,uuid,username,skin_texture_id',
                'removerPlayer:id,uuid,username,skin_texture_id',
            ])
                ->where('ip_address', 'LIKE', $playerPunishment->ip_address)
                ->where('id', '!=', $playerPunishment->id)
                ->orderByDesc('start_at')
                ->simplePaginate($perPage)
                ->through(fn($punishment) => $punishment->makeVisibleIf($canViewCritical, [
                    'ip_address',
                    'plugin_punishment_id',
                    'origin_server_name',
                ]));
        }

        return $lastPunishments;
    }

    public function indexLastSessions(PlayerPunishment $playerPunishment)
    {
        $this->authorize('viewAnyIntel', Player::class);
        $canViewCritical = Gate::allows('viewCritical', PlayerPunishment::class);

        $perPage = request()->query('perPage', 5);
        if ($perPage > 100) {
            $perPage = 100;
        }

        if ($playerPunishment->uuid) {
            $pastSessions = MinecraftPlayerSession::with(['country:id,name,iso_code', 'server:id,name'])
                ->where('player_uuid', $playerPunishment->uuid)
                ->where('session_started_at', '<=', $playerPunishment->start_at)
                ->orderByDesc('session_started_at')
                ->simplePaginate($perPage)
                ->through(fn($session) => $session->makeVisibleIf($canViewCritical, 'player_ip_address'));
        } else {
            $pastSessions = MinecraftPlayerSession::with(['country:id,name,iso_code', 'server:id,name'])
                ->where('player_ip_address', 'LIKE', $playerPunishment->ip_address)
                ->where('session_started_at', '<=', $playerPunishment->start_at)
                ->orderByDesc('session_started_at')
                ->simplePaginate($perPage)
                ->through(fn($session) => $session->makeVisibleIf($canViewCritical, 'player_ip_address'));
        }

        return $pastSessions;
    }

    public function indexAlts(PlayerPunishment $playerPunishment)
    {
        $this->authorize('viewAlts', $playerPunishment);
        $canViewCritical = Gate::allows('viewCritical', PlayerPunishment::class);

        $perPage = request()->query('perPage', 5);
        if ($perPage > 100) {
            $perPage = 100;
        }

        if (!$playerPunishment->ip_address) {
            return (object) [
                'data' => [],
            ];
        }

        $firstTwoOctets = explode('.', $playerPunishment->ip_address);
        $firstTwoOctets = $firstTwoOctets[0] . '.' . $firstTwoOctets[1] . '.%';
        $altUuids = MinecraftPlayerSession::distinct()->select('player_uuid')
            ->where('player_uuid', '!=', $playerPunishment->uuid)
            ->where('player_ip_address', 'LIKE', $firstTwoOctets)
            ->pluck('player_uuid');
        $players = Player::select(['id', 'uuid', 'username', 'skin_texture_id', 'first_seen_at', 'last_seen_at', 'country_id', 'ip_address', 'play_time'])
            ->whereIn('uuid', $altUuids)
            ->with('country:id,name,iso_code')
            ->withCount('punishments')
            ->orderByDesc('last_seen_at')
            ->simplePaginate($perPage)
            ->through(fn($player) => $player->makeVisibleIf($canViewCritical, 'ip_address'));

        return $players;
    }
}
