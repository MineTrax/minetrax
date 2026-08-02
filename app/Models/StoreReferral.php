<?php

namespace App\Models;

use App\Enums\StoreOrderStatus;
use App\Enums\StoreReferralAttributionMode;
use App\Traits\HasStoreCommandsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A creator code: someone sends buyers to the store and earns a share of what they spend.
 *
 * Owns store commands through HasStoreCommandsTrait, so a referred sale can also run something in
 * game. Registered in config('store.command_owners') with `purchase` as its only trigger.
 */
class StoreReferral extends BaseModel
{
    use HasFactory, HasStoreCommandsTrait, SoftDeletes;

    protected $casts = [
        'share_bp' => 'integer',
        'attribution_mode' => StoreReferralAttributionMode::class,
        'attribution_window_days' => 'integer',
        'is_url_tracking_enabled' => 'boolean',
        'is_command_execution_enabled' => 'boolean',
        'is_enabled' => 'boolean',
        'visit_count' => 'integer',
        'last_visited_at' => 'datetime',
    ];

    /**
     * The order states that count towards what a referrer has earned.
     *
     * Derived from isPaidState() rather than listed, so it cannot drift from the state machine: a
     * cancelled order was never paid, and a fully refunded or charged-back one has had its money
     * taken back, so neither owes anybody a commission.
     *
     * @return array<int, string>
     */
    public static function earningStatuses(): array
    {
        return collect(StoreOrderStatus::cases())
            ->filter(fn (StoreOrderStatus $status) => $status->isPaidState())
            ->map(fn (StoreOrderStatus $status) => $status->value)
            ->values()
            ->all();
    }

    /**
     * Attach the two sums every surface that shows a balance needs.
     *
     * Defined once here rather than in each controller, because the listing, the admin detail page
     * and the referrer's own page all have to agree on what "owed" means, and three copies of a
     * money query is three chances to write it differently.
     */
    public function scopeWithBalance(Builder $query): Builder
    {
        return $query
            ->withSum(
                ['orders as earned_base' => fn (Builder $orders) => $orders->whereIn('status', self::earningStatuses())],
                'referral_earning_base'
            )
            ->withSum('payouts as paid_out', 'amount');
    }

    /**
     * Total earned, in minor units of the base currency.
     *
     * Prefers the aggregate scopeWithBalance() loaded, and falls back to its own query so a model
     * fetched without the scope still answers correctly instead of silently reading zero.
     */
    public function earnedBase(): int
    {
        if ($this->earned_base !== null) {
            return (int) $this->earned_base;
        }

        return (int) $this->orders()->whereIn('status', self::earningStatuses())->sum('referral_earning_base');
    }

    public function paidOut(): int
    {
        if ($this->paid_out !== null) {
            return (int) $this->paid_out;
        }

        return (int) $this->payouts()->sum('amount');
    }

    /**
     * What is still owed, in minor units of the base currency.
     *
     * Can legitimately be negative: a refund landing after a payout means the referrer was paid for
     * a sale that later unwound. That is carried against future earnings rather than clamped to
     * zero, because clamping would quietly forgive an overpayment the owner needs to see.
     */
    public function owed(): int
    {
        return $this->earnedBase() - $this->paidOut();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The discount buyers get for using this code. Optional — a code can be pure attribution.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(StoreCoupon::class, 'store_coupon_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(StoreOrder::class, 'store_referral_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(StoreReferralPayout::class, 'store_referral_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
