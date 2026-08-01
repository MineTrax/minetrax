<?php

namespace App\Models;

use App\Services\StoreTaxService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One tax rule: a rate, the country it applies to, and whether prices already contain it.
 *
 * A rule with no country is the fallback, applied to every buyer no rule of their own covers —
 * including buyers whose country could not be determined at all.
 *
 * @see StoreTaxService for how a rule is chosen and applied.
 */
class StoreTax extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'rate_bp',
        'is_inclusive',
        'is_enabled',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rate_bp' => 'integer',
        'is_inclusive' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * The fallback rule, which is the one with no country.
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('country_id');
    }

    public function getIsGlobalAttribute(): bool
    {
        return $this->country_id === null;
    }
}
