<?php

namespace App\Models;

use App\Enums\StorePriceRounding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreCurrency extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'exponent' => 'integer',
        'rate_to_base' => 'decimal:10',
        'rate_updated_at' => 'datetime',
        'is_base' => 'boolean',
        'is_enabled' => 'boolean',
        'price_rounding' => StorePriceRounding::class,
        'sort_order' => 'integer',
    ];

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public static function base(): ?self
    {
        return static::where('is_base', true)->first();
    }
}
