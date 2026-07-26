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
        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // public order number, and the gateway metadata key

            // Buyer. user_id is null for guest checkout.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();

            // Recipient. Snapshotted so delivery works for players unknown to the website.
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->char('player_uuid', 36);
            $table->string('player_username');

            // Money. Every column below is in `currency`; base_total mirrors total in the
            // reporting currency at the rate in force when the order was placed, so revenue
            // reporting never has to re-convert historical orders at today's rate.
            $table->char('currency', 3);
            $table->char('base_currency', 3);
            $table->decimal('exchange_rate', 20, 10)->default(1);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('sale_discount')->default(0);
            $table->unsignedBigInteger('coupon_discount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('gift_card_amount')->default(0);
            $table->unsignedBigInteger('amount_due')->default(0); // what the gateway charges
            $table->unsignedBigInteger('base_total')->default(0);

            $table->foreignId('store_coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable(); // snapshot
            $table->foreignId('store_gift_card_id')->nullable()->constrained()->nullOnDelete();

            $table->string('status'); // pending, paid, completed, cancelled, refunded, partially_refunded, chargeback
            $table->string('delivery_status')->default('pending'); // pending, partial, delivered, failed
            $table->string('gateway')->nullable();

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['player_uuid', 'status']);
            $table->index('delivery_status');
        });

        Schema::create('store_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_package_id')->nullable()->constrained()->nullOnDelete();

            // Full snapshot: survives the package being edited, renamed or soft-deleted.
            $table->string('package_name');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price_original'); // before any sale
            $table->unsignedBigInteger('unit_price');          // after sale, before coupon
            $table->unsignedBigInteger('total');
            $table->string('sale_name')->nullable();
            $table->json('options')->nullable(); // includes resolved placeholders
            $table->unsignedInteger('expiry_duration_days')->nullable();

            $table->timestamps();
        });

        Schema::create('store_package_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_package_id')->nullable()->constrained()->nullOnDelete();
            $table->char('player_uuid', 36)->index();
            $table->string('status')->default('active'); // active, expired, revoked
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable(); // null = permanent
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']); // drives the expiry sweep
        });

        Schema::create('store_order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_package_command_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trigger'); // purchase, expiry, refund, chargeback
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();

            // nullOnDelete, unlike command_queues.server_id which cascades. Deleting a server
            // destroys its queue rows; this table keeps the audit trail of what was attempted.
            $table->foreignId('command_queue_id')->nullable()->constrained()->nullOnDelete();

            $table->text('parsed_command'); // snapshot of exactly what was queued
            $table->unsignedInteger('repeat_index')->default(0); // for repeat-per-quantity rows
            $table->unsignedInteger('redispatch_count')->default(0);
            $table->timestamps();

            // The idempotency guard. Without it a webhook replay or an admin "retry all failed"
            // would deliver the same purchase twice.
            $table->unique(
                ['store_order_item_id', 'store_package_command_id', 'server_id', 'trigger', 'repeat_index'],
                'store_order_deliveries_unique_dispatch'
            );
        });

        Schema::create('store_gift_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_gift_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // issue, redeem, reversal, adjustment
            $table->bigInteger('amount'); // signed, minor units in the gift card's currency
            $table->unsignedBigInteger('balance_after');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_gift_card_transactions');
        Schema::dropIfExists('store_order_deliveries');
        Schema::dropIfExists('store_package_grants');
        Schema::dropIfExists('store_order_items');
        Schema::dropIfExists('store_orders');
    }
};
