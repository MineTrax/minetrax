<?php

namespace App\Models;

use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentGateway;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class StoreOrder extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'status' => StoreOrderStatus::class,
        'delivery_status' => StoreDeliveryStatus::class,
        'gateway' => StorePaymentGateway::class,
        'exchange_rate' => 'decimal:10',
        'subtotal' => 'integer',
        'sale_discount' => 'integer',
        'coupon_discount' => 'integer',
        'tax_amount' => 'integer',
        'tax_rate_bp' => 'integer',
        'tax_is_inclusive' => 'boolean',
        'total' => 'integer',
        'gift_card_amount' => 'integer',
        'amount_due' => 'integer',
        'base_total' => 'integer',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StoreOrderItem::class, 'store_order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StorePayment::class, 'store_order_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(StoreOrderDelivery::class, 'store_order_id');
    }

    /**
     * The coupons that priced this order, and what each one took off.
     *
     * hasMany to the snapshot rows rather than belongsToMany through to the coupons themselves: a
     * deleted coupon nulls the pivot's id, and a receipt still has to name what was applied. The
     * live coupon, when there is one, hangs off each row's own `coupon` relation.
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(StoreOrderCoupon::class, 'store_order_id');
    }

    /**
     * The creator code that brought this sale in.
     *
     * withTrashed(), so a retired code still resolves for an order that already credited it — the
     * earnings and the payouts against it outlive the code itself.
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(StoreReferral::class, 'store_referral_id')->withTrashed();
    }

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(StoreGiftCard::class, 'store_gift_card_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The order's history: paid, refunded, resent, and who did it.
     *
     * A plain morphMany rather than spatie's LogsActivity trait, because nothing here is logged
     * automatically — StoreOrderService writes each line deliberately, so the trait's attribute
     * diffing would only add noise.
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->oldest('id');
    }

    /**
     * Whether ExpireStalePendingStoreOrdersJob would already consider this order abandoned.
     *
     * On the model rather than in a controller because two screens ask it — the payment page, which
     * refuses to open a session past the window, and the purchase list, which must not offer a
     * "finish paying" button the payment page will then turn away.
     */
    public function isPastPaymentWindow(): bool
    {
        return $this->created_at->lt(
            now()->subHours((int) config('store.pending_order_ttl_hours', 24))
        );
    }

    /**
     * Whether the buyer can still take this order to a gateway and pay it.
     */
    public function isResumable(): bool
    {
        return $this->status === StoreOrderStatus::PENDING && ! $this->isPastPaymentWindow();
    }
}
