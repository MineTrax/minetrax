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

            // Delivery behaviour
            $table->boolean('is_run_on_all_servers')->default(false);
            $table->boolean('is_player_online_required')->default(false); // default inherited by commands
            $table->boolean('is_command_repeated_per_quantity')->default(false); // else {QUANTITY} is substituted

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

        Schema::create('store_package_server', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['store_package_id', 'server_id']);
        });

        Schema::create('store_package_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('placeholder'); // UPPER_SNAKE, substituted into commands as {PLACEHOLDER}
            $table->string('type')->default('select'); // select (text, number reserved for later)
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['store_package_id', 'placeholder']);
        });

        Schema::create('store_package_option_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_option_id')->constrained()->cascadeOnDelete();
            $table->string('name');  // shown to the buyer
            $table->string('value'); // substituted into the command
            $table->bigInteger('price_delta')->default(0); // signed minor units, in base currency
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('store_package_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_id')->constrained()->cascadeOnDelete();
            $table->string('trigger'); // purchase, expiry, refund, chargeback
            $table->text('command');   // raw, with {PLACEHOLDER}s
            $table->boolean('is_player_online_required')->nullable(); // null = inherit from package
            $table->unsignedInteger('delay_seconds')->default(0);     // becomes command_queues.execute_at
            $table->string('target')->default('package_servers');     // package_servers, all_servers
            $table->boolean('is_repeat_per_quantity')->nullable();    // null = inherit from package
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['store_package_id', 'trigger']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_package_commands');
        Schema::dropIfExists('store_package_option_choices');
        Schema::dropIfExists('store_package_options');
        Schema::dropIfExists('store_package_server');
        Schema::dropIfExists('store_packages');
        Schema::dropIfExists('store_categories');
    }
};
