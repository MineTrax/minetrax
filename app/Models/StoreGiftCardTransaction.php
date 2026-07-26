<?php

namespace App\Models;

use App\Enums\StoreGiftCardTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreGiftCardTransaction extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'type' => StoreGiftCardTransactionType::class,
        'amount' => 'integer',
        'balance_after' => 'integer',
    ];

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(StoreGiftCard::class, 'store_gift_card_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
