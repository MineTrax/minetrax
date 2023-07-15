<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServerType;
use App\Http\Controllers\Controller;
use App\Models\MinecraftPlayerSession;
use App\Models\MinecraftServerLiveInfo;
use App\Models\ServerChatlog;
use App\Models\ServerConsolelog;
use App\Queries\Filters\FilterMultipleFields;
use App\Utils\Helpers\MinecraftColorUtils;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Server;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

const RESPONSE_CACHE_SECONDS = 3600; // 1 hour

class ServerIntelController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view server_intel');
    }

    public function overview(Request $request)
    {
        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
        ]);
        $serverList = Server::select(['id', 'name'])
            ->where('type', '!=', ServerType::Bungee())
            ->get()->pluck('name', 'id');

        $selectedServers = $request->query('servers') ?? null; // list of selected server ids

        /**
         * Start: LAST 7 DAYS STATS
         */
        // Unique Players Count
        $uniquePlayers = MinecraftPlayerSession::select(['player_uuid'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->distinct()->get();
        $uniquePlayersCount = $uniquePlayers->count();

        // Old & New Players Count
        $oldPlayers = MinecraftPlayerSession::select(['player_uuid'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '<', now()->subWeek())
            ->whereIn('player_uuid', $uniquePlayers->pluck('player_uuid'))
            ->distinct()->get();
        $oldPlayersCount = $oldPlayers->count();
        $newPlayersCount = $uniquePlayers->whereNotIn('player_uuid', $oldPlayers->pluck('player_uuid'))->count();

        // Avg TPS
        $averageTps = MinecraftServerLiveInfo::select(['tps'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->avg('tps');

        // Restarts
        $noOfRestarts = MinecraftServerLiveInfo::select(['server_session_id'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->distinct()->count();

        // Avg CPU
        $averageCpuLoad = MinecraftServerLiveInfo::select(['cpu_load'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->avg('cpu_load');

        // Longest Uptime
        $longestUptime = MinecraftServerLiveInfo::query()
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->max('uptime');
        /**
         * End: LAST 7 DAYS STATS
         */

        return Inertia::render('Admin/ServerIntel/Overview', [
            'filters' => request()->all(['servers']),
            'serverList' => $serverList,
            'last7DaysStats' => [
                'uniquePlayersCount' => $uniquePlayersCount,
                'oldPlayersCount' => $oldPlayersCount,
                'newPlayersCount' => $newPlayersCount,
                'averageTps' => $averageTps,
                'averageCpuLoad' => $averageCpuLoad,
                'noOfRestarts' => $noOfRestarts,
                'longestUptime' => $longestUptime,
            ],
        ]);
    }

    public function overviewNumbers(Request $request)
    {

    }

    public function performance(Request $request)
    {
        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
        ]);

        $serverList = Server::select(['id', 'name'])
            ->where('type', '!=', ServerType::Bungee())
            ->get()->pluck('name', 'id');

        return Inertia::render('Admin/ServerIntel/Performance', [
            'filters' => request()->all(['servers']),
            'serverList' => $serverList,
        ]);
    }

    public function performanceNumbers(Request $request)
    {
        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
        ]);

        $selectedServers = $request->query('servers') ?? null; // list of selected server ids

        $selectedServersKey = serialize($selectedServers);
        $numbers = Cache::remember("server-intel-performance-numbers.{$selectedServersKey}", RESPONSE_CACHE_SECONDS, function () use ($selectedServers) {
            if ($selectedServers) {
                $selectedServers = Server::where('type', '!=', ServerType::Bungee())->whereIn('id', $selectedServers)->get();
            } else {
                $selectedServers = Server::where('type', '!=', ServerType::Bungee())->get();
            }

            // NumbersData - last 24 hours, last week, last month, 3 months.
            $numbersData = [];
            // Max Online Players
            $maxPlayers["last_24h"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->max('online_players') ?: 0;
            $maxPlayers["last_7days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->max('online_players') ?: 0;
            $maxPlayers["last_30days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->max('online_players') ?: 0;
            $maxPlayers["last_90days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->max('online_players') ?: 0;
            $numbersData["max_players"] = $maxPlayers;

            // Lowest TPS
            $lowTPS["last_24h"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->min('tps') ?: 0;
            $lowTPS["last_7days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->min('tps') ?: 0;
            $lowTPS["last_30days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->min('tps') ?: 0;
            $lowTPS["last_90days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->min('tps') ?: 0;
            $numbersData["low_tps"] = $lowTPS;

            // Avg CPU
            $avgCPU["last_24h"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('cpu_load') ?: 0;
            $avgCPU["last_7days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('cpu_load') ?: 0;
            $avgCPU["last_30days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('cpu_load') ?: 0;
            $avgCPU["last_90days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('cpu_load') ?: 0;
            $numbersData["avg_cpu"] = $avgCPU;

            // Avg Ram
            $avgMemory["last_24h"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('used_memory') ?: 0;
            $avgMemory["last_7days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('used_memory') ?: 0;
            $avgMemory["last_30days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('used_memory') ?: 0;
            $avgMemory["last_90days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('used_memory') ?: 0;
            $numbersData["avg_memory"] = $avgMemory;

            // Avg Chunks
            $avgChunks["last_24h"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('chunks_loaded') ?: 0;
            $avgChunks["last_7days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('chunks_loaded') ?: 0;
            $avgChunks["last_30days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('chunks_loaded') ?: 0;
            $avgChunks["last_90days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('chunks_loaded') ?: 0;
            $numbersData["avg_chunks"] = $avgChunks;

            // Min Free Disk
            $minFreeDisk["last_24h"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->min('free_disk_in_kb') ?: 0;
            $minFreeDisk["last_7days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->min('free_disk_in_kb') ?: 0;
            $minFreeDisk["last_30days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->min('free_disk_in_kb') ?: 0;
            $minFreeDisk["last_90days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->min('free_disk_in_kb') ?: 0;
            $numbersData["min_free_disk"] = $minFreeDisk;

            // Total Restarts
            $totalRestarts["last_24h"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->distinct()->count('server_session_id');
            $totalRestarts["last_7days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->distinct()->count('server_session_id');
            $totalRestarts["last_30days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->distinct()->count('server_session_id');
            $totalRestarts["last_90days"] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->distinct()->count('server_session_id');
            $numbersData["total_restarts"] = $totalRestarts;

            // Player Avg Session Length
            $playerAvgSessionLength["last_24h"] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('play_time') ?: 0;
            $playerAvgSessionLength["last_7days"] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('play_time') ?: 0;
            $playerAvgSessionLength["last_30days"] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('play_time') ?: 0;
            $playerAvgSessionLength["last_90days"] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('play_time') ?: 0;
            $numbersData["player_avg_session_length"] = $playerAvgSessionLength;

            // Player Avg AFK Time
            $playerAvgAFKTime["last_24h"] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('afk_time') ?: 0;
            $playerAvgAFKTime["last_7days"] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('afk_time') ?: 0;
            $playerAvgAFKTime["last_30days"] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('afk_time') ?: 0;
            $playerAvgAFKTime["last_90days"] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('afk_time') ?: 0;
            $numbersData["player_avg_afk_time"] = $playerAvgAFKTime;

            return $numbersData;
        });

        return response()->json([
            'numbers' => $numbers,
        ]);
    }

    public function playerbase(Request $request)
    {
        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
        ]);
        $serverList = Server::select(['id', 'name'])
            ->where('type', '!=', ServerType::Bungee())
            ->get()->pluck('name', 'id');

        return Inertia::render('Admin/ServerIntel/Playerbase', [
            'filters' => request()->all(['servers']),
            'serverList' => $serverList,
        ]);
    }

    public function chatlog(Request $request)
    {
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
        $chatHistory = QueryBuilder::for(ServerChatlog::class)
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->select([
                'id',
                'server_id',
                'data',
                'causer_uuid',
                'causer_username',
                'channel',
                'type',
                'created_at',
            ])
            ->allowedFilters([
                'id',
                'server_id',
                'data',
                'causer_uuid',
                'causer_username',
                'channel',
                'type',
                'created_at',
                AllowedFilter::custom('q', new FilterMultipleFields(['data', 'causer_username', 'causer_uuid', 'type']))
            ])
            ->with(['server:id,name'])
            ->allowedSorts(['id', 'server_id', 'causer_uuid', 'causer_username', 'type', 'channel', 'created_at'])
            ->defaultSort('-id')
            ->simplePaginate($perPage)
            ->withQueryString();

        $chatHistory->getCollection()->transform(function ($item) {
            $item->data = MinecraftColorUtils::convertToHTML($item->data);
            return $item;
        });

        return Inertia::render('Admin/ServerIntel/Chatlog', [
            'filters' => request()->all(['servers', 'perPage', 'sort', 'filter']),
            'serverList' => $serverList,
            'chatHistory' => $chatHistory,
        ]);
    }

    public function consolelog(Request $request)
    {
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
        $consoleHistory = QueryBuilder::for(ServerConsolelog::class)
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->select([
                'id',
                'server_id',
                'data',
                'created_at',
            ])
            ->allowedFilters([
                'id',
                'server_id',
                'data',
                'created_at',
                AllowedFilter::custom('q', new FilterMultipleFields(['data']))
            ])
            ->with(['server:id,name'])
            ->allowedSorts(['id', 'server_id', 'created_at'])
            ->defaultSort('-id')
            ->simplePaginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/ServerIntel/Consolelog', [
            'filters' => request()->all(['servers', 'perPage', 'sort', 'filter']),
            'serverList' => $serverList,
            'consoleHistory' => $consoleHistory,
        ]);
    }
}
