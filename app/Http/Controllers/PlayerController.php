<?php

namespace App\Http\Controllers;

use App\Models\MinecraftPlayer;
use App\Models\Player;
use App\Models\Rank;
use App\Queries\Filters\FilterMultipleFields;
use App\Settings\PlayerSettings;
use App\Settings\PluginSettings;
use App\Utils\Helpers\MinecraftSkinUtils;
use DB;
use Exception;
use Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class PlayerController extends Controller
{
    public function index(Request $request, PlayerSettings $playerSettings): \Illuminate\Http\JsonResponse|\Inertia\Response
    {
        $perPage = $request->input('perPage', 15);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'username',
            'rating',
            'position',
            'total_score',
            'uuid',
            'play_time',
            'last_seen_at',
            'first_seen_at',
            'rank_id',
            'country_id',
            'skin_texture_id'
        ];

        $positionSortWithoutNullsFirst = AllowedSort::callback('position', new class {
            public function __invoke(Builder $query)
            {
                return $query->orderBy(DB::raw('-`position`'), 'desc');
            }
        });
        $players = QueryBuilder::for(Player::class)
            ->select($fields)
            ->with(['country:id,iso_code,flag,name', 'rank:id,shortname,name'])
            ->allowedFilters([
                ...$fields,
                'country.name',
                'rank.name',
                'rank.shortname',
                AllowedFilter::custom('q', new FilterMultipleFields(['username', 'uuid'])),
            ])
            ->allowedSorts([
                'id',
                'username',
                'rating',
                'total_score',
                'play_time',
                'last_seen_at',
                'first_seen_at',
                $positionSortWithoutNullsFirst
            ])
            ->defaultSort($positionSortWithoutNullsFirst)
            ->simplePaginate($perPage)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($players);
        }

        $cacheFor = 10 * 60; // 10 minutes
        $stats = fn() => Cache::remember('player.index::stats', $cacheFor, function () use ($playerSettings) {
            $playerActiveLastDay = $playerSettings->last_seen_day_for_active == -1 ? now()->subYears(100) : now()->subDays($playerSettings->last_seen_day_for_active);
            $totalPlayersCount = Player::fastCount();
            $activePlayersCount = Player::where('last_seen_at', '>=', $playerActiveLastDay)->count();
            $totalPlayTime = Player::sum('play_time');
            $totalLinkedPlayers = Player::whereHas('users')->count();

            return [
                'totalPlayersCount' => $totalPlayersCount,
                'activePlayersCount' => $activePlayersCount,
                'totalPlayTime' => $totalPlayTime,
                'totalLinkedPlayers' => $totalLinkedPlayers ?? 0,
            ];
        });

        return Inertia::render('Player/IndexPlayer', [
            'players' => $players,
            'stats' => Inertia::defer($stats),
        ]);
    }

    public function show($player, Request $request, PluginSettings $pluginSettings)
    {
        $player = Player::where('uuid', $player)->orWhere('username', $player)
            ->with(['rank:id,shortname,name', 'country:id,name,iso_code,flag'])->firstOrFail();

        // next rank
        $hideNextRank = config('minetrax.hide_player_next_rank');
        if ($hideNextRank) {
            $player->next_rank = null;
        } else {
            $currentRank = $player->rank()->first();
            if ($currentRank) {
                $player->next_rank = Rank::select(['id', 'shortname', 'name'])
                    ->where('weight', '>=', $currentRank->weight)
                    ->where('id', '!=', $currentRank->id)
                    ->orderBy('weight')
                    ->first()?->name;
            } else {
                $player->next_rank = Rank::select(['id', 'shortname', 'name'])->orderBy('weight')->first()?->name;
            }
        }

        // Servers Count
        $player->servers_count = MinecraftPlayer::where('player_uuid', $player->uuid)->count();

        // Favorite Server
        $player->favorite_server = MinecraftPlayer::where('player_uuid', $player->uuid)
            ->orderByDesc('play_time')->first()?->server->only(['name', 'hostname']);

        // Owner if any
        $player->owner = $player->users()->first()?->only(['id', 'username']);

        // Can show player intel
        $canShowPlayerIntel = Gate::allows('viewIntel', $player);

        // Can change player skin
        $canPlayerChangeSkin = $request->user() && Gate::allows('changeSkin', $player);
        $playerSkinChangerEnabled = config('minetrax.player_skin_changer_enabled');

        // Can change player password
        $playerPasswordResetEnabled = $pluginSettings->enable_player_password_reset;
        $canPlayerChangePassword = $playerPasswordResetEnabled && Gate::allows('resetPassword', $player);

        // filter out stuffs that are not used
        $player = $player->only([
            'id',
            'uuid',
            'username',
            'rating',
            'total_score',
            'position',
            'total_used',
            'total_mined',
            'total_picked_up',
            'total_dropped',
            'total_broken',
            'total_crafted',
            'total_items_placed',
            'total_items_enchanted',
            'total_mob_kills',
            'total_player_kills',
            'total_deaths',
            'total_fish_caught',
            'total_sleep_in_bed',
            'total_leave_game',
            'play_time',
            'afk_time',
            'distance_traveled',
            'raids_won',
            'first_seen_at',
            'last_seen_at',
            'is_active',
            'avatar_url',
            'country',
            'rank',
            'owner',
            'favorite_server',
            'next_rank',
            'servers_count',
            'skin_texture_id',
        ]);

        return Inertia::render('Player/ShowPlayer', [
            'player' => $player,
            'canShowPlayerIntel' => $canShowPlayerIntel,
            'canChangePlayerSkin' => $playerSkinChangerEnabled && $canPlayerChangeSkin,
            'canChangePlayerPassword' => $canPlayerChangePassword,
        ]);
    }

    public function getAvatarImage(Request $request, $uuid, $username = null, $textureid = null)
    {
        $username = strtolower($username);
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $param = $useUsernameForSkins ? $username : $uuid;
        $param = $username ?: $uuid;
        $size = $request->size ?? 100;

        // If we got invalid uuid, and we are not using username for skins, return alex
        if (!$useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = MinecraftSkinUtils::getDefaultSkinImage('avatar', $size);

            return $this->streamImage($img);
        }

        try {
            $mcHeadIdentifier = $textureid ?? $param;
            $img = MinecraftSkinUtils::getSkinImageFromMcHeads('avatar', $mcHeadIdentifier, $size);
        } catch (Exception $e) {
            try {
                $img = MinecraftSkinUtils::getSkinImageFromMinotar('avatar', $param, $size);
            } catch (Exception $exception) {
                try {
                    $img = MinecraftSkinUtils::getSkinImageFromCrafatar('avatar', $param, $size);
                } catch (Exception $exception) {
                    $img = MinecraftSkinUtils::getDefaultSkinImage('avatar', $size);
                }
            }
        }

        return $this->streamImage($img);
    }

    public function getSkinImage(Request $request, $uuid, $username = null, $textureid = null)
    {
        $username = strtolower($username);
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $param = $useUsernameForSkins ? $username : $uuid;

        // If we got invalid uuid, and we are not using username for skins, return alex
        if (!$useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = MinecraftSkinUtils::getDefaultSkinImage('skin');

            return $this->streamImage($img, 'png');
        }

        try {
            $mcHeadIdentifier = $textureid ?? $param;
            $img = MinecraftSkinUtils::getSkinImageFromMcHeads('skin', $mcHeadIdentifier);
        } catch (Exception $e) {
            try {
                $img = MinecraftSkinUtils::getSkinImageFromMinotar('skin', $param);
            } catch (Exception $exception) {
                try {
                    $img = MinecraftSkinUtils::getSkinImageFromCrafatar('skin', $param);
                } catch (Exception $exception) {
                    $img = MinecraftSkinUtils::getDefaultSkinImage('skin');
                }
            }
        }

        return $this->streamImage($img, 'png');
    }

    public function getRenderImage(Request $request, $uuid, $username = null, $textureid = null)
    {
        $username = strtolower($username);
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $param = $useUsernameForSkins ? $username : $uuid;
        $scale = $request->scale;

        // If we got invalid uuid, and we are not using username for skins, return alex
        if (!$useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = MinecraftSkinUtils::getDefaultSkinImage('render');

            return $this->streamImage($img, 'png');
        }

        try {
            $mcHeadIdentifier = $textureid ?? $param;
            $img = MinecraftSkinUtils::getSkinImageFromMcHeads('render', $mcHeadIdentifier, $scale);
        } catch (Exception $e) {
            try {
                $img = MinecraftSkinUtils::getSkinImageFromCrafatar('render', $param, $scale);
            } catch (Exception $exception) {
                $img = MinecraftSkinUtils::getDefaultSkinImage('render');
            }
        }

        return $this->streamImage($img, 'png');
    }

    private function streamImage($img, $imageType = 'jpeg')
    {
        $contentType = "image/{$imageType}";
        return response()->stream(function () use ($img, $imageType) {
            if ($imageType == 'png') {
                echo $img->toPng();
            } else {
                echo $img->toJpeg();
            }
        }, 200, ['Content-Type' => $contentType]);
    }
}
