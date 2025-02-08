<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServerType;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\MinecraftPlayer;
use App\Models\Player;
use App\Models\Server;
use App\Queries\Filters\FilterMultipleFields;
use App\Settings\PluginSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PlayerIntelController extends Controller
{
    public function playersList(Request $request, PluginSettings $pluginSettings)
    {
        $this->authorize('viewAnyIntel', Player::class);

        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
        ]);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $serverList = Server::select(['id', 'name'])
            ->where('type', '!=', ServerType::Bungee())
            ->get()->pluck('name', 'id');

        $selectedServers = $request->query('servers') ?? null; // list of selected server ids

        $data = QueryBuilder::for(MinecraftPlayer::class)
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->selectRaw('player_id')
            ->selectRaw('MAX(id) as id')
            ->selectRaw('MIN(country_id) as country_id')
            ->selectRaw('COUNT(*) as server_play_count')
            ->selectRaw('SUM(mob_kills) as mob_kills')
            ->selectRaw('SUM(player_kills) as player_kills')
            ->selectRaw('SUM(deaths) as deaths')
            ->selectRaw('SUM(play_time) as play_time')
            ->selectRaw('SUM(afk_time) as afk_time')
            ->selectRaw('MIN(first_seen_at) as first_seen_at')
            ->selectRaw('MAX(last_seen_at) as last_seen_at')
            ->selectRaw('MAX(last_minecraft_version) as last_minecraft_version')
            ->selectRaw('MAX(last_join_address) as last_join_address')
            ->selectRaw('SUM(vault_balance) as vault_balance')
            ->selectRaw('MAX(player_username) as player_username')
            ->allowedFilters([
                'player_uuid',
                'player_username',
                'country_id',
                'last_seen_at',
                'first_seen_at',
                'last_join_address',
                'last_minecraft_version',
                'country.name',
                AllowedFilter::custom('q', new FilterMultipleFields(['player_uuid', 'player_username'])),
            ])
            ->groupBy(['player_id'])
            ->with(['player:id,uuid,username,skin_texture_id', 'country:id,iso_code,flag,name'])
            ->allowedSorts([
                'id',
                'player_username',
                'country_id',
                'last_seen_at',
                'first_seen_at',
                'server_play_count',
                'mob_kills',
                'player_kills',
                'deaths',
                'play_time',
                'afk_time',
                'last_minecraft_version',
                'last_join_address',
                'vault_balance',
            ])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->user()->cannot('view player_intel_critical')) {
            $data->makeHidden([
                'last_join_address',
                'vault_balance',
                'last_minecraft_version'
            ]);
        }

        $countries = Country::select(['id', 'name'])->get()->pluck('name');

        // Password Reset Feature Enabled
        $passwordResetEnabled = $pluginSettings->enable_player_password_reset;
        $canResetAnyPlayerPassword = $request->user()->can('reset any_player_password');

        // Skin Change Feature Enabled
        $skinChangeEnabled = config('minetrax.player_skin_changer_enabled');
        $canChangeAnyPlayerSkin = $request->user()->can('change any_player_skin');

        return Inertia::render('Admin/PlayerIntel/PlayersList', [
            'filters' => request()->all(['servers']),
            'serverList' => $serverList,
            'data' => $data,
            'countries' => $countries,
            'canResetAnyPlayerPassword' => $canResetAnyPlayerPassword && $passwordResetEnabled,
            'canChangeAnyPlayerSkin' => $canChangeAnyPlayerSkin && $skinChangeEnabled,
        ]);
    }
}
