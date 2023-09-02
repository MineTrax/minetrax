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
            $table->string('player_displayname')->nullable();
            $table->integer('play_time')->nullable()->default(0);
            $table->integer('fish_caught')->nullable()->default(0);
            $table->integer('items_enchanted')->nullable()->default(0);
            $table->integer('times_slept_in_bed')->nullable()->default(0);
            $table->integer('times_jumped')->nullable()->default(0);
            $table->integer('raids_won')->nullable()->default(0);
            $table->double('distance_traveled')->nullable()->default(0);
            $table->double('distance_traveled_on_land')->nullable()->default(0);
            $table->double('distance_traveled_on_water')->nullable()->default(0);
            $table->double('distance_traveled_on_air')->nullable()->default(0);
            $table->double('pvp_damage_given')->nullable()->default(0);
            $table->double('pvp_damage_taken')->nullable()->default(0);

            $table->json('inventory_items')->nullable(); // list of items in inventory at time of event.
            $table->json('enderchest_items')->nullable(); // list of items in inventory at time of event.

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
            $table->dropColumn('player_displayname');
            $table->dropColumn('play_time');
            $table->dropColumn('fish_caught');
            $table->dropColumn('items_enchanted');
            $table->dropColumn('times_slept_in_bed');
            $table->dropColumn('times_jumped');
            $table->dropColumn('raids_won');
            $table->dropColumn('distance_traveled');
            $table->dropColumn('distance_traveled_on_land');
            $table->dropColumn('distance_traveled_on_water');
            $table->dropColumn('distance_traveled_on_air');
            $table->dropColumn('pvp_damage_given');
            $table->dropColumn('pvp_damage_taken');

            $table->dropColumn('vault_balance');
            $table->dropColumn('vault_groups');

            $table->dropColumn('inventory_items');
            $table->dropColumn('enderchest_items');
        });
    }
};
