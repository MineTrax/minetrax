<?php

namespace App\Models;

use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentGateway;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(StoreCoupon::class, 'store_coupon_id');
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
}
