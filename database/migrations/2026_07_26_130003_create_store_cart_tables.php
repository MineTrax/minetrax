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
            $table->json('selected_options')->nullable();

            // md5 of the normalised option selection, so identical configurations merge into one
            // line instead of stacking. Deliberately no price columns: carts are always priced
            // live, which is what stops a stale or tampered price reaching checkout.
            $table->string('options_signature', 32);

            $table->timestamps();

            $table->unique(['store_cart_id', 'store_package_id', 'options_signature'], 'store_cart_items_unique_line');
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
