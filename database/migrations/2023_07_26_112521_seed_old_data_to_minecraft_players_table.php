<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TRUNCATE minecraft_players table (so if in some case someone run it 2 times its don't break).
        DB::statement('TRUNCATE TABLE minecraft_players');

        // For each player in minecraft_player_sessions table, create a new row in minecraft_players table.

        // select DISTICT player_uuid, server_id. but also select mob_kills
        // from minecraft_player_sessions table.
        $rawMinecraftPlayerList = DB::table('minecraft_player_sessions')
            ->select('player_uuid', 'server_id')
            ->addSelect([
                'minecraft_version' => DB::table('minecraft_player_sessions')
                    ->select('minecraft_version')
                    ->whereColumn('id', 'minecraft_player_sessions.id')
                    ->orderByDesc('id')
                    ->limit(1)
            ])
            ->groupBy('player_uuid', 'server_id')
            ->orderBy('player_uuid', 'ASC')
            ->lazy();

        foreach ($rawMinecraftPlayerList as $rawMinecraftPlayer) {
            dump($rawMinecraftPlayer);
        }


        // Loop thru each

        // Get aggr value of stats from minecraft_player_events table.

        // Insert into minecraft_players table.

        // ... continue...
        // dd("NOT MIGRATED!");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // nothing...
    }
};
