<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServerType;
use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GraphController extends Controller
{
    public function getOnlinePlayersOverTime(Request $request)
    {
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

        $fromDate = $request->query('from_date') ?? now()->subMonth(12);
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
            $item->created_at_5min = (int)$item->created_at_5min;
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
        $servers = Server::where('type', '!=', ServerType::Bungee())
            ->withCount('minecraftPlayerStats')
            ->get();

        $data = $servers->map(function ($server) {
            return [
                'name' => $server->name,
                'value' => $server->minecraft_player_stats_count,
            ];
        })->sortByDesc('players');
        return response()->json($data);
    }
}
