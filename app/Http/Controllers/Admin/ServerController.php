<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServerType;
use App\Enums\ServerVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateServerRequest;
use App\Http\Requests\UpdateServerRequest;
use App\Jobs\CalculatePlayersJob;
use App\Jobs\DeleteServerJob;
use App\Models\MinecraftPlayer;
use App\Models\Server;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\GeolocationService;
use App\Settings\PluginSettings;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ServerController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Server::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $canCreateBungeeServer = Server::where('type', ServerType::Bungee)->exists();

        $servers = QueryBuilder::for(Server::class)
            ->select([
                'id',
                'name',
                'hostname',
                'ip_address',
                'join_port',
                'query_port',
                'webquery_port',
                'type',
                'minecraft_version',
                'order',
                'country_id',
                'last_scanned_at',
                'settings',
                'created_at',
            ])
            ->with('country')
            ->allowedFilters([
                'id',
                'name',
                'hostname',
                'ip_address',
                'join_port',
                'query_port',
                'webquery_port',
                'type',
                'minecraft_version',
                'order',
                'country_id',
                'last_scanned_at',
                'created_at',
                AllowedFilter::custom('q', new FilterMultipleFields(['name', 'hostname', 'ip_address', 'join_port', 'query_port', 'webquery_port', 'minecraft_version'])),
            ])
            ->allowedSorts(['id', 'name', 'hostname', 'ip_address', 'join_port', 'query_port', 'webquery_port', 'type', 'minecraft_version', 'order', 'country_id', 'last_scanned_at', 'created_at'])
            ->defaultSort('-order')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Server/IndexServer', [
            'servers' => $servers,
            'canCreateBungeeServer' => ! $canCreateBungeeServer,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Server::class);

        return Inertia::render('Admin/Server/CreateServer', [
            'versionsArray' => ServerVersion::getValues(),
        ]);
    }

    public function createBungee()
    {
        $alreadyHasBungee = Server::where('type', ServerType::Bungee)->exists();
        if ($alreadyHasBungee) {
            abort(403);
        }

        $this->authorize('create', Server::class);

        return Inertia::render('Admin/Server/CreateEditBungeeServer', [
            'versionsArray' => ServerVersion::getValues(),
        ]);
    }

    public function store(CreateServerRequest $request, GeolocationService $geolocationService, PluginSettings $pluginSettings)
    {
        $countryId = $geolocationService->getCountryIdFromIP(gethostbyname($request->ip_address));

        $server = Server::create([
            'ip_address' => $request->ip_address,
            'join_port' => $request->join_port,
            'query_port' => $request->query_port,
            'webquery_port' => $request->webquery_port,
            'hostname' => $request->hostname,
            'name' => $request->name,
            'minecraft_version' => $request->minecraft_version,
            'type' => $request->type,
            'country_id' => $countryId,
            'created_by' => $request->user()->id,
            'settings' => $request->settings,
            'is_server_intel_enabled' => $request->is_server_intel_enabled,
            'is_player_intel_enabled' => $request->is_player_intel_enabled,
            'is_ingame_chat_enabled' => $request->is_ingame_chat_enabled,
            'order' => $request->order,
        ]);

        return Inertia::render('Admin/Server/AfterCreateSteps', [
            'server' => $server,
            'apiKey' => $pluginSettings->plugin_api_key,
            'apiSecret' => $pluginSettings->plugin_api_secret,
            'apiHost' => config('app.url'),
        ])->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('New server added successfully')]]);
    }

    public function storeBungee(Request $request, GeolocationService $geolocationService, PluginSettings $pluginSettings)
    {
        $alreadyHasBungee = Server::where('type', ServerType::Bungee)->exists();
        if ($alreadyHasBungee) {
            abort(403);
        }

        $this->authorize('create', Server::class);

        $request->validate([
            'hostname' => 'required',
            'ip_address' => 'required|ip',
            'join_port' => 'required|numeric|min:0|max:65535',
            'query_port' => 'required|numeric|min:0|max:65535',
            'webquery_port' => 'nullable|numeric|min:0|max:65535|required_if_accepted:is_server_intel_enabled|different:join_port',
            'name' => 'required',
            'minecraft_version' => ['required', new EnumValue(ServerVersion::class)],
            'is_server_intel_enabled' => 'required|boolean',
            'settings' => 'sometimes',
            'settings.server_identifier' => 'nullable|alpha_dash',
            'settings.is_skin_change_via_web_allowed' => 'required|boolean',
            'settings.is_banwarden_enabled' => 'required|boolean',
            'order' => 'nullable|numeric',
        ]);

        $countryId = $geolocationService->getCountryIdFromIP($request->ip_address);

        $server = Server::create([
            'ip_address' => $request->ip_address,
            'join_port' => $request->join_port,
            'query_port' => $request->query_port,
            'webquery_port' => $request->webquery_port,
            'hostname' => $request->hostname,
            'name' => $request->name,
            'minecraft_version' => $request->minecraft_version,
            'type' => ServerType::Bungee,
            'country_id' => $countryId,
            'is_server_intel_enabled' => $request->is_server_intel_enabled,
            'settings' => $request->settings,
            'created_by' => $request->user()->id,
            'order' => $request->order,
        ]);

        if (! $request->webquery_port) {
            return redirect()->route('admin.server.index')
                ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Proxy server added successfully')]]);
        }

        return Inertia::render('Admin/Server/AfterCreateSteps', [
            'server' => $server,
            'apiKey' => $pluginSettings->plugin_api_key,
            'apiSecret' => $pluginSettings->plugin_api_secret,
            'apiHost' => config('app.url'),
        ])->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Proxy server added successfully')]]);
    }

    public function show(Server $server)
    {
        $this->authorize('view', $server);

        $serverAggrData = [];
        $serverAggrData['pvp_kills'] = (string) MinecraftPlayer::where('server_id', $server->id)->sum('player_kills');
        $serverAggrData['mob_kills'] = (string) MinecraftPlayer::where('server_id', $server->id)->sum('mob_kills');
        $serverAggrData['player_deaths'] = (string) MinecraftPlayer::where('server_id', $server->id)->sum('deaths');
        $serverAggrData['player_damage_taken'] = (string) MinecraftPlayer::where('server_id', $server->id)->sum('pvp_damage_taken');
        $serverAggrData['total_players'] = (string) MinecraftPlayer::where('server_id', $server->id)->count();
        $serverAggrData['active_players'] = (string) MinecraftPlayer::where('server_id', $server->id)->where('last_seen_at', '>=', now()->subDays(30))->count();

        return Inertia::render('Admin/Server/ShowServer', [
            'server' => $server->load(['country']),
            'serverAggrData' => $serverAggrData,
        ]);
    }

    public function getServerConsoleLogs(Server $server, Request $request)
    {
        $afterId = $request->after;
        $this->authorize('view', $server);

        $query = $server->serverConsolelog()->orderByDesc('id')->limit(100);
        if ($afterId) {
            $query->where('id', '>', $afterId);
        }

        return $query->get();
    }

    public function showStatistics(Server $server)
    {
        $this->authorize('view', $server);

        $aggregatedTotals = $server->getAggregatedTotalJsonStats();
        $aggregatedMax = $server->getAggregatedMaxJsonStats();

        return Inertia::render('Admin/Server/ShowServerStats', [
            'server' => $server,
            'aggrMax' => $aggregatedMax,
            'aggrTotal' => $aggregatedTotals,
        ]);
    }

    public function edit(Server $server)
    {
        $this->authorize('update', $server);

        if (ServerType::Bungee()->is($server->type)) {
            return Inertia::render('Admin/Server/CreateEditBungeeServer', [
                'server' => $server,
                'versionsArray' => ServerVersion::getValues(),
            ]);
        }

        $serverData = [
            'id' => $server->id,
            'name' => $server->name,
            'join_port' => $server->join_port,
            'query_port' => $server->query_port,
            'webquery_port' => $server->webquery_port,
            'minecraft_version' => $server->minecraft_version,
            'type' => $server->type->value,
            'hostname' => $server->hostname,
            'ip_address' => $server->ip_address,
            'is_server_intel_enabled' => $server->is_server_intel_enabled,
            'is_player_intel_enabled' => $server->is_player_intel_enabled,
            'is_ingame_chat_enabled' => $server->is_ingame_chat_enabled,
            'settings' => $server->settings,
            'order' => $server->order,
        ];

        return Inertia::render('Admin/Server/EditServer', [
            'server' => $serverData,
            'versionsArray' => ServerVersion::getValues(),
        ]);
    }

    public function updateBungee(Request $request, Server $server, GeolocationService $geolocationService)
    {
        $this->authorize('update', $server);

        $request->validate([
            'hostname' => 'required',
            'ip_address' => 'required|ip',
            'join_port' => 'required|numeric|min:0|max:65535',
            'query_port' => 'required|numeric|min:0|max:65535',
            'webquery_port' => 'nullable|numeric|min:0|max:65535|required_if_accepted:is_server_intel_enabled|different:join_port',
            'name' => 'required',
            'minecraft_version' => ['required', new EnumValue(ServerVersion::class)],
            'is_server_intel_enabled' => 'required|boolean',
            'settings' => 'sometimes',
            'settings.server_identifier' => 'nullable|alpha_dash',
            'settings.is_skin_change_via_web_allowed' => 'required|boolean',
            'settings.is_banwarden_enabled' => 'required|boolean',
            'order' => 'nullable|numeric',
        ]);

        $countryId = $geolocationService->getCountryIdFromIP($request->ip_address);

        $server->name = $request->name;
        $server->join_port = $request->join_port;
        $server->query_port = $request->query_port;
        $server->webquery_port = $request->webquery_port;
        $server->minecraft_version = $request->minecraft_version;
        $server->hostname = $request->hostname;
        $server->ip_address = $request->ip_address;
        $server->settings = $request->settings;
        $server->is_server_intel_enabled = $request->is_server_intel_enabled;
        $server->country_id = $countryId;
        $server->order = $request->order;
        $server->updated_by = $request->user()->id;
        $server->save();

        // We forget the cached result so that new data will be shown instantly and not redundant data.
        Cache::forget('server:ping:'.$server->id);
        Cache::forget('server:query:'.$server->id);
        Cache::forget('server:webquery:'.$server->id);

        return redirect()->route('admin.server.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Server updated successfully')]]);
    }

    public function update(UpdateServerRequest $request, Server $server, GeolocationService $geolocationService)
    {
        $this->authorize('update', $server);

        $countryId = $geolocationService->getCountryIdFromIP($request->ip_address);

        $server->ip_address = $request->ip_address;
        $server->join_port = $request->join_port;
        $server->query_port = $request->query_port;
        $server->webquery_port = $request->webquery_port;
        $server->hostname = $request->hostname;
        $server->name = $request->name;
        $server->minecraft_version = $request->minecraft_version;
        $server->type = $request->type;
        $server->country_id = $countryId;
        $server->updated_by = $request->user()->id;
        $server->settings = $request->settings;
        $server->is_server_intel_enabled = $request->is_server_intel_enabled;
        $server->is_player_intel_enabled = $request->is_player_intel_enabled;
        $server->is_ingame_chat_enabled = $request->is_ingame_chat_enabled;
        $server->order = $request->order;
        $server->save();

        // We forget the cached result so that new data will be shown instantly and not redundant data.
        Cache::forget('server:ping:'.$server->id);
        Cache::forget('server:query:'.$server->id);
        Cache::forget('server:webquery:'.$server->id);

        return redirect()->route('admin.server.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Server updated successfully')]]);
    }

    public function destroy(Server $server)
    {
        $this->authorize('delete', $server);

        $server->settings = array_merge($server->settings, ['is_deleting' => true]);
        $server->save();
        DeleteServerJob::dispatch($server);

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Deletion Started'), 'body' => __('Server deletion has been started. It may take sometime to complete depending on number of players.')]]);
    }

    public function postSendCommandToServer(Server $server, Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:kill,kick,mute,ban,broadcast,custom'],
            'context' => ['required', 'in:player,server'],
            'params' => ['required', 'string'],
        ]);

        $webQuery = new MinecraftWebQuery($server->ip_address, $server->webquery_port);
        $user = $request->user();
        // Eg: kick, ban, mute, broadcast, custom etc
        $type = $request->type;
        // Eg: player, server
        $context = $request->context;
        // Eg: username
        $params = $request->params;

        switch ($type) {
            case 'kill':
                $this->authorize('kill players');
                $response = $webQuery->runCommand('kill', $params);
                break;
            case 'kick':
                $this->authorize('kick players');
                $response = $webQuery->runCommand('kick', $params);
                break;
            case 'mute':
                $this->authorize('mute players');
                $response = $webQuery->runCommand('emute', $params);
                break;
            case 'ban':
                $this->authorize('ban players');
                $response = $webQuery->runCommand('ban', $params);
                break;
            case 'broadcast':
                $this->authorize('send server_broadcasts');
                $response = $webQuery->runCommand('broadcast', $params);
                break;
            case 'custom':
                $this->authorize('send server_custom_commands');
                $response = $webQuery->runCommand($params);
                break;
            default:
                $response = null;
                break;
        }

        return $response;
    }

    public function postForceSyncStats(Request $request)
    {
        $this->authorize('create', Server::class);

        CalculatePlayersJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Rescan Queued!'), 'body' => __('Successfully queued resync of player stats. It may take sometime to reflect depending on number of players.'), 'milliseconds' => 20000]]);
    }
}
