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
        Schema::create('store_carts', function (Blueprint $table) {
            $table->id();

            // Exactly one of these is set: a logged-in user's cart, or a guest cookie token.
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('session_token', 64)->nullable()->unique();

            // Who the purchase is for. Snapshotted onto the order at checkout.
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->char('player_uuid', 36)->nullable();
            $table->string('player_username')->nullable();

            $table->char('currency_code', 3)->nullable(); // null = resolve at render time
            $table->foreignId('store_coupon_id')->nullable();
            $table->foreignId('store_gift_card_id')->nullable();

            $table->timestamps();

            $table->index('updated_at'); // pruning abandoned carts
        });

        Schema::create('store_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_package_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);

            // Deliberately no cached package price: carts are always priced live, which is what
            // stops a stale or tampered price reaching checkout.
            //
            // A pay-what-you-want amount is the exception, because it is buyer input rather than a
            // copy of anything: it is kept in the currency it was typed in so switching currency
            // converts what the buyer chose instead of silently re-rounding it.
            $table->unsignedBigInteger('custom_price')->nullable();
            $table->char('custom_price_currency', 3)->nullable();

            // What the buyer typed into the package's variables, keyed by identifier. Re-adding the
            // package replaces this rather than making a second line: one configuration per package
            // per cart is predictable, and two configurations have no meaningful merge.
            $table->json('variable_values')->nullable();

            $table->timestamps();

            // One line per package, so adding the same package again bumps the quantity.
            $table->unique(['store_cart_id', 'store_package_id'], 'store_cart_items_unique_line');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_cart_items');
        Schema::dropIfExists('store_carts');
    }
};
