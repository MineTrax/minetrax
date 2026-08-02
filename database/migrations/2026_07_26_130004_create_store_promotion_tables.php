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
        Schema::create('store_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // stored uppercase
            $table->string('description')->nullable();

            $table->string('discount_type'); // percent, fixed
            // percent => basis points (2000 = 20%); fixed => minor units in currency_code
            $table->unsignedBigInteger('discount_value');
            $table->char('currency_code', 3)->nullable(); // fixed only; null = base, converted

            $table->unsignedBigInteger('min_basket_amount')->nullable(); // minor units, base currency
            $table->unsignedInteger('max_uses_total')->nullable();
            $table->unsignedInteger('max_uses_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0); // reserved at order creation

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_enabled')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // No rows for a coupon means it applies store-wide.
        Schema::create('store_couponables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_coupon_id')->constrained()->cascadeOnDelete();
            $table->morphs('couponable'); // StorePackage or StoreCategory
            $table->timestamps();
        });

        Schema::create('store_sales', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // shown as the badge label
            $table->string('discount_type'); // percent, fixed
            $table->unsignedBigInteger('discount_value');

            // What the sale covers: all, categories, packages. Held explicitly rather than inferred
            // from whether store_saleables has rows, because inferring made an emptied picker
            // silently promote a targeted sale to a store-wide one — the most expensive possible
            // way to mis-save a form.
            $table->string('scope_type')->default('all');

            // The basket must reach this before the sale applies at all. Minor units of the base
            // currency, like a fixed discount_value, converted for a buyer paying in something
            // else. Measured against the basket *before* any sale or upgrade credit — see
            // StorePricingService::qualifyingSubtotal() for why it cannot be measured after.
            $table->unsignedBigInteger('min_basket_amount')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_enabled')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Order items point at the sale that priced them, and a sale's refund, chargeback and
            // expiry commands resolve live at trigger time. Hard-deleting a finished sale would
            // silently stop it clawing back the bonus it handed out, months after the fact.
            $table->softDeletes();

            $table->index(['is_enabled', 'starts_at', 'ends_at']);
        });

        // Which packages or categories a scoped sale covers. Sales never stack: the single largest
        // saving wins. scope_type on store_sales says which of the two morph types is in play.
        Schema::create('store_saleables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_sale_id')->constrained()->cascadeOnDelete();
            $table->morphs('saleable'); // StorePackage or StoreCategory
            $table->timestamps();
        });

        // Added here rather than in the catalog migration: store_package_commands is created before
        // store_sales exists, so the constraint has nowhere to point until now.
        Schema::table('store_package_commands', function (Blueprint $table) {
            // cascadeOnDelete, matching store_package_id. nullOnDelete would leave a row owned by
            // nothing — invisible to both $package->commands and $sale->commands, and unreachable
            // from either admin form.
            $table->foreignId('store_sale_id')->nullable()->constrained()->cascadeOnDelete();

            $table->index(['store_sale_id', 'trigger']);
        });

        // Which packages a sale's command applies to: a "Bonus Coins" sale giving 100 on one package
        // and 1000 on another is two commands, each naming its package. No rows means every package
        // the sale discounted, which is what store_package_commands.is_run_on_all_packages records.
        Schema::create('store_package_command_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_command_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_package_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['store_package_command_id', 'store_package_id'], 'store_package_command_package_unique');
        });

        Schema::create('store_gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->char('currency_code', 3);
            $table->unsignedBigInteger('original_balance'); // minor units
            $table->unsignedBigInteger('balance');          // mutated under a row lock
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_enabled')->default(true);

            $table->foreignId('issued_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_package_command_package');

        if (Schema::hasColumn('store_package_commands', 'store_sale_id')) {
            Schema::table('store_package_commands', function (Blueprint $table) {
                $table->dropIndex(['store_sale_id', 'trigger']);
                // Drops the constraint and the column together; dropColumn on its own fails while
                // the foreign key still references store_sales.
                $table->dropConstrainedForeignId('store_sale_id');
            });
        }

        Schema::dropIfExists('store_gift_cards');
        Schema::dropIfExists('store_saleables');
        Schema::dropIfExists('store_sales');
        Schema::dropIfExists('store_couponables');
        Schema::dropIfExists('store_coupons');
    }
};
