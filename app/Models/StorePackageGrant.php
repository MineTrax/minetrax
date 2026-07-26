<?php

namespace App\Models;

use App\Enums\StorePackageGrantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePackageGrant extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'status' => StorePackageGrantStatus::class,
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(StoreOrderItem::class, 'store_order_item_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(StorePackage::class, 'store_package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', StorePackageGrantStatus::ACTIVE);
    }
}
