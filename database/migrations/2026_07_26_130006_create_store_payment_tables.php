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
        // One row per gateway the application ships a driver for. Rows rather than a settings key
        // so a gateway is a first-class record: it can be listed, ordered, and audited, and adding
        // a provider later is a seeder run that leaves every configured gateway untouched.
        Schema::create('store_payment_gateways', function (Blueprint $table) {
            $table->id();
            // Matches the key in config('store.gateways') and the StorePaymentGateway enum.
            $table->string('key')->unique();
            $table->boolean('is_enabled')->default(false);
            // Encrypted at rest by the model cast, as the settings bag was. Shape is per driver,
            // defined by its settingsSchema().
            $table->text('credentials')->nullable();
            // The order the methods are offered in at checkout.
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // One row per charge attempt, so a buyer who abandons Stripe and retries with PayPal
        // leaves two rows against one order.
        Schema::create('store_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // binds the gateway return URL back to this attempt
            $table->foreignId('store_order_id')->constrained()->cascadeOnDelete();

            $table->string('gateway'); // stripe, paypal, manual, giftcard, free
            $table->string('gateway_session_id')->nullable()->unique();
            $table->string('gateway_transaction_id')->nullable()->unique();

            $table->string('status'); // pending, completed, failed, refunded, partially_refunded, chargeback
            $table->unsignedBigInteger('amount');
            $table->char('currency', 3);
            $table->unsignedBigInteger('fee_amount')->nullable();
            $table->unsignedBigInteger('refunded_amount')->default(0);

            $table->json('payload')->nullable(); // raw gateway response, for audit
            $table->string('failure_reason')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'status']);
        });

        Schema::create('store_payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_payment_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // refund, chargeback
            $table->string('gateway_refund_id')->nullable()->unique();
            $table->unsignedBigInteger('amount');
            $table->char('currency', 3);
            $table->string('reason')->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Replay guard. Gateways retry webhooks aggressively and will resend an event the
        // moment a response is slow, so processing must be keyed on the event id.
        Schema::create('store_gateway_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');
            $table->string('event_id');
            $table->string('type')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('error')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_gateway_webhooks');
        Schema::dropIfExists('store_payment_refunds');
        Schema::dropIfExists('store_payments');
        Schema::dropIfExists('store_payment_gateways');
    }
};
