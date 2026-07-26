<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePackageOptionChoice extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'price_delta' => 'integer',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(StorePackageOption::class, 'store_package_option_id');
    }
}
