<?php

namespace App\Models;

use App\Enums\StoreDiscountType;
use App\Enums\StoreSaleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreSale extends BaseModel
{
    use HasFactory, SoftDeletes;

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

    /**
     * The extra this sale hands out on top of its discount — "10% off, and 100 bonus coins".
     *
     * Shares a table with a package's own commands so that store_order_deliveries keeps a single
     * non-null command id to guard idempotency with. See the store_package_commands migration.
     */
    public function commands(): HasMany
    {
        return $this->hasMany(StorePackageCommand::class, 'store_sale_id');
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
