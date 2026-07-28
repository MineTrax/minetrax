<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StoreOrderItem extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_original' => 'integer',
        'unit_price' => 'integer',
        'total' => 'integer',
        'expiry_duration_days' => 'integer',
        'variable_values' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(StorePackage::class, 'store_package_id');
    }

    public function grant(): HasOne
    {
        return $this->hasOne(StorePackageGrant::class, 'store_order_item_id');
    }

    /**
     * The gift card this item minted, for a package that sells store credit.
     */
    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(StoreGiftCard::class, 'store_gift_card_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(StoreOrderDelivery::class, 'store_order_item_id');
    }
}
