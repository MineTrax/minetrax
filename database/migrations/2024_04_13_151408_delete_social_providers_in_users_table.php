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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('provider_name');
            $table->dropColumn('provider_id');
            $table->string('discord_user_id')->nullable();
            $table->string('discord_private_channel_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('discord_user_id');
            $table->dropColumn('discord_private_channel_id');
            $table->string('provider_name')->nullable();
            $table->string('provider_id')->nullable();
        });
    }
};
