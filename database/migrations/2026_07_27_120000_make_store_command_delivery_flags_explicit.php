<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every command decides for itself whether the player must be online and whether it repeats
     * per quantity.
     *
     * The nullable "inherit from the package" state cost more than it bought: an admin reading a
     * command could not tell what it would actually do without going back up to the package, and
     * the package-level defaults existed only to feed it.
     */
    public function up(): void
    {
        // Resolve every inherited value to what it currently evaluates to, so no command changes
        // behaviour as a result of this migration.
        DB::statement('
            UPDATE store_package_commands c
            JOIN store_packages p ON p.id = c.store_package_id
            SET c.is_player_online_required = COALESCE(c.is_player_online_required, p.is_player_online_required),
                c.is_repeat_per_quantity = COALESCE(c.is_repeat_per_quantity, p.is_command_repeated_per_quantity)
        ');

        // A command whose package is already gone has nothing to inherit from.
        DB::table('store_package_commands')->whereNull('is_player_online_required')->update(['is_player_online_required' => false]);
        DB::table('store_package_commands')->whereNull('is_repeat_per_quantity')->update(['is_repeat_per_quantity' => false]);

        Schema::table('store_package_commands', function (Blueprint $table) {
            $table->boolean('is_player_online_required')->default(false)->nullable(false)->change();
            $table->boolean('is_repeat_per_quantity')->default(false)->nullable(false)->change();
        });

        Schema::table('store_packages', function (Blueprint $table) {
            $table->dropColumn(['is_player_online_required', 'is_command_repeated_per_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_packages', function (Blueprint $table) {
            $table->boolean('is_player_online_required')->default(false);
            $table->boolean('is_command_repeated_per_quantity')->default(false);
        });

        Schema::table('store_package_commands', function (Blueprint $table) {
            $table->boolean('is_player_online_required')->nullable()->change();
            $table->boolean('is_repeat_per_quantity')->nullable()->change();
        });
    }
};
