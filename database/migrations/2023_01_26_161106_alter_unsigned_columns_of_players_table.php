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
            $table->bigInteger('total_used')->default(0)->change();
            $table->bigInteger('total_mined')->default(0)->change();
            $table->bigInteger('total_picked_up')->default(0)->change();
            $table->bigInteger('total_dropped')->default(0)->change();
            $table->bigInteger('total_broken')->default(0)->change();
            $table->bigInteger('total_crafted')->default(0)->change();

            $table->bigInteger('total_mob_kills')->default(0)->change();
            $table->bigInteger('total_player_kills')->default(0)->change();
            $table->bigInteger('total_deaths')->default(0)->change();

            $table->bigInteger('total_damage_dealt')->default(0)->change();
            $table->bigInteger('total_damage_dealt_absorbed')->default(0)->change();
            $table->bigInteger('total_damage_dealt_resisted')->default(0)->change();
            $table->bigInteger('total_damage_absorbed')->default(0)->change();
            $table->bigInteger('total_damage_blocked_by_shield')->default(0)->change();
            $table->bigInteger('total_damage_resisted')->default(0)->change();
            $table->bigInteger('total_damage_taken')->default(0)->change();

            $table->bigInteger('total_walk_one_cm')->default(0)->change();
            $table->bigInteger('total_fish_caught')->default(0)->change();
            $table->bigInteger('total_enchant_item')->default(0)->change();
            $table->bigInteger('total_play_one_minute')->default(0)->change();
            $table->bigInteger('total_sleep_in_bed')->default(0)->change();
            $table->bigInteger('total_jumps')->default(0)->change();
            $table->bigInteger('total_leave_game')->default(0)->change();
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
            $table->unsignedBigInteger('total_used')->default(0)->change();
            $table->unsignedBigInteger('total_mined')->default(0)->change();
            $table->unsignedBigInteger('total_picked_up')->default(0)->change();
            $table->unsignedBigInteger('total_dropped')->default(0)->change();
            $table->unsignedBigInteger('total_broken')->default(0)->change();
            $table->unsignedBigInteger('total_crafted')->default(0)->change();

            $table->unsignedBigInteger('total_mob_kills')->default(0)->change();
            $table->unsignedBigInteger('total_player_kills')->default(0)->change();
            $table->unsignedBigInteger('total_deaths')->default(0)->change();

            $table->unsignedBigInteger('total_damage_dealt')->default(0)->change();
            $table->unsignedBigInteger('total_damage_dealt_absorbed')->default(0)->change();
            $table->unsignedBigInteger('total_damage_dealt_resisted')->default(0)->change();
            $table->unsignedBigInteger('total_damage_absorbed')->default(0)->change();
            $table->unsignedBigInteger('total_damage_blocked_by_shield')->default(0)->change();
            $table->unsignedBigInteger('total_damage_resisted')->default(0)->change();
            $table->unsignedBigInteger('total_damage_taken')->default(0)->change();

            $table->unsignedBigInteger('total_walk_one_cm')->default(0)->change();
            $table->unsignedBigInteger('total_fish_caught')->default(0)->change();
            $table->unsignedBigInteger('total_enchant_item')->default(0)->change();
            $table->unsignedBigInteger('total_play_one_minute')->default(0)->change();
            $table->unsignedBigInteger('total_sleep_in_bed')->default(0)->change();
            $table->unsignedBigInteger('total_jumps')->default(0)->change();
            $table->unsignedBigInteger('total_leave_game')->default(0)->change();
        });
    }
};
