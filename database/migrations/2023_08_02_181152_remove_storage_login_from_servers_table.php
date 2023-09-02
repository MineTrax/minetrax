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
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('storage_login');
            $table->dropColumn('is_stats_tracking_enabled');
            $table->dropColumn('is_online_players_query_enabled');

            $table->boolean('is_server_intel_enabled')->default(true);
            $table->boolean('is_player_intel_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->longText('storage_login')->nullable();
            $table->boolean('is_stats_tracking_enabled')->default(true);
            $table->boolean('is_online_players_query_enabled')->default(true);

            $table->dropColumn('is_server_intel_enabled');
            $table->dropColumn('is_player_intel_enabled');
        });
    }
};
