<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServerType;
use App\Http\Controllers\Controller;
use App\Models\MinecraftServerLiveInfo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Server;
use Illuminate\Support\Facades\Cache;
const RESPONSE_CACHE_SECONDS = 3600; // 1 hour

class ServerIntelController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view server_intel');
    }

    public function index()
    {
        return Inertia::render('Admin/ServerIntel/Overview');
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

            return $numbersData;
        });

        return response()->json([
            'numbers' => $numbers,
        ]);
    }

    public function playerbase()
    {
        return Inertia::render('Admin/ServerIntel/Playerbase');
    }

    public function chatlog()
    {
        return Inertia::render('Admin/ServerIntel/Chatlog');
    }

    public function consolelog()
    {
        return Inertia::render('Admin/ServerIntel/Consolelog');
    }
}
