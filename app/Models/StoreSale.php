<?php

namespace App\Models;

use App\Enums\StoreDiscountType;
use App\Enums\StoreSaleScope;
use App\Traits\HasStoreCommandsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreSale extends BaseModel
{
    use HasFactory, HasStoreCommandsTrait, SoftDeletes;

    protected $casts = [
        'discount_type' => StoreDiscountType::class,
        'discount_value' => 'integer',
        'scope_type' => StoreSaleScope::class,
        'min_basket_amount' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_enabled' => 'boolean',
    ];

    public function saleables(): HasMany
    {
        return $this->hasMany(StoreSaleable::class, 'store_sale_id');
    }

    // The extra this sale hands out on top of its discount — "10% off, and 100 bonus coins" — comes
    // from HasStoreCommandsTrait::commands(), shared with every other kind of command owner.

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
