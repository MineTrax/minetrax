<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftPlayerPvpKillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_player_pvp_kills', function (Blueprint $table) {
            $table->id();
            $table->uuid('killer_uuid')->index();
            $table->uuid('victim_uuid')->index();
            $table->string('killer_username');
            $table->string('victim_username');
            $table->timestamp('killed_at');
            $table->string('weapon')->nullable();
            $table->json('world_location')->nullable();

            $table->foreignId('session_id')->constrained('minecraft_player_sessions')->onDelete('cascade');
            $table->foreignId('minecraft_server_world_id')->nullable()->constrained('minecraft_server_worlds')->onDelete('set null');
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
        Schema::dropIfExists('minecraft_player_pvp_kills');
    }
}
