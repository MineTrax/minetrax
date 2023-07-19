<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServerType;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Server;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraphController extends Controller
{
    public function getOnlinePlayersOverTime(Request $request)
    {
        $this->authorize('view admin_dashboard');

        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
            'from_date' => 'sometimes|nullable|date',
        ]);

        $servers = $request->query('servers') ?? null;
        if ($servers) {
            $servers = Server::where('type', '!=', ServerType::Bungee())->whereIn('id', $servers)->get();
        } else {
            $servers = Server::where('type', '!=', ServerType::Bungee())->get();
        }

        $fromDate = $request->query('from_date') ?? now()->subMonth();
        $query = DB::table('minecraft_server_live_infos')
            ->selectRaw("ROUND(UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(created_at, '%Y-%m-%d %H:'), LPAD((MINUTE(created_at) DIV 5) * 5, 2, '0'), ':00')) * 1000) AS created_at_5min")
            ->groupBy('created_at_5min')
            ->orderBy('created_at_5min')
            ->where('created_at', '>=', $fromDate);
        foreach ($servers as $server) {
            $serverId = $server->id;
            $query->selectRaw("MAX(CASE WHEN server_id = $serverId THEN online_players ELSE 0 END) AS server$serverId");
        }
        $sqlData = $query->get();
        $sqlData = $sqlData->map(function ($item) {
            $item->created_at_5min = (int) $item->created_at_5min;
            return array_values(get_object_vars($item));
        })->toArray();

        return response()->json([
            'servers' => $servers->pluck('name'),
            'from_date' => $request->query('from_date'),
            'data' => $sqlData,
        ]);
    }

    public function getPlayersPerServer()
    {
        $this->authorize('view admin_dashboard');

        $servers = Server::where('type', '!=', ServerType::Bungee())
            ->withCount('minecraftPlayerStats')
            ->get();

        $data = $servers->map(function ($server) {
            return [
                'name' => $server->name,
                'value' => $server->minecraft_player_stats_count,
            ];
        });

        return response()->json($data);
    }

    public function getPlayerPerCountry()
    {
        $this->authorize('view admin_dashboard');

        $countries = Country::withCount('players')->get();

        $data = $countries->map(function ($country) {
            return [
                'name' => $country->name,
                'value' => $country->players_count,
                'image' => $country->photo_path,
            ];
        });
        $maxValue = $data->max('value') == 0 ? 1 : $data->max('value');

        return response()->json([
            'data' => $data,
            'max' => $maxValue,
        ]);
    }

    public function getNetworkTrendsMonthVsMonth()
    {
        $this->authorize('view admin_dashboard');

        $currentMonth = CarbonImmutable::now();
        $previousMonth = $currentMonth->subMonth();

        // Total Players.
        $avgTotalPlayerPreviousMonth = DB::table('minecraft_player_sessions')
            ->where('created_at', '>=', $previousMonth->startOfMonth())
            ->where('created_at', '<', $previousMonth->endOfMonth())
            ->distinct()
            ->count('player_uuid') ?? 0;
        $avgTotalPlayerCurrentMonth = DB::table('minecraft_player_sessions')
            ->where('created_at', '>=', $currentMonth->startOfMonth())
            ->where('created_at', '<', $currentMonth->endOfMonth())
            ->distinct()
            ->count('player_uuid') ?? 0;
        $avgTotalPlayerChangePercent = (($avgTotalPlayerCurrentMonth - $avgTotalPlayerPreviousMonth) / ($avgTotalPlayerPreviousMonth == 0 ? 1 : $avgTotalPlayerPreviousMonth)) * 100;

        // New Players.
        $avgNewPlayerPreviousMonth = DB::table('minecraft_player_sessions')
            ->where('created_at', '>=', $previousMonth->startOfMonth())
            ->where('created_at', '<', $previousMonth->endOfMonth())
            ->whereNotIn('player_uuid', function ($query) use ($previousMonth) {
                $query->select('player_uuid')
                    ->distinct()
                    ->from('minecraft_player_sessions')
                    ->where('created_at', '<', $previousMonth->startOfMonth());
            })
            ->distinct()
            ->count('player_uuid') ?? 0;
        $avgNewPlayerCurrentMonth = DB::table('minecraft_player_sessions')
            ->where('created_at', '>=', $currentMonth->startOfMonth())
            ->where('created_at', '<', $currentMonth->endOfMonth())
            ->whereNotIn('player_uuid', function ($query) use ($currentMonth) {
                $query->select('player_uuid')
                    ->distinct()
                    ->from('minecraft_player_sessions')
                    ->where('created_at', '<', $currentMonth->startOfMonth());
            })
            ->count() ?? 0;
        $avgNewPlayerChangePercent = (($avgNewPlayerCurrentMonth - $avgNewPlayerPreviousMonth) / ($avgNewPlayerPreviousMonth == 0 ? 1 : $avgNewPlayerPreviousMonth)) * 100;

        // Total sessions
        $avgTotalSessionPreviousMonth = DB::table('minecraft_player_sessions')
            ->where('session_started_at', '>=', $previousMonth->startOfMonth())
            ->where('session_started_at', '<', $previousMonth->endOfMonth())
            ->count() ?? 0;
        $avgTotalSessionCurrentMonth = DB::table('minecraft_player_sessions')
            ->where('session_started_at', '>=', $currentMonth->startOfMonth())
            ->where('session_started_at', '<', $currentMonth->endOfMonth())
            ->count() ?? 0;
        $avgTotalSessionChangePercent = (($avgTotalSessionCurrentMonth - $avgTotalSessionPreviousMonth) / ($avgTotalSessionPreviousMonth == 0 ? 1 : $avgTotalSessionPreviousMonth)) * 100;

        // Average Play Time.
        $averagePlayTimePreviousMonth = DB::table('minecraft_player_sessions')
            ->where('session_started_at', '>=', $previousMonth->startOfMonth())
            ->where('session_started_at', '<', $previousMonth->endOfMonth())
            ->avg('play_time') ?? 0;
        $averagePlayTimeCurrentMonth = DB::table('minecraft_player_sessions')
            ->where('session_started_at', '>=', $currentMonth->startOfMonth())
            ->where('session_started_at', '<', $currentMonth->endOfMonth())
            ->avg('play_time') ?? 0;
        $averagePlayTimeChangePercent = (($averagePlayTimeCurrentMonth - $averagePlayTimePreviousMonth) / ($averagePlayTimePreviousMonth == 0 ? 1 : $averagePlayTimePreviousMonth)) * 100;

        // Average AFK Time.
        $averageAfkTimePreviousMonth = DB::table('minecraft_player_sessions')
            ->where('session_started_at', '>=', $previousMonth->startOfMonth())
            ->where('session_started_at', '<', $previousMonth->endOfMonth())
            ->avg('afk_time') ?? 0;
        $averageAfkTimeCurrentMonth = DB::table('minecraft_player_sessions')
            ->where('session_started_at', '>=', $currentMonth->startOfMonth())
            ->where('session_started_at', '<', $currentMonth->endOfMonth())
            ->avg('afk_time') ?? 0;
        $averageAfkTimeChangePercent = (($averageAfkTimeCurrentMonth - $averageAfkTimePreviousMonth) / ($averageAfkTimePreviousMonth == 0 ? 1 : $averageAfkTimePreviousMonth)) * 100;

        // Average Player Ping.
        $averagePlayerPingPreviousMonth = DB::table('minecraft_player_events')
            ->where('created_at', '>=', $previousMonth->startOfMonth())
            ->where('created_at', '<', $previousMonth->endOfMonth())
            ->avg('player_ping') ?? 0;
        $averagePlayerPingCurrentMonth = DB::table('minecraft_player_events')
            ->where('created_at', '>=', $currentMonth->startOfMonth())
            ->where('created_at', '<', $currentMonth->endOfMonth())
            ->avg('player_ping') ?? 0;
        $averagePlayerPingChangePercent = (($averagePlayerPingCurrentMonth - $averagePlayerPingPreviousMonth) / ($averagePlayerPingPreviousMonth == 0 ? 1 : $averagePlayerPingPreviousMonth)) * 100;

        // Peek Online Players.
        $peekOnlinePlayersPreviousMonth = DB::table('minecraft_server_live_infos')
            ->where('created_at', '>=', $previousMonth->startOfMonth())
            ->where('created_at', '<', $previousMonth->endOfMonth())
            ->max('online_players') ?? 0;
        $peekOnlinePlayersCurrentMonth = DB::table('minecraft_server_live_infos')
            ->where('created_at', '>=', $currentMonth->startOfMonth())
            ->where('created_at', '<', $currentMonth->endOfMonth())
            ->max('online_players') ?? 0;
        $peekOnlinePlayersChangePercent = (($peekOnlinePlayersCurrentMonth - $peekOnlinePlayersPreviousMonth) / ($peekOnlinePlayersPreviousMonth == 0 ? 1 : $peekOnlinePlayersPreviousMonth)) * 100;

        return response()->json([
            'total_players' => [
                'previous_month' => $avgTotalPlayerPreviousMonth,
                'current_month' => $avgTotalPlayerCurrentMonth,
                'change' => round($avgTotalPlayerChangePercent, 2),
            ],
            'total_new_players' => [
                'previous_month' => $avgNewPlayerPreviousMonth,
                'current_month' => $avgNewPlayerCurrentMonth,
                'change' => round($avgNewPlayerChangePercent, 2),
            ],
            'total_player_sessions' => [
                'previous_month' => $avgTotalSessionPreviousMonth,
                'current_month' => $avgTotalSessionCurrentMonth,
                'change' => round($avgTotalSessionChangePercent, 2),
            ],
            'avg_playtime' => [
                'previous_month' => $averagePlayTimePreviousMonth,
                'current_month' => $averagePlayTimeCurrentMonth,
                'change' => round($averagePlayTimeChangePercent, 2),
            ],
            'avg_afktime' => [
                'previous_month' => $averageAfkTimePreviousMonth,
                'current_month' => $averageAfkTimeCurrentMonth,
                'change' => round($averageAfkTimeChangePercent, 2),
            ],
            'avg_player_ping' => [
                'previous_month' => $averagePlayerPingPreviousMonth,
                'current_month' => $averagePlayerPingCurrentMonth,
                'change' => round($averagePlayerPingChangePercent, 2),
            ],
            'peek_online_players' => [
                'previous_month' => $peekOnlinePlayersPreviousMonth,
                'current_month' => $peekOnlinePlayersCurrentMonth,
                'change' => round($peekOnlinePlayersChangePercent, 2),
            ],
        ]);
    }

    public function getServerPerformanceOverTime(Request $request)
    {
        $this->authorize('view server_intel');

        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
            'from_date' => 'sometimes|nullable|date',
            'to_date' => 'sometimes|nullable|date',
        ]);

        $servers = $request->query('servers') ?? null;
        if ($servers) {
            $servers = Server::where('type', '!=', ServerType::Bungee())->whereIn('id', $servers)->get();
        } else {
            $servers = Server::where('type', '!=', ServerType::Bungee())->get();
        }

        $fromDate = $request->query('from_date') ?? now()->subWeek();
        $toDate = $request->query('to_date') ?? now();
        $query = DB::table('minecraft_server_live_infos')
            ->selectRaw("ROUND(UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(created_at, '%Y-%m-%d %H:'), LPAD((MINUTE(created_at) DIV 5) * 5, 2, '0'), ':00')) * 1000) AS created_at_5min")
            ->selectRaw('MAX(online_players) AS online_players')
            ->selectRaw('MIN(tps) AS tps')
            ->selectRaw('MAX(cpu_load) AS cpu_load')
            ->selectRaw('MAX(used_memory) AS used_memory')
            ->selectRaw('MAX(chunks_loaded) AS chunks_loaded')
            ->groupBy('created_at_5min')
            ->orderBy('created_at_5min')
            ->where('created_at', '>=', $fromDate)
            ->where('created_at', '<=', $toDate)
            ->whereIn('server_id', $servers->pluck('id'));
        $sqlData = $query->get();
        $sqlData = $sqlData->map(function ($item) {
            $item->created_at_5min = (int) $item->created_at_5min;
            $item->used_memory = round($item->used_memory / 1024); // Convert to MB
            return array_values(get_object_vars($item));
        })->toArray();

        return response()->json([
            'labels' => [__('Online Players'), __('TPS'), __('CPU Load (%)'), __('RAM (Megabytes)'), __('Chunks Loaded')],
            'filters' => request()->all(['servers', 'from_date', 'to_date']),
            'data' => $sqlData,
        ]);
    }

    public function getServerOnlineActivityOverTime(Request $request)
    {
        $this->authorize('view server_intel');

        $request->validate([
            'servers' => 'sometimes|nullable|array',
            'servers.*' => 'sometimes|nullable|integer|exists:servers,id',
            'from_date' => 'sometimes|nullable|date',
            'to_date' => 'sometimes|nullable|date',
        ]);

        $servers = $request->query('servers') ?? null;
        if ($servers) {
            $servers = Server::where('type', '!=', ServerType::Bungee())->whereIn('id', $servers)->get();
        } else {
            $servers = Server::where('type', '!=', ServerType::Bungee())->get();
        }

        $fromDate = $request->query('from_date') ?? now()->subMonth();
        $toDate = $request->query('to_date') ?? now();
        $query = DB::table('minecraft_server_live_infos')
            ->selectRaw("ROUND(UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(created_at, '%Y-%m-%d %H:'), LPAD((MINUTE(created_at) DIV 5) * 5, 2, '0'), ':00')) * 1000) AS created_at_5min")
            ->selectRaw('MAX(online_players) AS online_players')
            ->groupBy('created_at_5min')
            ->orderBy('created_at_5min')
            ->where('created_at', '>=', $fromDate)
            ->where('created_at', '<=', $toDate)
            ->whereIn('server_id', $servers->pluck('id'));
        $sqlData = $query->get();
        $sqlData = $sqlData->map(function ($item) {
            $item->created_at_5min = (int) $item->created_at_5min;
            return array_values(get_object_vars($item));
        })->toArray();

        return response()->json([
            'labels' => [__('Online Players')],
            'filters' => request()->all(['servers', 'from_date', 'to_date']),
            'data' => $sqlData,
        ]);
    }
}
