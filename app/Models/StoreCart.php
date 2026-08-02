<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreCart extends BaseModel
{
    use HasFactory, Prunable;

    public function prunable()
    {
        return static::where('updated_at', '<=', now()->subDays(config('store.cart_ttl_days', 30)));
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
        return $this->hasMany(StoreCartItem::class, 'store_cart_id');
    }

    /**
     * Coupons the buyer has attached: at most one exclusive, plus any stackable ones.
     *
     * A relation rather than a column, so the one-exclusive rule lives in StoreCartService where it
     * can be explained to the buyer, instead of being an overwrite they never see happen. Joined
     * through the pivot, so a coupon deleted out from under a cart simply stops appearing.
     */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(StoreCoupon::class, 'store_cart_coupons')->withTimestamps();
    }

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(StoreGiftCard::class, 'store_gift_card_id');
    }
}
