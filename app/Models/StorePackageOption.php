<?php

namespace App\Models;

use App\Enums\StorePackageOptionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorePackageOption extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'type' => StorePackageOptionType::class,
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(StorePackage::class, 'store_package_id');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(StorePackageOptionChoice::class, 'store_package_option_id')->orderBy('sort_order');
    }
}
