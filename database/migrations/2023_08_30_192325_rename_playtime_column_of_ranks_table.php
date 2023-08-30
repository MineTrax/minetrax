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
        Schema::table('ranks', function (Blueprint $table) {
            $table->renameColumn('total_play_one_minute_needed', 'total_play_time_needed');
        });

        // Update old data from Ticks to Seconds
        DB::table('ranks')->update([
            'total_play_time_needed' => DB::raw('total_play_time_needed / 20'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            $table->renameColumn('total_play_time_needed', 'total_play_one_minute_needed');
        });

        DB::table('ranks')->update([
            'total_play_one_minute_needed' => DB::raw('total_play_one_minute_needed * 20'),
        ]);
    }
};
