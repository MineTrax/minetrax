<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServerChatlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_chatlogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->nullable()->constrained('servers')->cascadeOnDelete();
            $table->text('data')->nullable();
            $table->uuid('causer_uuid')->nullable();
            $table->string('causer_username')->nullable();
            $table->string('channel')->nullable(); // Eg: global, admin
            $table->string('type')->nullable();  // Eg: chat, death, etc
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
        Schema::dropIfExists('server_chatlogs');
    }
}
