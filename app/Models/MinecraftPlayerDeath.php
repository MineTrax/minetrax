<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinecraftPlayerDeath extends BaseModel
{
    use HasFactory;

    public function minecraftPlayerSession(): BelongsTo
    {
        return $this->belongsTo(MinecraftPlayerSession::class, 'session_id');
    }
}