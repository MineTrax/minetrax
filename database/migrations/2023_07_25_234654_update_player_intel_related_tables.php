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
        Schema::table('minecraft_player_sessions', function (Blueprint $table) {
            $table->string('join_address')->nullable();
            $table->string('minecraft_version')->nullable();
            $table->integer('player_ping')->nullable();

            $table->double('vault_balance')->nullable();
            $table->json('vault_groups')->nullable();
        });

        Schema::table('minecraft_player_events', function (Blueprint $table) {
            $table->integer('fish_caught')->nullable();
            $table->integer('items_enchanted')->nullable();
            $table->integer('times_slept_in_bed')->nullable();
            $table->integer('times_jumped')->nullable();
            $table->integer('raids_won')->nullable();
            $table->double('distance_travelled')->nullable();
            $table->double('distance_traveled_on_land')->nullable();
            $table->double('distance_traveled_on_water')->nullable();
            $table->double('distance_traveled_on_air')->nullable();
            $table->double('pvp_damage_given')->nullable();
            $table->double('pvp_damage_taken')->nullable();

            $table->json('items_stack')->nullable(); // list of items in inventory at time of event.

            $table->double('vault_balance')->nullable();
            $table->json('vault_groups')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('minecraft_player_sessions', function (Blueprint $table) {
            $table->dropColumn('join_address');
            $table->dropColumn('minecraft_version');
            $table->dropColumn('player_ping');

            $table->dropColumn('vault_balance');
            $table->dropColumn('vault_groups');
        });

        Schema::table('minecraft_player_events', function (Blueprint $table) {
            $table->dropColumn('fish_caught');
            $table->dropColumn('items_enchanted');
            $table->dropColumn('times_slept_in_bed');
            $table->dropColumn('times_jumped');
            $table->dropColumn('raids_won');
            $table->dropColumn('distance_travelled');
            $table->dropColumn('distance_traveled_on_land');
            $table->dropColumn('distance_traveled_on_water');
            $table->dropColumn('distance_traveled_on_air');
            $table->dropColumn('pvp_damage_given');
            $table->dropColumn('pvp_damage_taken');

            $table->dropColumn('vault_balance');
            $table->dropColumn('vault_groups');
        });
    }
};
