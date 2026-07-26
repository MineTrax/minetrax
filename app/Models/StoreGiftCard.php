<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreGiftCard extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'original_balance' => 'integer',
        'balance' => 'integer',
        'expires_at' => 'datetime',
        'is_enabled' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(StoreGiftCardTransaction::class, 'store_gift_card_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(StoreOrder::class, 'store_gift_card_id');
    }

    public function issuedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
