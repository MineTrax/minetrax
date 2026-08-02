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
        'upgrade_credit' => 'integer',
        'expiry_duration_days' => 'integer',
        'variable_values' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }

    /**
     * Packages soft-delete, and retiring one must not sever a sold order from it.
     *
     * The expiry, refund and chargeback command sets resolve live at trigger time, so without
     * withTrashed() a retired package would silently stop removing its own perk: the sweep marks the
     * grant EXPIRED and sends nothing, leaving the buyer with it forever. The sold_count give-back on
     * a refund is skipped the same way.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(StorePackage::class, 'store_package_id')->withTrashed();
    }

    /**
     * The sale this line was priced under.
     *
     * Sales soft-delete for the same reason packages do: the sale's refund, chargeback and expiry
     * commands resolve live at trigger time, so without withTrashed() a retired sale would silently
     * stop taking back the bonus it granted, and the buyer would keep it for nothing.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(StoreSale::class, 'store_sale_id')->withTrashed();
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
