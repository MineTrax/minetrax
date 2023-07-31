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
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedBigInteger('total_items_placed')->default(0);
            $table->unsignedBigInteger('total_items_consumed')->default(0);
            $table->unsignedBigInteger('total_items_enchanted')->default(0);
            $table->unsignedBigInteger('raids_won')->default(0);
            $table->unsignedBigInteger('play_time')->default(0);
            $table->unsignedBigInteger('afk_time')->default(0);
            $table->double('distance_traveled')->default(0);
            $table->double('distance_traveled_on_land')->default(0);
            $table->double('distance_traveled_on_water')->default(0);
            $table->double('distance_traveled_on_air')->default(0);
            $table->double('pvp_damage_given')->default(0);
            $table->double('pvp_damage_taken')->default(0);
            $table->string('last_minecraft_version')->nullable();
            $table->string('last_join_address')->nullable();

            // Drop
            $table->dropColumn('total_enchant_item');
            $table->dropColumn('total_damage_dealt');
            $table->dropColumn('total_damage_dealt_absorbed');
            $table->dropColumn('total_damage_dealt_resisted');
            $table->dropColumn('total_damage_absorbed');
            $table->dropColumn('total_damage_blocked_by_shield');
            $table->dropColumn('total_damage_resisted');
            $table->dropColumn('total_damage_taken');
            $table->dropColumn('total_walk_one_cm');
            $table->dropColumn('total_play_one_minute');
            $table->dropColumn('last_mps_updated_at');
        });


        // Minecraft Player Session table.
        Schema::table('minecraft_player_sessions', function (Blueprint $table) {
            $table->foreignId('minecraft_player_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('total_items_placed');
            $table->dropColumn('total_items_consumed');
            $table->dropColumn('total_items_enchanted');
            $table->dropColumn('raids_won');
            $table->dropColumn('play_time');
            $table->dropColumn('afk_time');
            $table->dropColumn('distance_traveled');
            $table->dropColumn('distance_traveled_on_land');
            $table->dropColumn('distance_traveled_on_water');
            $table->dropColumn('distance_traveled_on_air');
            $table->dropColumn('pvp_damage_given');
            $table->dropColumn('pvp_damage_taken');
            $table->dropColumn('last_minecraft_version');
            $table->dropColumn('last_join_address');

            // Re-add
            $table->bigInteger('total_damage_dealt')->default(0);
            $table->bigInteger('total_damage_dealt_absorbed')->default(0);
            $table->bigInteger('total_damage_dealt_resisted')->default(0);
            $table->bigInteger('total_damage_absorbed')->default(0);
            $table->bigInteger('total_damage_blocked_by_shield')->default(0);
            $table->bigInteger('total_damage_resisted')->default(0);
            $table->bigInteger('total_damage_taken')->default(0);
            $table->bigInteger('total_walk_one_cm')->default(0);
            $table->bigInteger('total_enchant_item')->default(0);
            $table->bigInteger('total_play_one_minute')->default(0);
            $table->timestamp('last_mps_updated_at')->nullable();
        });


        // Minecraft Player Session table.
        Schema::table('minecraft_player_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('minecraft_player_id');
        });
    }
};
