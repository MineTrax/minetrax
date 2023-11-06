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
        Schema::table('minecraft_player_events', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('minecraft_player_deaths', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('died_at');
        });

        Schema::table('minecraft_player_pvp_kills', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('killed_at');
        });

        Schema::table('minecraft_player_world_stats', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('minecraft_players', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('first_seen_at');
            $table->index('last_seen_at');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('first_seen_at');
            $table->index('last_seen_at');
        });

        Schema::table('minecraft_player_sessions', function (Blueprint $table) {
            $table->index('session_started_at');
            $table->index('session_ended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('minecraft_player_events', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('minecraft_player_deaths', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['died_at']);
        });

        Schema::table('minecraft_player_pvp_kills', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['killed_at']);
        });

        Schema::table('minecraft_player_world_stats', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('minecraft_players', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['first_seen_at']);
            $table->dropIndex(['last_seen_at']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['first_seen_at']);
            $table->dropIndex(['last_seen_at']);
        });

        Schema::table('minecraft_player_sessions', function (Blueprint $table) {
            $table->dropIndex(['session_started_at']);
            $table->dropIndex(['session_ended_at']);
        });
    }
};
