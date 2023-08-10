<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement($this->dropView());
    }

    private function createView(): string
    {
        return <<<'SQL'
            CREATE VIEW `minecraft_players` AS
            SELECT
            MIN(id) AS id,
            mps.player_uuid,
            mps.server_id,
            (SELECT player_username
             FROM minecraft_player_sessions sub_mps
             WHERE sub_mps.player_uuid = mps.player_uuid AND sub_mps.server_id = mps.server_id
             ORDER BY id DESC
             LIMIT 1) AS player_username,
            (SELECT player_displayname
             FROM minecraft_player_sessions sub_mps2
             WHERE sub_mps2.player_uuid = mps.player_uuid AND sub_mps2.server_id = mps.server_id
             ORDER BY id DESC
             LIMIT 1) AS player_displayname,
            (SELECT player_ip_address
             FROM minecraft_player_sessions sub_mps3
             WHERE sub_mps3.player_uuid = mps.player_uuid AND sub_mps3.server_id = mps.server_id
             ORDER BY id DESC
             LIMIT 1) AS player_ip_address,
            (SELECT country_id
             FROM minecraft_player_sessions sub_mps4
             WHERE sub_mps4.player_uuid = mps.player_uuid AND sub_mps4.server_id = mps.server_id
             ORDER BY id DESC
             LIMIT 1) AS country_id,
            SUM(mob_kills) AS mob_kills,
            SUM(player_kills) AS player_kills,
            SUM(deaths) AS deaths,
            SUM(afk_time) AS afk_time,
            SUM(play_time) AS play_time,
            MAX(updated_at) AS updated_at,
            MIN(created_at) AS created_at
            FROM
            minecraft_player_sessions mps
            GROUP BY
            mps.player_uuid, mps.server_id;
SQL;
    }

    private function dropView(): string
    {
        return <<<'SQL'
            DROP VIEW IF EXISTS `minecraft_players`;
SQL;
    }
};
