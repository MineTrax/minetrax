<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftServerLiveInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_server_live_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('online_players');
            $table->integer('max_players');
            $table->float('tps');
            $table->integer('chunks_loaded')->nullable();
            $table->bigInteger('max_memory')->nullable();
            $table->bigInteger('total_memory')->nullable();
            $table->bigInteger('free_memory')->nullable();
            $table->bigInteger('used_memory')->nullable();
            $table->integer('available_cpu_count')->nullable();
            $table->float('cpu_load')->nullable();
            $table->integer('uptime')->nullable();
            $table->bigInteger('free_disk_in_kb')->nullable();
            $table->text('motd')->nullable();
            $table->string("server_version")->nullable();
            $table->string("server_session_id")->nullable();   // A unique identifier, changes only when server restart

            $table->foreignId('server_id')->constrained('servers')->onDelete('cascade');
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
        Schema::dropIfExists('minecraft_server_live_infos');
    }
}
