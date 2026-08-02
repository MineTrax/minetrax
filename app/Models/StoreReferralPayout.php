<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One payment actually made to a referrer.
 *
 * These are the only rows that move a balance downwards. What is owed is always
 * earnings − payouts, computed on read.
 */
class StoreReferralPayout extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function referral(): BelongsTo
    {
        return $this->belongsTo(StoreReferral::class, 'store_referral_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
