<?php

namespace App\Models;

use App\Enums\StorePaymentGateway;
use App\Enums\StorePaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StorePayment extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'gateway' => StorePaymentGateway::class,
        'status' => StorePaymentStatus::class,
        'amount' => 'integer',
        'fee_amount' => 'integer',
        'refunded_amount' => 'integer',
        'payload' => 'array',
        'paid_at' => 'datetime',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(StorePaymentRefund::class, 'store_payment_id');
    }
}
