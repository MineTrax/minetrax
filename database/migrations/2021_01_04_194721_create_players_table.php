<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('username')->index()->nullable();
            $table->float('rating')->nullable(); // Calculated from secret rating function. Used to decide how elite player is.
            $table->integer('total_score')->default(0); // Used to calculate Rank. Admin can change what score give what rank.
            $table->unsignedInteger('position')->nullable();            // Based on Average of rating and score maybe

            $table->unsignedBigInteger('total_used')->default(0);
            $table->unsignedBigInteger('total_mined')->default(0);
            $table->unsignedBigInteger('total_picked_up')->default(0);
            $table->unsignedBigInteger('total_dropped')->default(0);
            $table->unsignedBigInteger('total_broken')->default(0);
            $table->unsignedBigInteger('total_crafted')->default(0);

            $table->unsignedBigInteger('total_mob_kills')->default(0);
            $table->unsignedBigInteger('total_player_kills')->default(0);
            $table->unsignedBigInteger('total_deaths')->default(0);

            $table->unsignedBigInteger('total_damage_dealt')->default(0);
            $table->unsignedBigInteger('total_damage_dealt_absorbed')->default(0);
            $table->unsignedBigInteger('total_damage_dealt_resisted')->default(0);
            $table->unsignedBigInteger('total_damage_absorbed')->default(0);
            $table->unsignedBigInteger('total_damage_blocked_by_shield')->default(0);
            $table->unsignedBigInteger('total_damage_resisted')->default(0);
            $table->unsignedBigInteger('total_damage_taken')->default(0);

            $table->unsignedBigInteger('total_walk_one_cm')->default(0);
            $table->unsignedBigInteger('total_fish_caught')->default(0);
            $table->unsignedBigInteger('total_enchant_item')->default(0);
            $table->unsignedBigInteger('total_play_one_minute')->default(0);
            $table->unsignedBigInteger('total_sleep_in_bed')->default(0);
            $table->unsignedBigInteger('total_jumps')->default(0);
            $table->unsignedBigInteger('total_leave_game')->default(0);

            // Can be get from Essentials if that plugin is installed
            $table->string('ip_address')->nullable();
            $table->double('total_money')->nullable();
            $table->timestamp('first_seen_at')->nullable();          // Min of last_modified of mps to get last seen from all servers
            $table->timestamp('last_seen_at')->nullable();          // Max of last_modified of mps to get last seen from all servers

            $table->timestamp('last_mps_updated_at')->nullable();   // This store updated_at of minecraft_player_stats table. It used to check if we need to sync.

            $table->integer('account_link_after_success_command_run_count')->default(0);   // Used to check how many times that command has run on this player, ie how many time this player is rewarded for linking. mostly it will be one.

            $table->foreignId('rank_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('players');
    }
}
