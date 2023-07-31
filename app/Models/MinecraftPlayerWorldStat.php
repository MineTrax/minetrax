<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinecraftPlayerWorldStat extends BaseModel
{
    use HasFactory;

    public function minecraftPlayerSession(): BelongsTo
    {
        return $this->belongsTo(MinecraftPlayerSession::class, 'session_id');
    }

    public function minecraftServerWorld(): BelongsTo
    {
        return $this->belongsTo(MinecraftServerWorld::class, 'minecraft_server_world_id');
    }
}
