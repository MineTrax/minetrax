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
        // A creator code. Someone sends buyers to the store with ?ref=THEIRCODE, and earns a share
        // of what those buyers spend.
        //
        // Named referral rather than creator on purpose: created_by, creator() and
        // ScopesToCreatorTrait already mean "the staff member who made this record" across every
        // store policy, and one word cannot mean both things in the same model.
        Schema::create('store_referrals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // stored uppercase, matched case-insensitively
            $table->string('referrer_name');  // shown to buyers: "Supporting Kakamora"

            // The member this code belongs to, when it belongs to one. Two jobs: it unlocks their
            // own stats page, and it stops a code earning its holder a commission on their own
            // purchases. Null is fine — a code can be handed to someone with no account here.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Basis points of the sale, like every other percentage in the store. 500 = 5%.
            $table->unsignedInteger('share_bp')->default(0);

            // What the buyer gets for using the code. Optional: a code can be pure attribution.
            $table->foreignId('store_coupon_id')->nullable()->constrained()->nullOnDelete();

            $table->boolean('is_url_tracking_enabled')->default(true);

            // How long a ?ref= visit keeps earning. Null is a lifetime cookie.
            $table->unsignedInteger('attribution_window_days')->nullable();

            // What an arriving code does when the visitor already carries one.
            $table->string('attribution_mode')->default('first_touch'); // first_touch, last_touch, extend_window

            $table->boolean('is_command_execution_enabled')->default(false);

            $table->unsignedBigInteger('visit_count')->default(0);
            $table->timestamp('last_visited_at')->nullable();

            $table->boolean('is_enabled')->default(true);
            $table->string('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Orders and payouts point here, and both are part of a money trail. Hard-deleting a
            // code would strand what it earned and what was paid against it — the same reasoning
            // that makes store_sales soft-delete.
            $table->softDeletes();

            $table->index('is_enabled');
        });

        // Money actually handed over. What is *owed* is never stored: it is the earnings on the
        // referral's orders minus the sum of these rows. A stored balance would be a second copy of
        // something the orders already say, and a refund landing weeks later would leave the two
        // permanently disagreeing.
        Schema::create('store_referral_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_referral_id')->constrained()->cascadeOnDelete();

            $table->unsignedBigInteger('amount'); // minor units

            // The base currency as it stood when this was paid. Snapshotted rather than read live,
            // so the history still reads correctly if the base currency is ever changed.
            $table->char('currency', 3);

            $table->string('reference')->nullable(); // PayPal transaction, bank reference
            $table->string('note')->nullable();
            $table->timestamp('paid_at');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['store_referral_id', 'paid_at']);
        });

        Schema::table('store_orders', function (Blueprint $table) {
            // nullOnDelete and a snapshot beside it, exactly like store_coupon_id / coupon_code:
            // renaming or removing a code later must not rewrite what an order recorded at the time.
            $table->foreignId('store_referral_id')->nullable()->after('store_gift_card_id')->constrained()->nullOnDelete();
            $table->string('referral_code')->nullable()->after('store_referral_id');

            // The rate in force when the order was placed. Changing a referral's share must not
            // retroactively re-price what it already earned.
            $table->unsignedInteger('referral_share_bp')->nullable()->after('referral_code');

            // In the order's own currency, and in the base currency at the order's own snapshotted
            // rate — the same pairing as total / base_total, and for the same reason: reporting has
            // to sum across currencies without re-converting history at today's rate.
            $table->unsignedBigInteger('referral_earning')->nullable()->after('referral_share_bp');
            $table->unsignedBigInteger('referral_earning_base')->nullable()->after('referral_earning');

            $table->string('referral_source')->nullable()->after('referral_earning_base'); // url, manual

            $table->index(['store_referral_id', 'status']);
        });

        Schema::table('store_carts', function (Blueprint $table) {
            // Plain column with no constraint, matching store_coupon_id and store_gift_card_id
            // beside it. A cart is disposable; a dangling id here costs nothing and is pruned.
            $table->unsignedBigInteger('store_referral_id')->nullable()->after('store_gift_card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_carts', function (Blueprint $table) {
            $table->dropColumn('store_referral_id');
        });

        Schema::table('store_orders', function (Blueprint $table) {
            // In this order. MySQL enforces the foreign key through the index, so the index cannot
            // go first, and the column cannot go while either still references it.
            $table->dropForeign(['store_referral_id']);
            $table->dropIndex(['store_referral_id', 'status']);
            $table->dropColumn([
                'store_referral_id',
                'referral_code',
                'referral_share_bp',
                'referral_earning',
                'referral_earning_base',
                'referral_source',
            ]);
        });

        Schema::dropIfExists('store_referral_payouts');
        Schema::dropIfExists('store_referrals');
    }
};
