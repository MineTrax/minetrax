<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinecraftPlayerSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minecraft_player_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('player_uuid')->index();
            $table->string('player_username');
            $table->string('player_displayname')->nullable();
            $table->timestamp('session_started_at');
            $table->timestamp('session_ended_at')->nullable();
            $table->integer('mob_kills')->default(0);
            $table->integer('player_kills')->default(0);
            $table->integer('deaths')->default(0);
            $table->integer('afk_time')->default(0);
            $table->integer('play_time')->default(0);   // total time player online for (including afk)
            $table->string('player_ip_address')->nullable();
            $table->boolean('is_op')->default(false);
            $table->boolean('is_kicked')->default(false);
            $table->boolean('is_banned')->default(false);

            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
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
        Schema::dropIfExists('minecraft_player_sessions');
    }
}
