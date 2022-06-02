<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftWorldLiveInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_world_live_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('chunks_loaded')->nullable();
            $table->integer('game_time');
            $table->integer('online_players');
            $table->string('server_session_id')->nullable();

            $table->foreignId('minecraft_server_world_id')->constrained('minecraft_server_worlds')->onDelete('cascade');
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
        Schema::dropIfExists('minecraft_world_live_infos');
    }
}
