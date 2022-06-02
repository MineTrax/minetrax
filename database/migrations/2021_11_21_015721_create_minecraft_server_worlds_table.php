<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftServerWorldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_server_worlds', function (Blueprint $table) {
            $table->id();
            $table->string('world_name');
            $table->bigInteger('world_border')->nullable();
            $table->string('environment')->nullable();
            // TODO Add other world information nice to have

            $table->foreignId('server_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('minecraft_server_worlds');
    }
}
