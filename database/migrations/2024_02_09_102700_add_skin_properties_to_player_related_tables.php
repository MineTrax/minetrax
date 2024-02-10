<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('minecraft_players', function (Blueprint $table) {
            $table->json('skin_property')->nullable();
            $table->string('skin_texture_id')->nullable();
        });

        Schema::table('players', function (Blueprint $table) {
            $table->json('skin_property')->nullable();
            $table->string('skin_texture_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('minecraft_players', function (Blueprint $table) {
            $table->dropColumn('skin_property');
            $table->dropColumn('skin_texture_id');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('skin_property');
            $table->dropColumn('skin_texture_id');
        });
    }
};
