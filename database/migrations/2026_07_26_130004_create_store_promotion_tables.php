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
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_enabled')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['is_enabled', 'starts_at', 'ends_at']);
        });

        // No rows for a sale means it applies store-wide. Sales never stack: the single largest
        // saving wins.
        Schema::create('store_saleables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_sale_id')->constrained()->cascadeOnDelete();
            $table->morphs('saleable'); // StorePackage or StoreCategory
            $table->timestamps();
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
        Schema::dropIfExists('store_gift_cards');
        Schema::dropIfExists('store_saleables');
        Schema::dropIfExists('store_sales');
        Schema::dropIfExists('store_couponables');
        Schema::dropIfExists('store_coupons');
    }
};
