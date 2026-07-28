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
        Schema::create('store_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('store_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true); // hidden categories are reachable by direct link only
            $table->boolean('is_enabled')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('store_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable(); // rich text
            $table->unsignedBigInteger('price'); // minor units, in the store base currency
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('requires_login')->default(false);

            // Delivery is decided entirely per command — which servers, whether the player must be
            // online, whether it repeats per unit. A package-wide default meant you could not tell
            // what a command would do without looking somewhere else.

            // Purchase constraints
            $table->unsignedSmallInteger('min_quantity')->default(1);
            $table->unsignedSmallInteger('max_quantity')->nullable();
            $table->unsignedInteger('stock_limit')->nullable(); // total ever sellable
            $table->unsignedInteger('player_purchase_limit')->nullable();
            $table->unsignedInteger('purchase_limit_period_days')->nullable(); // null = lifetime limit
            $table->unsignedInteger('sold_count')->default(0);

            $table->unsignedInteger('expiry_duration_days')->nullable(); // null = permanent

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes(); // order items snapshot everything, so deleted packages stay safe

            $table->index(['is_enabled', 'is_visible']);
        });

        Schema::create('store_package_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_id')->constrained()->cascadeOnDelete();
            $table->string('trigger'); // purchase, expiry, refund, chargeback
            $table->text('command');   // raw, with {PLACEHOLDER}s
            $table->boolean('is_player_online_required')->default(false);
            $table->unsignedInteger('delay_seconds')->default(0); // becomes command_queues.execute_at
            $table->boolean('is_repeat_per_quantity')->default(false); // else {QUANTITY} is substituted

            // Mirrors the account-link command convention: picking no servers means all of them,
            // and this flag records that choice so a server added later is included automatically.
            $table->boolean('is_run_on_all_servers')->default(true);

            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['store_package_id', 'trigger']);
        });

        Schema::create('store_package_command_server', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_command_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['store_package_command_id', 'server_id'], 'store_package_command_server_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_package_command_server');
        Schema::dropIfExists('store_package_commands');
        Schema::dropIfExists('store_packages');
        Schema::dropIfExists('store_categories');
    }
};
