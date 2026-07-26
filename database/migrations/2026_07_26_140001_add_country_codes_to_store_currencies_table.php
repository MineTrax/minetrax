<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Country ISO codes whose visitors should default to this currency. An explicit list is used
     * rather than deriving one from `countries.currency`, which stores only a name and symbol
     * with no ISO-4217 code to match on.
     */
    public function up(): void
    {
        Schema::table('store_currencies', function (Blueprint $table) {
            $table->json('country_codes')->nullable()->after('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_currencies', function (Blueprint $table) {
            $table->dropColumn('country_codes');
        });
    }
};
