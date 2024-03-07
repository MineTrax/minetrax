<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MinecraftServerLiveInfo extends BaseModel
{
    use HasFactory;

    public static function getOnlinePlayersCount($serverIds, $endDate)
    {
        $subquery = DB::table('minecraft_server_live_infos')
            ->selectRaw('ROUND(UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(created_at, "%Y-%m-%d %H:"),LPAD((MINUTE(created_at) DIV 5) * 5, 2, "0"),":00")) * 1000) AS created_at_5min')
            ->selectRaw('MAX(online_players) AS max_online_players')
            ->selectRaw('server_id')
            ->when($endDate, function ($query, $endDate) {
                return $query->where('created_at', '>=', $endDate);
            })
            ->when($serverIds, function ($query, $serverIds) {
                return $query->whereIn('server_id', $serverIds);
            })
            ->groupBy('created_at_5min', 'server_id');

        $query = DB::table(DB::raw("({$subquery->toSql()}) as online_players_per_server"))
            ->mergeBindings($subquery)
            ->select('online_players_per_server.created_at_5min', DB::raw('SUM(online_players_per_server.max_online_players) AS total_max_online_players'))
            ->groupBy('online_players_per_server.created_at_5min')
            ->orderBy('online_players_per_server.created_at_5min', 'ASC');

        $max = DB::table(DB::raw("({$query->toSql()}) as online_players"))
            ->mergeBindings($query)
            ->max('online_players.total_max_online_players');

        return (int) $max;
    }
}
