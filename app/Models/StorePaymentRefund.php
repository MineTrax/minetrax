<?php

namespace App\Models;

use App\Enums\StorePaymentRefundType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePaymentRefund extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'type' => StorePaymentRefundType::class,
        'amount' => 'integer',
        'payload' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(StorePayment::class, 'store_payment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
