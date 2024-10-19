<?php

namespace App\Http\Controllers;

use App\Models\MinecraftPlayer;
use App\Models\Player;
use App\Models\Rank;
use App\Models\Server;
use App\Settings\PlayerSettings;
use App\Utils\Helpers\MinecraftSkinUtils;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlayerController extends Controller
{
    public function index(Request $request, PlayerSettings $playerSettings): \Illuminate\Http\JsonResponse|\Inertia\Response
    {
        $players = Player::select(['id', 'username', 'rating', 'position', 'total_score', 'uuid', 'play_time', 'last_seen_at', 'first_seen_at', 'rank_id', 'country_id', 'skin_texture_id'])
            ->with(['country:id,iso_code,flag,name', 'rank:id,shortname,name'])
            ->orderBy(DB::raw('-`position`'), 'desc') // this sort with position but excludes the nulls
            ->orderByDesc('rating')
            ->orderByDesc('total_score')
            ->simplePaginate(15);

        if ($request->wantsJson()) {
            return response()->json($players);
        }

        $playerActiveLastDay = $playerSettings->last_seen_day_for_active == -1 ? now()->subYears(100) : now()->subDays($playerSettings->last_seen_day_for_active);

        $totalPlayersCount = Player::fastCount();
        $activePlayersCount = Player::where('last_seen_at', '>=', $playerActiveLastDay)->count();
        $totalPlayTime = Player::sum('play_time');

        return Inertia::render('Player/IndexPlayer', [
            'players' => $players,
            'totalPlayersCount' => $totalPlayersCount,
            'activePlayersCount' => $activePlayersCount,
            'totalPlayTime' => $totalPlayTime,
        ]);
    }

    public function show($player, Request $request)
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
        if (! $useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = MinecraftSkinUtils::getDefaultSkinImage('avatar', $size);

            return $img->response('jpg');
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

        return $img->response('jpg');
    }

    public function getSkinImage(Request $request, $uuid, $username = null, $textureid = null)
    {
        $username = strtolower($username);
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $param = $useUsernameForSkins ? $username : $uuid;

        // If we got invalid uuid, and we are not using username for skins, return alex
        if (! $useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = MinecraftSkinUtils::getDefaultSkinImage('skin');

            return $img->response('jpg');
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

        return $img->response('png');
    }

    public function getRenderImage(Request $request, $uuid, $username = null, $textureid = null)
    {
        $username = strtolower($username);
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $param = $useUsernameForSkins ? $username : $uuid;
        $scale = $request->scale;

        // If we got invalid uuid, and we are not using username for skins, return alex
        if (! $useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = MinecraftSkinUtils::getDefaultSkinImage('render');

            return $img->response('jpg');
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

        return $img->response('png');
    }
}
