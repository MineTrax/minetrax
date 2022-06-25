<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftPlayerEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_player_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('player_uuid')->index();
            $table->string('player_username');
            $table->string('ip_address')->nullable();
            $table->integer('player_ping')->nullable();

            $table->integer('mob_kills')->default(0);
            $table->integer('player_kills')->default(0);
            $table->integer('deaths')->default(0);
            $table->integer('afk_time')->default(0);
            $table->integer('items_used')->default(0);
            $table->integer('items_mined')->default(0);
            $table->integer('items_picked_up')->default(0);
            $table->integer('items_dropped')->default(0);
            $table->integer('items_broken')->default(0);
            $table->integer('items_crafted')->default(0);
            $table->integer('items_placed')->default(0);
            $table->integer('items_consumed')->default(0);
            $table->json('world_location')->nullable(); // world location during the report if player was online

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
        Schema::dropIfExists('minecraft_player_events');
    }
}
