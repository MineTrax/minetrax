<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftPlayerStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('json_minecraft_player_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->uuid('uuid')->index();
            $table->string('username')->nullable();
            $table->unsignedBigInteger('last_modified')->nullable();    // This can be used as player last seen
            $table->string('hash')->nullable();                 // Used to check if file has changed in server and need to be synced

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
            $table->double('money')->nullable();
            $table->string('nickname')->nullable();

            // Raw Data
            $table->json('killed_by')->nullable();
            $table->json('used')->nullable();
            $table->json('mined')->nullable();
            $table->json('picked_up')->nullable();
            $table->json('custom')->nullable();
            $table->json('dropped')->nullable();
            $table->json('broken')->nullable();
            $table->json('crafted')->nullable();
            $table->json('killed')->nullable();
            // Plugins
            $table->json('essentials')->nullable();
            $table->unsignedInteger('data_version')->nullable();

            $table->foreignId('player_id')->nullable()->constrained()->onDelete('set null');
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
        Schema::dropIfExists('minecraft_player_stats');
    }
}
