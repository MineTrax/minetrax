<?php

namespace App\Models;

use App\Enums\StoreDiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreCoupon extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'discount_type' => StoreDiscountType::class,
        'discount_value' => 'integer',
        'min_basket_amount' => 'integer',
        'max_uses_total' => 'integer',
        'max_uses_per_user' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_enabled' => 'boolean',
        'is_stackable' => 'boolean',
    ];

    public function couponables(): HasMany
    {
        return $this->hasMany(StoreCouponable::class, 'store_coupon_id');
    }

    /**
     * Orders this coupon priced.
     *
     * Through the pivot rather than a column on the order, because an order may carry several
     * coupons. This is what the per-user usage limit counts, so it has to stay in step with what
     * checkout writes.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(StoreOrder::class, 'store_order_coupons')
            ->withPivot(['code', 'discount_amount'])
            ->withTimestamps();
    }

    /**
     * Whether this coupon rides on top of others rather than replacing them.
     *
     * A basket holds at most one exclusive coupon plus any number of stackable ones.
     */
    public function isStackable(): bool
    {
        return (bool) $this->is_stackable;
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
