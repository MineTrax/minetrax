<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftPlayerWorldStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_player_world_stats', function (Blueprint $table) {
            $table->id();
            $table->uuid('player_uuid')->index();
            $table->integer('survival_time');
            $table->integer('creative_time');
            $table->integer('adventure_time');
            $table->integer('spectator_time');

            $table->foreignId('session_id')->constrained('minecraft_player_sessions')->onDelete('cascade');
            $table->foreignId('world_id')->constrained('minecraft_server_worlds')->onDelete('cascade');
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
        Schema::dropIfExists('minecraft_player_world_stats');
    }
}
