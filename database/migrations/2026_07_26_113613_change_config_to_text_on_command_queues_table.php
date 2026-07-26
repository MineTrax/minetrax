<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `config` was created as a string (VARCHAR 255) but is cast to an array on the model, so any
     * payload over 255 characters is silently truncated into invalid JSON. Store deliveries carry
     * richer config than the account-link commands this column was originally built for.
     */
    public function up(): void
    {
        Schema::table('command_queues', function (Blueprint $table) {
            $table->text('config')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('command_queues', function (Blueprint $table) {
            $table->string('config')->nullable()->change();
        });
    }
};
