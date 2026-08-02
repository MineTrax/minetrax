<?php

namespace App\Models;

use App\Enums\StoreDiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What one coupon took off one order.
 *
 * An order may carry several: one exclusive coupon plus any number of stackable ones. The
 * descriptive columns are snapshots taken at checkout, so a receipt still reads correctly after the
 * coupon is renamed, re-rated or deleted — the same reasoning as `tax_name` and `referral_share_bp`
 * on the order itself. `store_coupon_id` is the live link that survives all but deletion, and is
 * what the reserve/release of `used_count` counts through.
 */
class StoreOrderCoupon extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'discount_type' => StoreDiscountType::class,
        'discount_value' => 'integer',
        'discount_amount' => 'integer',
        'is_stackable' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }

    /**
     * The coupon itself, when it still exists.
     *
     * Null once the coupon is deleted — which is why every figure this row displays is snapshotted
     * beside the id rather than read back through here.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(StoreCoupon::class, 'store_coupon_id');
    }
}
