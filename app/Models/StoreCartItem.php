<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCartItem extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'quantity' => 'integer',
        'selected_options' => 'array',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(StoreCart::class, 'store_cart_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(StorePackage::class, 'store_package_id');
    }
}
