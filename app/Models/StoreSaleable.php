<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StoreSaleable extends BaseModel
{
    use HasFactory;

    public function sale(): BelongsTo
    {
        return $this->belongsTo(StoreSale::class, 'store_sale_id');
    }

    public function saleable(): MorphTo
    {
        return $this->morphTo();
    }
}
