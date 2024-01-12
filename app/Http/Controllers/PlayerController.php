<?php

namespace App\Http\Controllers;

use App\Models\MinecraftPlayer;
use App\Models\Player;
use App\Models\Rank;
use App\Models\Server;
use App\Services\MinecraftApiService;
use App\Settings\PlayerSettings;
use DB;
use Exception;
use Gate;
use Http;
use Illuminate\Http\Request;
use Image;
use Inertia\Inertia;

class PlayerController extends Controller
{
    public function index(Request $request, PlayerSettings $playerSettings): \Illuminate\Http\JsonResponse|\Inertia\Response
    {
        $players = Player::select(['id', 'username', 'rating', 'position', 'total_score', 'uuid', 'play_time', 'last_seen_at', 'first_seen_at', 'rank_id', 'country_id'])
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
        $player->servers_count = MinecraftPlayer::where('player_uuid', $player->uuid)->count();

        // Favorite Server
        $player->favorite_server = MinecraftPlayer::where('player_uuid', $player->uuid)
            ->orderByDesc('play_time')->first()?->server->only(['name', 'hostname']);

        // Owner if any
        $player->owner = $player->users()->first()?->only(['id', 'username']);

        // Can show player intel
        $canShowPlayerIntel = Gate::allows('viewIntel', $player);

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
        ]);

        return Inertia::render('Player/ShowPlayer', [
            'player' => $player,
            'canShowPlayerIntel' => $canShowPlayerIntel,
        ]);
    }

    public function getAvatarImage(Request $request, $uuid, $username = null)
    {
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $fetchAvatarFromUrlUsingCurl = config('minetrax.fetch_avatar_from_url_using_curl');
        $param = $useUsernameForSkins ? $username : $uuid;
        $size = $request->size ?? 100;

        // If we got invalid uuid, and we are not using username for skins, return alex
        if (! $useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = Image::make(public_path('images/alex.png'))->resize($size, $size);

            return $img->response('jpg');
        }

        try {
            $img = Image::cache(function ($image) use ($param, $size, $fetchAvatarFromUrlUsingCurl) {
                // try getting from third party service
                $url = "https://minotar.net/avatar/$param";
                if ($size) {
                    $url = "https://minotar.net/avatar/{$param}/{$size}";
                }
                $data = $fetchAvatarFromUrlUsingCurl ? Http::get($url)->body() : $url;

                return $image->make($data);
            }, 60, true); // Cache lifetime is in minutes
        } catch (Exception $exception) {
            try {
                $img = Image::cache(function ($image) use ($param, $size, $useUsernameForSkins, $fetchAvatarFromUrlUsingCurl) {
                    // try getting from third party service
                    if ($useUsernameForSkins) {
                        $uuid = MinecraftApiService::playerUsernameToUuid($param);
                    } else {
                        $uuid = $param;
                    }
                    $url = 'https://crafatar.com/avatars/'.$uuid.'?size='.$size;
                    $data = $fetchAvatarFromUrlUsingCurl ? Http::get($url)->body() : $url;

                    return $image->make($data);
                }, 60, true); // Cache lifetime is in minutes
            } catch (Exception $exception) {
                $img = Image::make(public_path('images/alex.png'))->resize($size, $size);
            }
        }

        return $img->response('jpg');
    }

    public function getSkinImage(Request $request, $uuid, $username = null)
    {
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $fetchAvatarFromUrlUsingCurl = config('minetrax.fetch_avatar_from_url_using_curl');
        $param = $useUsernameForSkins ? $username : $uuid;

        // If we got invalid uuid, and we are not using username for skins, return alex
        if (! $useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = Image::make(public_path('images/alex_skin.png'));

            return $img->response('jpg');
        }

        try {
            $img = Image::cache(function ($image) use ($param, $fetchAvatarFromUrlUsingCurl) {
                // try getting from third party service
                $url = "https://minotar.net/skin/$param";
                $data = $fetchAvatarFromUrlUsingCurl ? Http::get($url)->body() : $url;

                return $image->make($data);
            }, 60, true); // Cache lifetime is in minutes
        } catch (Exception $exception) {
            try {
                $img = Image::cache(function ($image) use ($param, $useUsernameForSkins, $fetchAvatarFromUrlUsingCurl) {
                    // try getting from third party service
                    if ($useUsernameForSkins) {
                        $uuid = MinecraftApiService::playerUsernameToUuid($param);
                    } else {
                        $uuid = $param;
                    }
                    $url = 'https://crafatar.com/skins/'.$uuid;
                    $data = $fetchAvatarFromUrlUsingCurl ? Http::get($url)->body() : $url;

                    return $image->make($data);
                }, 60, true); // Cache lifetime is in minutes
            } catch (Exception $exception) {
                $img = Image::make(public_path('images/alex_skin.png'));
            }
        }

        return $img->response('png');
    }

    public function getRenderImage(Request $request, $uuid, $username = null)
    {
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $fetchAvatarFromUrlUsingCurl = config('minetrax.fetch_avatar_from_url_using_curl');
        $param = $useUsernameForSkins ? $username : $uuid;
        $scale = $request->scale;

        // If we got invalid uuid, and we are not using username for skins, return alex
        if (! $useUsernameForSkins && $uuid === '00000000-0000-0000-0000-000000000000') {
            $img = Image::make(public_path('images/alex_render.png'));

            return $img->response('jpg');
        }

        try {
            $img = Image::cache(function ($image) use ($param, $scale, $useUsernameForSkins, $fetchAvatarFromUrlUsingCurl) {
                // try getting from third party service
                if ($useUsernameForSkins) {
                    $uuid = MinecraftApiService::playerUsernameToUuid($param);
                } else {
                    $uuid = $param;
                }

                $url = 'https://crafatar.com/renders/body/'.$uuid.'?scale='.$scale;
                $data = $fetchAvatarFromUrlUsingCurl ? Http::get($url)->body() : $url;

                return $image->make($data);
            }, 60, true); // Cache lifetime is in minutes
        } catch (Exception $exception) {
            $img = Image::make(public_path('images/alex_render.png'));
        }

        return $img->response('png');
    }
}
