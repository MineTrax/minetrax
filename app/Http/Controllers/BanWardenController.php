<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePunishmentInsightsJob;
use App\Jobs\PardonPlayerPunishmentJob;
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
        $canShowMaskedIp = config('minetrax.banwarden.show_masked_ip_public') ? true : auth()?->user()?->isStaffMember();

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
                    'victimPlayer.username',
                ])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-id')
            ->simplePaginate(perPage: $perPage)
            ->through(fn($punishment) => $punishment->makeVisibleIf($canViewCritical, [
                'ip_address',
                'plugin_punishment_id',
                'origin_server_name',
            ])->makeHiddenIf(!$canShowMaskedIp, ['masked_ip_address']))
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
        $canShowMaskedIp = config('minetrax.banwarden.show_masked_ip_public') ? true : auth()?->user()?->isStaffMember();

        $playerPunishment->load([
            'country:id,name,iso_code',
            'victimPlayer:id,uuid,username,skin_texture_id',
            'creatorPlayer:id,uuid,username,skin_texture_id',
            'removerPlayer:id,uuid,username,skin_texture_id',
        ]);

        // Generate insights if enabled and not already generated
        $insightEnabled = config('minetrax.banwarden.ai_insights_enabled');
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
        $canViewEvidence = Gate::allows('viewEvidence', PlayerPunishment::class);
        if ($canViewEvidence) {
            $playerPunishment->append('evidences');
        }
        if (!$canShowMaskedIp) {
            $playerPunishment->makeHidden('masked_ip_address');
        }
        $allowControlFromWeb = config('minetrax.banwarden.allow_control_from_web');
        $response = [
            'punishment' => $playerPunishment,
            'permissions' => [
                'canViewSessions' => Gate::allows('viewAnyIntel', Player::class),
                'canViewAlts' => Gate::allows('viewAlts', $playerPunishment),
                'canViewCritical' => $canViewCritical,
                'canViewEvidence' => $canViewEvidence,
                'canCreateEvidence' => Gate::allows('createEvidence', PlayerPunishment::class)
                    && $playerPunishment->evidences->count() < config('minetrax.banwarden.evidence_max_count'),
                'canDeleteEvidence' => Gate::allows('deleteEvidence', PlayerPunishment::class),
                'canPardon' => $allowControlFromWeb && Gate::allows('delete', $playerPunishment),
            ],
        ];

        return Inertia::render('BanWarden/ShowPunishment', $response);
    }

    public function indexLastPunishments(PlayerPunishment $playerPunishment)
    {
        $this->authorize('view', $playerPunishment);
        $canViewCritical = Gate::allows('viewCritical', PlayerPunishment::class);
        $canShowMaskedIp = config('minetrax.banwarden.show_masked_ip_public') ? true : auth()?->user()?->isStaffMember();

        $perPage = request()->query('perPage', 5);
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
                ])->makeHiddenIf(!$canShowMaskedIp, ['masked_ip_address']));
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


    public function showEvidence(PlayerPunishment $playerPunishment, $evidence, Request $request)
    {
        $this->authorize('viewEvidence', $playerPunishment);

        $media = $playerPunishment->getMedia('punishment-evidence')->find($evidence);
        if (!$media) {
            abort(404);
        }

        return $media->toInlineResponse($request);
    }

    public function createEvidence(PlayerPunishment $playerPunishment, Request $request)
    {
        $this->authorize('createEvidence', PlayerPunishment::class);

        $evidenceAllowedMimetypes = config('minetrax.banwarden.evidence_allowed_mimetypes');
        $evidenceMaxSizeKb = config('minetrax.banwarden.evidence_max_size_kb');
        $evidenceMaxCount = config('minetrax.banwarden.evidence_max_count');
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:' . $evidenceAllowedMimetypes,
                'max:' . $evidenceMaxSizeKb,
            ]
        ]);

        $previousEvidences = $playerPunishment->evidences;
        if ($previousEvidences->count() >= $evidenceMaxCount) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'error', 'title' => __('Upload Failed'), 'body' => __('You can only upload up to :count evidence files', ['count' => $evidenceMaxCount])]]);
        }

        $playerPunishment->addMediaFromRequest('file')->toMediaCollection('punishment-evidence');

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Upload Successful'), 'body' => __('Evidence uploaded successfully')]]);
    }

    public function deleteEvidence(PlayerPunishment $playerPunishment, $evidence)
    {
        $this->authorize('deleteEvidence', PlayerPunishment::class);

        $media = $playerPunishment->getMedia('punishment-evidence')->find($evidence);
        if (!$media) {
            abort(404);
        }

        $media->delete();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Delete Successful'), 'body' => __('Evidence deleted successfully')]]);
    }

    public function pardon(PlayerPunishment $playerPunishment, Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $this->authorize('delete', $playerPunishment);

        try {
            PardonPlayerPunishmentJob::dispatchSync($playerPunishment, $request->input('reason'));
        } catch (\Exception $e) {
            \Log::error($e);
            return redirect()->back()
                ->with(['toast' => ['type' => 'error', 'title' => __('Pardon Failed'), 'body' => 'Failed to execute pardon job to due webquery issue.']]);
        }

        $playerPunishment->removed_by = auth()->id();
        $playerPunishment->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Pardon Successful'), 'body' => __('Reloading in 5 seconds to reflect changes'), 'milliseconds' => 5000]]);
    }
}
