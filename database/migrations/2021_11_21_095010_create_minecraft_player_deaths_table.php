<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftPlayerDeathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_player_deaths', function (Blueprint $table) {
            $table->id();
            $table->uuid('player_uuid')->index();
            $table->string('player_username');
            $table->string('cause'); // Death cause
            $table->uuid('killer_uuid')->nullable(); // if killed by player then player uuid here
            $table->string('killer_username')->nullable(); // if killed by player then player name here

            $table->string('killer_entity_id')->nullable();    // If killed by mob: Entity id
            $table->string('killer_entity_name')->nullable();    // If killed by mob: Entity name
            $table->timestamp('died_at');
            $table->string('world_location')->nullable();

            $table->foreignId('session_id')->constrained('minecraft_player_sessions')->onDelete('cascade');
            $table->foreignId('minecraft_server_world_id')->nullable()->constrained('minecraft_server_worlds')->onDelete('cascade');
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
        Schema::dropIfExists('minecraft_player_deaths');
    }
}
