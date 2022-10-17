<?php

namespace App\Http\Controllers;

use App\Models\JsonMinecraftPlayerStat;
use App\Models\Player;
use App\Models\Rank;
use App\Models\Server;
use App\Services\MinecraftApiService;
use DB;
use Exception;
use Illuminate\Http\Request;
use Image;
use Inertia\Inertia;

class PlayerController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse|\Inertia\Response
    {
        $players = Player::select(['id', 'username', 'rating', 'position', 'total_score', 'uuid', 'total_play_one_minute', 'last_seen_at', 'first_seen_at', 'rank_id', 'country_id'])
            ->with(['country:id,iso_code,flag,name', 'rank:id,shortname,name'])
            ->orderBy(DB::raw('-`position`'), 'desc')                   // this sort with position but excludes the nulls
            ->simplePaginate(15);

        if ($request->wantsJson()) {
            return response()->json($players);
        }

        $totalPlayersCount = Player::count();
        $activePlayersCount = Player::where('last_seen_at', '>=', now()->subDays(30))->count();
        $totalPlayTime = Player::sum('total_play_one_minute');
        $lastScanAt = Server::orderByDesc('last_scanned_at')->first()?->last_scanned_at;

        return Inertia::render('Player/IndexPlayer', [
            'players' => $players,
            'totalPlayersCount' => $totalPlayersCount,
            'activePlayersCount' => $activePlayersCount,
            'totalPlayTime' => $totalPlayTime,
            'lastScanAt' => $lastScanAt
        ]);
    }

    public function show($player)
    {
        $player = Player::where('uuid', $player)->orWhere('username', $player)
            ->with(['rank:id,shortname,name', 'country:id,name,iso_code,flag'])->firstOrFail();

        // next rank
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

        // Servers Count
        $player->servers_count = JsonMinecraftPlayerStat::where('uuid', $player->uuid)->count();

        // Favorite Server
        $player->favorite_server = JsonMinecraftPlayerStat::where('uuid', $player->uuid)
            ->orderByDesc('total_play_one_minute')->first()?->server->only(['name', 'hostname']);

        // Owner if any
        $player->owner = $player->users()->first()?->only(['id', 'username']);

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
            'total_dropped',
            'total_broken',
            'total_crafted',
            'total_mob_kills',
            'total_player_kills',
            'total_deaths',
            'total_walk_one_cm',
            'total_play_one_minute',
            'total_sleep_in_bed',
            'total_leave_game',
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
        ]);

        return Inertia::render('Player/ShowPlayer', [
            'player' => $player,
        ]);
    }

    public function getAvatarImage($uuid, $username, Request $request)
    {
        $useUsernameForSkins = config("minetrax.use_username_for_skins");
        $param = $useUsernameForSkins ? $username : $uuid;
        $size = $request->size;

        try {
            $img = Image::cache(function ($image) use ($param, $size) {
                // try getting from third party service
                $url = "https://minotar.net/avatar/$param";
                if ($size) $url = "https://minotar.net/avatar/{$param}/{$size}";
                return $image->make($url);
            }, 60, true);   // Cache lifetime is in minutes
        } catch (Exception $exception) {
            try {
                $img = Image::cache(function ($image) use ($param, $size, $useUsernameForSkins) {
                    // try getting from third party service
                    if ($useUsernameForSkins) {
                        $uuid = MinecraftApiService::playerUsernameToUuid($param);
                    } else {
                        $uuid = $param;
                    }
                    return $image->make('https://crafatar.com/avatars/' . $uuid . '?size=' . $size);
                }, 60, true);   // Cache lifetime is in minutes
            } catch (Exception $exception) {
                $img = Image::make(public_path('images/alex.png'))->resize($size, $size);
            }
        }

        return $img->response('jpg');
    }


    public function getSkinImage($uuid, $username, Request $request)
    {
        $useUsernameForSkins = config("minetrax.use_username_for_skins");
        $param = $useUsernameForSkins ? $username : $uuid;

        try {
            $img = Image::cache(function ($image) use ($param) {
                // try getting from third party service
                $url = "https://minotar.net/skin/$param";
                return $image->make($url);
            }, 60, true);   // Cache lifetime is in minutes
        } catch (Exception $exception) {
            try {
                $img = Image::cache(function ($image) use ($param, $useUsernameForSkins) {
                    // try getting from third party service
                    if ($useUsernameForSkins) {
                        $uuid = MinecraftApiService::playerUsernameToUuid($param);
                    } else {
                        $uuid = $param;
                    }
                    return $image->make('https://crafatar.com/skins/' . $uuid);
                }, 60, true);   // Cache lifetime is in minutes
            } catch (Exception $exception) {
                $img = Image::make(public_path('images/alex_skin.png'));
            }
        }

        return $img->response('png');
    }


    public function getRenderImage($uuid, $username, Request $request)
    {
        $useUsernameForSkins = config("minetrax.use_username_for_skins");
        $param = $useUsernameForSkins ? $username : $uuid;
        $scale = $request->scale;

        try {
            $img = Image::cache(function ($image) use ($param, $scale, $useUsernameForSkins) {
                // try getting from third party service
                if ($useUsernameForSkins) {
                    $uuid = MinecraftApiService::playerUsernameToUuid($param);
                } else {
                    $uuid = $param;
                }
                return $image->make('https://crafatar.com/renders/body/' . $uuid . '?scale=' . $scale);
            }, 60, true);   // Cache lifetime is in minutes
        } catch (Exception $exception) {
            $img = Image::make(public_path('images/alex_render.png'));
        }

        return $img->response('png');
    }
}
