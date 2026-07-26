<?php

namespace App\Models;

use App\Enums\StoreDiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    public function couponables(): HasMany
    {
        return $this->hasMany(StoreCouponable::class, 'store_coupon_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(StoreOrder::class, 'store_coupon_id');
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
