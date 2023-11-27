<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServerType;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\MinecraftPlayer;
use App\Models\MinecraftPlayerSession;
use App\Models\MinecraftServerLiveInfo;
use App\Models\Server;
use App\Models\ServerChatlog;
use App\Models\ServerConsolelog;
use App\Queries\Filters\FilterMultipleFields;
use App\Utils\Helpers\MinecraftColorUtils;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
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
        $uniquePlayersCount = MinecraftPlayer::select(['player_uuid'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('last_seen_at', '>=', now()->subWeek())
            ->distinct()->count('player_uuid');

        // Old & New Players Count
        $oldPlayersCount = MinecraftPlayer::select(['player_uuid'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('first_seen_at', '<', now()->subWeek())
            ->where('last_seen_at', '>=', now()->subWeek())
            ->distinct()->count('player_uuid');

        $newPlayersCount = MinecraftPlayer::select(['player_uuid'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('first_seen_at', '>=', now()->subWeek())
            ->distinct()->count('player_uuid');

        // Peek Online Players
        $peekOnlinePlayersCount = MinecraftServerLiveInfo::query()
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->max('online_players') ?: 0;

        // Avg TPS
        $averageTps = MinecraftServerLiveInfo::select(['tps'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->avg('tps');

        $lowestTps = MinecraftServerLiveInfo::select(['tps'])
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->min('tps');

        // Restarts
        $noOfRestarts = MinecraftServerLiveInfo::query()
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('server_id', $selectedServers);
            })
            ->where('created_at', '>=', now()->subWeek())
            ->distinct()->count('server_session_id');

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
        $lastIntelReportedAt = Server::query()
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('id', $selectedServers);
            })
            ->max('last_scanned_at');
        if ($lastIntelReportedAt) {
            $lastIntelReportedAt = Carbon::parse($lastIntelReportedAt);
            $noIntelForOverWeek = $lastIntelReportedAt->diffInDays(now()) > 7;
        } else {
            $noIntelForOverWeek = true;
        }

        return Inertia::render('Admin/ServerIntel/Overview', [
            'filters' => request()->all(['servers']),
            'serverList' => $serverList,
            'last7DaysStats' => [
                'uniquePlayersCount' => $uniquePlayersCount,
                'oldPlayersCount' => $oldPlayersCount,
                'peekOnlinePlayersCount' => $peekOnlinePlayersCount, // 'peek' is not a typo, it's a pun
                'newPlayersCount' => $newPlayersCount,
                'averageTps' => $averageTps,
                'lowestTps' => $lowestTps,
                'averageCpuLoad' => $averageCpuLoad,
                'noOfRestarts' => $noOfRestarts,
                'longestUptime' => $longestUptime,
            ],
            'noIntelForOverWeek' => $noIntelForOverWeek,
        ]);
    }

    public function overviewNumbers(Request $request)
    {
        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
        ]);

        $selectedServers = $request->query('servers') ?? null; // list of selected server ids

        $selectedServersKey = serialize($selectedServers);
        $numbers = Cache::remember("server-overview-numbers.{$selectedServersKey}", RESPONSE_CACHE_SECONDS, function () use ($selectedServers) {
            if ($selectedServers) {
                $selectedServers = Server::where('type', '!=', ServerType::Bungee())->whereIn('id', $selectedServers)->get();
            } else {
                $selectedServers = Server::where('type', '!=', ServerType::Bungee())->get();
            }

            $numbersData = [];
            // Total Players
            $totalPlayers['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->distinct()->count('player_uuid') ?: 0;
            $totalPlayers['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->distinct()->count('player_uuid') ?: 0;
            $totalPlayers['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->distinct()->count('player_uuid') ?: 0;
            $totalPlayers['all_time'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->distinct()->count('player_uuid') ?: 0;
            $numbersData['total_players'] = $totalPlayers;

            // Total Playtime
            $totalPlaytime['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->sum('play_time') ?: 0;
            $totalPlaytime['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->sum('play_time') ?: 0;
            $totalPlaytime['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->sum('play_time') ?: 0;
            $totalPlaytime['all_time'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->sum('play_time') ?: 0;
            $numbersData['total_playtime'] = $totalPlaytime;

            // AFK Time
            $totalAfkTime['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->sum('afk_time') ?: 0;
            $totalAfkTime['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->sum('afk_time') ?: 0;
            $totalAfkTime['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->sum('afk_time') ?: 0;
            $totalAfkTime['all_time'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->sum('afk_time') ?: 0;
            $numbersData['total_afktime'] = $totalAfkTime;

            // Total Sessions
            $totalSessions['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->count() ?: 0;
            $totalSessions['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->count() ?: 0;
            $totalSessions['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->count() ?: 0;
            $totalSessions['all_time'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->distinct()->count() ?: 0;
            $numbersData['total_sessions'] = $totalSessions;

            // Avg Session Playtime
            $avgSessionPlaytime['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('play_time') ?: 0;
            $avgSessionPlaytime['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('play_time') ?: 0;
            $avgSessionPlaytime['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('play_time') ?: 0;
            $avgSessionPlaytime['all_time'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->avg('play_time') ?: 0;
            $numbersData['avg_session_playtime'] = $avgSessionPlaytime;

            // Total Player Kills
            $totalPlayerKills['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->sum('player_kills') ?: 0;
            $totalPlayerKills['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->sum('player_kills') ?: 0;
            $totalPlayerKills['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->sum('player_kills') ?: 0;
            $totalPlayerKills['all_time'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->sum('player_kills') ?: 0;
            $numbersData['total_player_kills'] = $totalPlayerKills;

            // Total Mob Kills
            $totalMobKills['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->sum('mob_kills') ?: 0;
            $totalMobKills['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->sum('mob_kills') ?: 0;
            $totalMobKills['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->sum('mob_kills') ?: 0;
            $totalMobKills['all_time'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->sum('mob_kills') ?: 0;
            $numbersData['total_mob_kills'] = $totalMobKills;

            // Total Deaths
            $totalDeaths['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->sum('deaths') ?: 0;
            $totalDeaths['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->sum('deaths') ?: 0;
            $totalDeaths['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->sum('deaths') ?: 0;
            $totalDeaths['all_time'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->sum('deaths') ?: 0;
            $numbersData['total_deaths'] = $totalDeaths;

            // Avg Sessions per Player
            $minecraftPlayerSessionTableName = app(MinecraftPlayerSession::class)->getTable();
            $avgSessionsPerPlayer['last_24h'] = DB::table($minecraftPlayerSessionTableName)
                ->whereIn('server_id', $selectedServers->pluck('id'))
                ->where('created_at', '>=', now()->subHours(24))
                ->select('player_uuid', DB::raw('COUNT(*) as count'))
                ->groupBy('player_uuid')
                ->pluck('count')?->avg() ?: 0;
            $avgSessionsPerPlayer['last_7days'] = DB::table($minecraftPlayerSessionTableName)
                ->whereIn('server_id', $selectedServers->pluck('id'))
                ->where('created_at', '>=', now()->subWeek())
                ->select('player_uuid', DB::raw('COUNT(*) as count'))
                ->groupBy('player_uuid')
                ->pluck('count')?->avg() ?: 0;
            $avgSessionsPerPlayer['last_30days'] = DB::table($minecraftPlayerSessionTableName)
                ->whereIn('server_id', $selectedServers->pluck('id'))
                ->where('created_at', '>=', now()->subMonth())
                ->select('player_uuid', DB::raw('COUNT(*) as count'))
                ->groupBy('player_uuid')
                ->pluck('count')?->avg() ?: 0;
            $avgSessionsPerPlayer['all_time'] = DB::table($minecraftPlayerSessionTableName)
                ->whereIn('server_id', $selectedServers->pluck('id'))
                ->select('player_uuid', DB::raw('COUNT(*) as count'))
                ->groupBy('player_uuid')
                ->pluck('count')?->avg() ?: 0;
            $numbersData['avg_session_per_player'] = $avgSessionsPerPlayer;

            return $numbersData;
        });

        return response()->json([
            'numbers' => $numbers,
        ]);
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

        $selectedServers = $request->query('servers') ?? null; // list of selected server ids

        $lastIntelReportedAt = Server::query()
            ->when($selectedServers, function ($query, $selectedServers) {
                $query->whereIn('id', $selectedServers);
            })
            ->max('last_scanned_at');
        if ($lastIntelReportedAt) {
            $lastIntelReportedAt = Carbon::parse($lastIntelReportedAt);
            $noIntelForOverWeek = $lastIntelReportedAt->diffInDays(now()) > 7;
        } else {
            $noIntelForOverWeek = true;
        }

        return Inertia::render('Admin/ServerIntel/Performance', [
            'filters' => request()->all(['servers']),
            'serverList' => $serverList,
            'noIntelForOverWeek' => $noIntelForOverWeek,
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
            $maxPlayers['last_24h'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->max('online_players') ?: 0;
            $maxPlayers['last_7days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->max('online_players') ?: 0;
            $maxPlayers['last_30days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->max('online_players') ?: 0;
            $maxPlayers['last_90days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->max('online_players') ?: 0;
            $numbersData['max_players'] = $maxPlayers;

            // Lowest TPS
            $lowTPS['last_24h'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->min('tps') ?: 0;
            $lowTPS['last_7days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->min('tps') ?: 0;
            $lowTPS['last_30days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->min('tps') ?: 0;
            $lowTPS['last_90days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->min('tps') ?: 0;
            $numbersData['low_tps'] = $lowTPS;

            // Avg CPU
            $avgCPU['last_24h'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('cpu_load') ?: 0;
            $avgCPU['last_7days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('cpu_load') ?: 0;
            $avgCPU['last_30days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('cpu_load') ?: 0;
            $avgCPU['last_90days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('cpu_load') ?: 0;
            $numbersData['avg_cpu'] = $avgCPU;

            // Avg Ram
            $avgMemory['last_24h'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('used_memory') ?: 0;
            $avgMemory['last_7days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('used_memory') ?: 0;
            $avgMemory['last_30days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('used_memory') ?: 0;
            $avgMemory['last_90days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('used_memory') ?: 0;
            $numbersData['avg_memory'] = $avgMemory;

            // Avg Chunks
            $avgChunks['last_24h'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('chunks_loaded') ?: 0;
            $avgChunks['last_7days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('chunks_loaded') ?: 0;
            $avgChunks['last_30days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('chunks_loaded') ?: 0;
            $avgChunks['last_90days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('chunks_loaded') ?: 0;
            $numbersData['avg_chunks'] = $avgChunks;

            // Min Free Disk
            $minFreeDisk['last_24h'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->min('free_disk_in_kb') ?: 0;
            $minFreeDisk['last_7days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->min('free_disk_in_kb') ?: 0;
            $minFreeDisk['last_30days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->min('free_disk_in_kb') ?: 0;
            $minFreeDisk['last_90days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->min('free_disk_in_kb') ?: 0;
            $numbersData['min_free_disk'] = $minFreeDisk;

            // Total Restarts
            $totalRestarts['last_24h'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->distinct()->count('server_session_id');
            $totalRestarts['last_7days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->distinct()->count('server_session_id');
            $totalRestarts['last_30days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->distinct()->count('server_session_id');
            $totalRestarts['last_90days'] = MinecraftServerLiveInfo::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->distinct()->count('server_session_id');
            $numbersData['total_restarts'] = $totalRestarts;

            // Player Avg Session Length
            $playerAvgSessionLength['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('play_time') ?: 0;
            $playerAvgSessionLength['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('play_time') ?: 0;
            $playerAvgSessionLength['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('play_time') ?: 0;
            $playerAvgSessionLength['last_90days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('play_time') ?: 0;
            $numbersData['player_avg_session_length'] = $playerAvgSessionLength;

            // Player Avg AFK Time
            $playerAvgAFKTime['last_24h'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subHours(24))->avg('afk_time') ?: 0;
            $playerAvgAFKTime['last_7days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subWeek())->avg('afk_time') ?: 0;
            $playerAvgAFKTime['last_30days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonth())->avg('afk_time') ?: 0;
            $playerAvgAFKTime['last_90days'] = MinecraftPlayerSession::whereIn('server_id', $selectedServers->pluck('id'))->where('created_at', '>=', now()->subMonths(3))->avg('afk_time') ?: 0;
            $numbersData['player_avg_afk_time'] = $playerAvgAFKTime;

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

    public function getPlayerPerCountry(Request $request)
    {
        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
        ]);

        $selectedServers = $request->query('servers') ?? null; // list of selected server ids

        $countries = Country::withCount([
            'minecraftPlayers' => function ($query) use ($selectedServers) {
                $query->when($selectedServers, function ($query, $selectedServers) {
                    $query->whereIn('server_id', $selectedServers);
                });
            },
        ])->get();
        $data = $countries->map(function ($country) {
            return [
                'name' => $country->name,
                'value' => $country->minecraft_players_count,
                'image' => $country->photo_path,
            ];
        });
        $maxValue = $data->max('value') == 0 ? 1 : $data->max('value');

        return response()->json([
            'data' => $data,
            'max' => $maxValue,
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
                AllowedFilter::custom('q', new FilterMultipleFields(['data', 'causer_username', 'causer_uuid', 'type'])),
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
                AllowedFilter::custom('q', new FilterMultipleFields(['data'])),
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
