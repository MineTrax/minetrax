<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServerType;
use App\Enums\ServerVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateServerRequest;
use App\Http\Requests\UpdateServerRequest;
use App\Jobs\FetchStatsFromAllServersJob;
use App\Models\JsonMinecraftPlayerStat;
use App\Models\MinecraftServerLiveInfo;
use App\Models\Server;
use App\Services\GeolocationService;
use App\Services\MinecraftServerFileService;
use App\Services\MinecraftServerPingService;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ServerController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Server::class);

        $canCreateBungeeServer = Server::where('type', ServerType::Bungee)->exists();

        $servers = Server::latest()->select([
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
            'created_at'
        ])->with('country')->paginate(5);

        return Inertia::render('Admin/Server/IndexServer', [
            'servers' => $servers,
            'canCreateBungeeServer' => !$canCreateBungeeServer
        ]);
    }

    public function create()
    {
        $this->authorize('create', Server::class);

        return Inertia::render('Admin/Server/CreateServer', [
            "versionsArray" => ServerVersion::getValues()
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
            "versionsArray" => ServerVersion::getValues()
        ]);
    }

    public function store(CreateServerRequest $request, GeolocationService $geolocationService)
    {
        // Encrypt the Login information
        // Make connection string
        $connectionString = [];
        $storageServerHost = $request->storage_server_host;
        if ($request->connection_type == 'ftp') {
            $connectionString = [
                'driver' => 'ftp',
                'host' => $storageServerHost,
                'username' => $request->storage_server_username,
                'password' => $request->storage_server_password,
                'port' => $request->storage_server_port ?? 21,
                'root' => $request->storage_server_root ?? '',
                'ssl' => $request->storage_server_ssl ?? false,
            ];
        } elseif ($request->connection_type == 'sftp') {
            $connectionString = [
                'driver' => 'sftp',
                'host' => $storageServerHost,
                'username' => $request->storage_server_username,
                'password' => $request->storage_server_password,
                'port' => $request->storage_server_port ?? 22,
                'root' => $request->storage_server_root ?? ''
            ];
        } else if($request->connection_type == 'local') {
            $storageServerHost = '127.0.0.1';
            $connectionString = [
                'driver' => 'local',
                'root' => $request->storage_server_root
            ];
        }
        $connectionString = encrypt($connectionString);
        $countryId = $geolocationService->getCountryIdFromIP(gethostbyname($storageServerHost));

        $ipAddress = gethostbyname($request->hostname);
        // If ip address still have something like 111.111.111.111:25565 then we remove the port part
        if(\Str::contains($ipAddress, ":")) {
            $ipAddress = explode(":",$ipAddress);
            $ipAddress = $ipAddress[0];
        }

        Server::create([
            'ip_address' => $request->ip_address,
            'join_port' => $request->join_port,
            'query_port' => $request->query_port,
            'webquery_port' => $request->webquery_port,
            'hostname' => $request->hostname,
            'name' => $request->name,
            'minecraft_version' => $request->minecraft_version,
            'type' => $request->type,
            'storage_login' => $connectionString,
            'level_name' => $request->level_name,
            'country_id' => $countryId,
            'created_by' => $request->user()->id,
            'settings' => $request->settings,
            'is_stats_tracking_enabled' => $request->is_stats_tracking_enabled,
            'is_ingame_chat_enabled' => $request->is_ingame_chat_enabled,
            'is_online_players_query_enabled' => $request->is_online_players_query_enabled
        ]);

        return redirect()->route('admin.server.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('New server added successfully')]]);
    }

    public function storeBungee(Request $request, GeolocationService $geolocationService)
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
            'webquery_port' => 'nullable|numeric|min:0|max:65535',
            'name' => 'required',
            'minecraft_version' => ['required', new EnumValue(ServerVersion::class)],
        ]);

        $countryId = $geolocationService->getCountryIdFromIP($request->ip_address);

        Server::create([
            'ip_address' => $request->ip_address,
            'join_port' => $request->join_port,
            'query_port' => $request->query_port,
            'webquery_port' => $request->webquery_port,
            'hostname' => $request->hostname,
            'name' => $request->name,
            'minecraft_version' => $request->minecraft_version,
            'type' => ServerType::Bungee,
            'country_id' => $countryId,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.server.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Bungee server added successfully')]]);
    }

    public function show(Server $server)
    {
        $this->authorize('view', $server);

        $serverAggrData = [];
        $serverAggrData['pvp_kills'] = (string) JsonMinecraftPlayerStat::where('server_id', $server->id)->sum('total_player_kills');
        $serverAggrData['mob_kills'] = (string) JsonMinecraftPlayerStat::where('server_id', $server->id)->sum('total_mob_kills');
        $serverAggrData['player_deaths'] = (string) JsonMinecraftPlayerStat::where('server_id', $server->id)->sum('total_deaths');
        $serverAggrData['player_damage_taken'] = (string) JsonMinecraftPlayerStat::where('server_id', $server->id)->sum('total_damage_taken');
        $serverAggrData['total_players'] = (string) JsonMinecraftPlayerStat::where('server_id', $server->id)->count();
        $serverAggrData['active_players'] = (string) JsonMinecraftPlayerStat::where('server_id', $server->id)
            ->where('updated_at', '>=', now()->subDays(30))->count();

        return Inertia::render('Admin/Server/ShowServer', [
            'server' => $server->load(['country']),
            'serverAggrData' => $serverAggrData,
            'serverConsoleLogs' => $server->serverConsolelog()->limit(100)->orderByDesc('id')->get()
        ]);
    }

    public function showPerformanceMonitor(Server $server, Request $request)
    {
        $this->authorize('view', $server);

        // LiveServerInfoData
        $query = MinecraftServerLiveInfo::query();
        if($request->has('dateFrom') && $request->has('dateTo')) {
            $query->whereBetween('created_at', [$request->dateFrom, $request->dateTo]);
        }
        else {
            $query->limit(60);
        }
        $serverLiveInfoData = $query->where('server_id', $server->id)->latest()->get();

        // TODO NOTE: Can be optimized to less number of queries. But do we need to?
        // NumbersData - last 24 hours, last week, last month, all time.
        $numbersData = [];
        // Max Online Players
        $maxPlayers["last_24h"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subHours(24))->max('online_players') ?: 0;
        $maxPlayers["last_week"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subWeek())->max('online_players') ?: 0;
        $maxPlayers["last_month"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subMonth())->max('online_players') ?: 0;
        $maxPlayers["all_time"] = MinecraftServerLiveInfo::where('server_id', $server->id)->max('online_players') ?: 0;
        $numbersData["max_players"] = $maxPlayers;

        // Lowest TPS
        $lowTPS["last_24h"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subHours(24))->min('tps') ?: 0;
        $lowTPS["last_week"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subWeek())->min('tps') ?: 0;
        $lowTPS["last_month"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subMonth())->min('tps') ?: 0;
        $lowTPS["all_time"] = MinecraftServerLiveInfo::where('server_id', $server->id)->min('tps') ?: 0;
        $numbersData["low_tps"] = $lowTPS;

        // Avg CPU
        $avgCPU["last_24h"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subHours(24))->avg('cpu_load') ?: 0;
        $avgCPU["last_week"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subWeek())->avg('cpu_load') ?: 0;
        $avgCPU["last_month"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subMonth())->avg('cpu_load') ?: 0;
        $avgCPU["all_time"] = MinecraftServerLiveInfo::where('server_id', $server->id)->avg('cpu_load') ?: 0;
        $numbersData["avg_cpu"] = $avgCPU;

        // Avg Ram
        $avgMemory["last_24h"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subHours(24))->avg('used_memory') ?: 0;
        $avgMemory["last_week"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subWeek())->avg('used_memory') ?: 0;
        $avgMemory["last_month"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subMonth())->avg('used_memory') ?: 0;
        $avgMemory["all_time"] = MinecraftServerLiveInfo::where('server_id', $server->id)->avg('used_memory') ?: 0;
        $numbersData["avg_memory"] = $avgMemory;

        // Avg Chunks
        $avgChunks["last_24h"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subHours(24))->avg('chunks_loaded') ?: 0;
        $avgChunks["last_week"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subWeek())->avg('chunks_loaded') ?: 0;
        $avgChunks["last_month"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subMonth())->avg('chunks_loaded') ?: 0;
        $avgChunks["all_time"] = MinecraftServerLiveInfo::where('server_id', $server->id)->avg('chunks_loaded') ?: 0;
        $numbersData["avg_chunks"] = $avgChunks;

        // Min Free Disk
        $minFreeDisk["last_24h"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subHours(24))->min('free_disk_in_kb') ?: 0;
        $minFreeDisk["last_week"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subWeek())->min('free_disk_in_kb') ?: 0;
        $minFreeDisk["last_month"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subMonth())->min('free_disk_in_kb') ?: 0;
        $minFreeDisk["all_time"] = MinecraftServerLiveInfo::where('server_id', $server->id)->min('free_disk_in_kb') ?: 0;
        $numbersData["min_free_disk"] = $minFreeDisk;

        // Total Restarts
        $totalRestarts["last_24h"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subHours(24))->distinct()->count('server_session_id');
        $totalRestarts["last_week"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subWeek())->distinct()->count('server_session_id');
        $totalRestarts["last_month"] = MinecraftServerLiveInfo::where('server_id', $server->id)->where('created_at', '>=', now()->subMonth())->distinct()->count('server_session_id');
        $totalRestarts["all_time"] = MinecraftServerLiveInfo::where('server_id', $server->id)->distinct()->count('server_session_id');
        $numbersData["total_restarts"] = $totalRestarts;

        return Inertia::render('Admin/Server/ShowServerPerformanceMonitor', [
            'server' => $server,
            'serverLiveInfo' => $serverLiveInfoData->reverse()->values(),
            'numbers' => $numbersData,
            'queryParams' => request()->all(['dateFrom', 'dateTo']),
        ]);
    }

    public function showInsights(Server $server)
    {
        $this->authorize('view', $server);

        return Inertia::render('Admin/Server/ShowServerInsights', [
            'server' => $server,
        ]);
    }

    public function showStatistics(Server $server)
    {
        $this->authorize('view', $server);

        $aggregatedTotals = $server->getAggregatedTotalJsonStats();
        $aggregatedMax = $server->getAggregatedMaxJsonStats();

        return Inertia::render('Admin/Server/ShowServerStats', [
            'server' => $server,
            "aggrMax" => $aggregatedMax,
            "aggrTotal" => $aggregatedTotals
        ]);
    }

    public function edit(Server $server)
    {
        $this->authorize('update', $server);

        if (ServerType::Bungee()->is($server->type)) {
            return Inertia::render('Admin/Server/CreateEditBungeeServer', [
                'server' => $server,
                "versionsArray" => ServerVersion::getValues()
            ]);
        }

        $decryptedStorageLoginData = decrypt($server->storage_login);
        $serverData = [
            'id' => $server->id,
            "connection_type" => $decryptedStorageLoginData['driver'],
            "storage_server_host" => $decryptedStorageLoginData['host'] ?? null,
            "storage_server_port" => $decryptedStorageLoginData['port'] ?? null,
            "storage_server_username" => $decryptedStorageLoginData['username'] ?? null,
            "storage_server_password" => $decryptedStorageLoginData['password'] ?? null,
            "storage_server_root" => $decryptedStorageLoginData['root'] ?? null,
            "storage_server_ssl" => $decryptedStorageLoginData['ssl'] ?? null,
            "name" => $server->name,
            "join_port" => $server->join_port,
            "query_port" => $server->query_port,
            "webquery_port" => $server->webquery_port,
            "level_name" => $server->level_name,
            "minecraft_version" => $server->minecraft_version,
            "type" => $server->type->value,
            "hostname" => $server->hostname,
            "ip_address" => $server->ip_address,
            "is_stats_tracking_enabled" => $server->is_stats_tracking_enabled,
            "is_ingame_chat_enabled" => $server->is_ingame_chat_enabled,
            "is_online_players_query_enabled" => $server->is_online_players_query_enabled,
            "settings" => $server->settings,
        ];
        return Inertia::render('Admin/Server/EditServer', [
            'server' => $serverData,
            "versionsArray" => ServerVersion::getValues()
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
            'webquery_port' => 'nullable|numeric|min:0|max:65535',
            'name' => 'required',
            'minecraft_version' => ['required', new EnumValue(ServerVersion::class)],
        ]);

        $countryId = $geolocationService->getCountryIdFromIP($request->ip_address);

        $server->name = $request->name;
        $server->join_port = $request->join_port;
        $server->query_port = $request->query_port;
        $server->webquery_port = $request->webquery_port;
        $server->minecraft_version = $request->minecraft_version;
        $server->hostname = $request->hostname;
        $server->ip_address = $request->ip_address;
        $server->country_id = $countryId;
        $server->updated_by = $request->user()->id;
        $server->save();

        // We forget the cached result so that new data will be shown instantly and not redundant data.
        Cache::forget('server:ping:'.$server->id);
        Cache::forget('server:query:'.$server->id);
        Cache::forget('server:webquery:'.$server->id);

        return redirect()->route('admin.server.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Bungee server updated successfully')]]);
    }

    public function update(UpdateServerRequest $request, Server $server, GeolocationService $geolocationService)
    {
        $this->authorize('update', $server);

        // Encrypt the Login information
        // Make connection string
        $connectionString = [];
        $storageServerHost = $request->storage_server_host;
        if ($request->connection_type == 'ftp') {
            $connectionString = [
                'driver' => 'ftp',
                'host' => $storageServerHost,
                'username' => $request->storage_server_username,
                'password' => $request->storage_server_password,
                'port' => $request->storage_server_port ?? 21,
                'root' => $request->storage_server_root ?? '',
                'ssl' => $request->storage_server_ssl ?? false
            ];
        } elseif ($request->connection_type == 'sftp') {
            $connectionString = [
                'driver' => 'sftp',
                'host' => $storageServerHost,
                'username' => $request->storage_server_username,
                'password' => $request->storage_server_password,
                'port' => $request->storage_server_port ?? 22,
                'root' => $request->storage_server_root ?? ''
            ];
        } else if($request->connection_type == 'local') {
            $storageServerHost = '127.0.0.1';
            $connectionString = [
                'driver' => 'local',
                'root' => $request->storage_server_root
            ];
        }
        $connectionString = encrypt($connectionString);
        $countryId = $geolocationService->getCountryIdFromIP($request->ip_address);

        $server->ip_address = $request->ip_address;
        $server->join_port = $request->join_port;
        $server->query_port = $request->query_port;
        $server->webquery_port = $request->webquery_port;
        $server->hostname = $request->hostname;
        $server->name = $request->name;
        $server->minecraft_version = $request->minecraft_version;
        $server->type = $request->type;
        $server->storage_login = $connectionString;
        $server->level_name = $request->level_name;
        $server->country_id = $countryId;
        $server->updated_by = $request->user()->id;
        $server->settings = $request->settings;
        $server->is_stats_tracking_enabled = $request->is_stats_tracking_enabled;
        $server->is_ingame_chat_enabled = $request->is_ingame_chat_enabled;
        $server->is_online_players_query_enabled = $request->is_online_players_query_enabled;
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

        $server->delete();
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Server has been deleted permanently')]]);
    }

    public function prefetch(Request $request, MinecraftServerFileService $serverFileService, MinecraftServerPingService $serverPingService)
    {
        $this->authorize('create', Server::class);

        $request->validate([
            'storage_server_host' => 'required_if:connection_type,ftp,sftp',
            'storage_server_port' => 'nullable|numeric|min:0|max:65535',
            'storage_server_username' => 'nullable|required_if:connection_type,ftp,sftp|string',
            'storage_server_password' => 'required_if:connection_type,ftp,sftp',
            'storage_server_root' => 'sometimes|required_if:connection_type,local|nullable',
            'storage_server_ssl'=> 'sometimes|nullable|required_if:connection_type,ftp|boolean',
        ]);

        // Make connection string
        $connectionString = [];
        $storageServerHost = $request->storage_server_host;
        if ($request->connection_type == 'ftp') {
            $connectionString = [
                'driver' => 'ftp',
                'host' => $storageServerHost,
                'username' => $request->storage_server_username,
                'password' => $request->storage_server_password,
                'port' => $request->storage_server_port ?? 21,
                'root' => $request->storage_server_root ?? '',
                'ssl' =>  $request->storage_server_ssl ?? false,
            ];
        } elseif ($request->connection_type == 'sftp') {
            $connectionString = [
                'driver' => 'sftp',
                'host' => $storageServerHost,
                'username' => $request->storage_server_username,
                'password' => $request->storage_server_password,
                'port' => $request->storage_server_port ?? 22,
                'root' => $request->storage_server_root ?? ''
            ];
        } else if($request->connection_type == 'local') {
            $storageServerHost = '127.0.0.1';
            $connectionString = [
                'driver' => 'local',
                'root' => $request->storage_server_root
            ];
        }

        // Send request to Service and return results.
        try {
            $fetchInfo = $serverFileService->getServerPropertiesFromRemote($connectionString);
            if ($fetchInfo) {
                $serverStatus = $serverPingService->pingServer($storageServerHost, $fetchInfo['server-port']);
            }

            if ($fetchInfo) {
                return response(['success' => __('Something found'), 'data' => $fetchInfo, 'server_status' => $serverStatus]);
            }
            return response(['message' => __('No Server found at this path')], 404);
        } catch (\Exception $exception) {
            return response(['message' => $exception->getMessage()], 500);
        }
    }

    public function postSendCommandToServer(Server $server, Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:kill,kick,mute,ban,broadcast,custom'],
            'context' => ['required', 'in:player,server'],
            'params' => ['required', 'string']
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

    public function postForceScan(Request $request)
    {
        $this->authorize('create', Server::class);

        FetchStatsFromAllServersJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Rescan Queued!'), 'body' => __('Successfully queued rescanning of all servers. It may take sometime to reflect depending on number of players found.'),'milliseconds' => 20000]]);
    }
}
