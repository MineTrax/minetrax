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
            $table->string('type')->default('minecraft_package'); // minecraft_package, giftcard, both
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('requires_login')->default(false);
            $table->boolean('is_featured')->default(false); // pinned to the top of its category
            // Whether the buyer may have this delivered to a player that is not their own.
            $table->boolean('is_giftable')->default(false);

            // Delivery is decided entirely per command — which servers, whether the player must be
            // online, whether it repeats per unit. A package-wide default meant you could not tell
            // what a command would do without looking somewhere else.

            // Pricing. Every amount here is minor units of the store base currency.
            $table->unsignedBigInteger('price'); // the minimum when pay-what-you-want is on
            $table->unsignedInteger('discount_bp')->default(0); // basis points off the price; 2000 = 20%
            $table->boolean('is_pay_what_you_want')->default(false);
            $table->unsignedBigInteger('pay_what_you_want_max')->nullable(); // null = uncapped

            // Gift card issued to the buyer on purchase (type giftcard or both).
            $table->unsignedBigInteger('gift_card_amount')->nullable();
            $table->boolean('is_gift_card_amount_same_as_price')->default(false);

            // Purchase constraints. Each limit is a quantity cap over a rolling window; a null
            // period means the window never resets, which is how a fixed stock is expressed.
            $table->unsignedSmallInteger('min_quantity')->default(1);
            $table->unsignedSmallInteger('max_quantity')->nullable();
            $table->unsignedInteger('player_purchase_limit')->nullable();
            $table->unsignedInteger('player_purchase_limit_period_days')->nullable();
            $table->unsignedInteger('global_purchase_limit')->nullable();
            $table->unsignedInteger('global_purchase_limit_period_days')->nullable();
            $table->unsignedInteger('sold_count')->default(0);

            // Whether every listed requirement must be owned, or any one of them. See
            // store_package_requirement below.
            $table->string('required_packages_mode')->default('all'); // all, any

            $table->unsignedInteger('expiry_duration_days')->nullable(); // null = permanent

            // The publish window. Outside it the package is neither listed nor purchasable, which
            // is evaluated on read rather than flipped by a cron so there is nothing to drift.
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes(); // order items snapshot everything, so deleted packages stay safe

            $table->index(['is_enabled', 'is_visible']);
            $table->index(['available_from', 'available_until']);
        });

        // Packages the buyer must already own before this one can be bought. Read together with
        // store_packages.required_packages_mode.
        Schema::create('store_package_requirement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('required_store_package_id')->constrained('store_packages')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['store_package_id', 'required_store_package_id'], 'store_package_requirement_unique');
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
        Schema::dropIfExists('store_package_requirement');
        Schema::dropIfExists('store_packages');
        Schema::dropIfExists('store_categories');
    }
};
