<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->bigInteger('total_used')->change();
            $table->bigInteger('total_mined')->change();
            $table->bigInteger('total_picked_up')->change();
            $table->bigInteger('total_dropped')->change();
            $table->bigInteger('total_broken')->change();
            $table->bigInteger('total_crafted')->change();

            $table->bigInteger('total_mob_kills')->change();
            $table->bigInteger('total_player_kills')->change();
            $table->bigInteger('total_deaths')->change();

            $table->bigInteger('total_damage_dealt')->change();
            $table->bigInteger('total_damage_dealt_absorbed')->change();
            $table->bigInteger('total_damage_dealt_resisted')->change();
            $table->bigInteger('total_damage_absorbed')->change();
            $table->bigInteger('total_damage_blocked_by_shield')->change();
            $table->bigInteger('total_damage_resisted')->change();
            $table->bigInteger('total_damage_taken')->change();

            $table->bigInteger('total_walk_one_cm')->change();
            $table->bigInteger('total_fish_caught')->change();
            $table->bigInteger('total_enchant_item')->change();
            $table->bigInteger('total_play_one_minute')->change();
            $table->bigInteger('total_sleep_in_bed')->change();
            $table->bigInteger('total_jumps')->change();
            $table->bigInteger('total_leave_game')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedBigInteger('total_used')->change();
            $table->unsignedBigInteger('total_mined')->change();
            $table->unsignedBigInteger('total_picked_up')->change();
            $table->unsignedBigInteger('total_dropped')->change();
            $table->unsignedBigInteger('total_broken')->change();
            $table->unsignedBigInteger('total_crafted')->change();

            $table->unsignedBigInteger('total_mob_kills')->change();
            $table->unsignedBigInteger('total_player_kills')->change();
            $table->unsignedBigInteger('total_deaths')->change();

            $table->unsignedBigInteger('total_damage_dealt')->change();
            $table->unsignedBigInteger('total_damage_dealt_absorbed')->change();
            $table->unsignedBigInteger('total_damage_dealt_resisted')->change();
            $table->unsignedBigInteger('total_damage_absorbed')->change();
            $table->unsignedBigInteger('total_damage_blocked_by_shield')->change();
            $table->unsignedBigInteger('total_damage_resisted')->change();
            $table->unsignedBigInteger('total_damage_taken')->change();

            $table->unsignedBigInteger('total_walk_one_cm')->change();
            $table->unsignedBigInteger('total_fish_caught')->change();
            $table->unsignedBigInteger('total_enchant_item')->change();
            $table->unsignedBigInteger('total_play_one_minute')->change();
            $table->unsignedBigInteger('total_sleep_in_bed')->change();
            $table->unsignedBigInteger('total_jumps')->change();
            $table->unsignedBigInteger('total_leave_game')->change();
        });
    }
};
