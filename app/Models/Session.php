<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Session extends BaseModel
{
    use HasFactory;

    public $incrementing = false;

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
