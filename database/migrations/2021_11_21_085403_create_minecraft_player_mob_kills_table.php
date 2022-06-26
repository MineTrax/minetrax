<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftPlayerMobKillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_player_mob_kills', function (Blueprint $table) {
            $table->id();
            $table->uuid('player_uuid')->index();
            $table->string('player_username');
            $table->string('mob_id');
            $table->string('mob_name');
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
        Schema::dropIfExists('minecraft_player_mob_kills');
    }
}
