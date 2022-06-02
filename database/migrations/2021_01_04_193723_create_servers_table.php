<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->string('join_port');
            $table->string('query_port')->nullable();
            $table->string('webquery_port')->nullable();
            $table->string('hostname')->nullable();
            $table->smallInteger('type')->default(0);   // 0 -> Survival Like
            $table->string('minecraft_version')->nullable();
            $table->string('name')->nullable();
            $table->string('level_name')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('order')->nullable();
            $table->json('settings')->nullable();
            $table->longText('storage_login')->nullable();

            $table->boolean('is_stats_tracking_enabled')->default(true);
            $table->boolean('is_ingame_chat_enabled')->default(true);
            $table->boolean('is_online_players_query_enabled')->default(true);

            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->timestamp('last_scanned_at')->nullable();
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
        Schema::dropIfExists('servers');
    }
}
