<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old view.
        DB::statement($this->dropMinecraftPlayersView());

        // Create new table with same name.
        Schema::create('minecraft_players', function(Blueprint $table) {
            $table->id();
            $table->uuid('player_uuid')->index();
            $table->string('player_username')->index()->nullable();
            $table->string('player_displayname')->nullable();
            $table->string('player_ip_address')->nullable();

            $table->unsignedBigInteger('mob_kills')->nullable()->default(0);
            $table->unsignedBigInteger('player_kills')->nullable()->default(0);
            $table->unsignedBigInteger('deaths')->nullable()->default(0);
            $table->unsignedBigInteger('afk_time')->nullable()->default(0);
            $table->unsignedBigInteger('play_time')->nullable()->default(0);
            $table->integer('ban_counter')->nullable()->default(0);
            $table->integer('kick_counter')->nullable()->default(0);

            $table->unsignedBigInteger('items_used')->nullable()->default(0);
            $table->unsignedBigInteger('items_mined')->nullable()->default(0);
            $table->unsignedBigInteger('items_picked_up')->nullable()->default(0);
            $table->unsignedBigInteger('items_dropped')->nullable()->default(0);
            $table->unsignedBigInteger('items_broken')->nullable()->default(0);
            $table->unsignedBigInteger('items_crafted')->nullable()->default(0);
            $table->unsignedBigInteger('items_placed')->nullable()->default(0);
            $table->unsignedBigInteger('items_consumed')->nullable()->default(0);
            $table->unsignedBigInteger('fish_caught')->nullable()->default(0);
            $table->unsignedBigInteger('items_enchanted')->nullable()->default(0);
            $table->unsignedBigInteger('times_slept_in_bed')->nullable()->default(0);
            $table->unsignedBigInteger('times_jumped')->nullable()->default(0);
            $table->unsignedBigInteger('raids_won')->nullable()->default(0);
            $table->double('distance_traveled')->nullable()->default(0);
            $table->double('distance_traveled_on_land')->nullable()->default(0);
            $table->double('distance_traveled_on_water')->nullable()->default(0);
            $table->double('distance_traveled_on_air')->nullable()->default(0);
            $table->double('pvp_damage_given')->nullable()->default(0);
            $table->double('pvp_damage_taken')->nullable()->default(0);

            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_minecraft_version')->nullable();
            $table->string('last_join_address')->nullable();
            $table->double('vault_balance')->nullable();
            $table->json('vault_groups')->nullable();

            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['player_uuid', 'server_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the newly created table first, and create view with same name.
        Schema::dropIfExists('minecraft_players');
        DB::statement($this->createMinecraftPlayersView());
    }


    /**
     * Create the legacy minecraft_players view.
     */
    private function createMinecraftPlayersView(): string
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

    /**
     * Drop the legacy minecraft_players view.
     */
    private function dropMinecraftPlayersView(): string
    {
        return <<<'SQL'
            DROP VIEW IF EXISTS `minecraft_players`;
SQL;
    }
};
