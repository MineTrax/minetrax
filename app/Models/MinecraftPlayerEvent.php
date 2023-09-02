<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinecraftPlayerEvent extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'vault_groups' => 'array',
        'inventory_items' => 'array',
        'enderchest_items' => 'array',
        'world_location' => 'json'
    ];

    public function minecraftPlayerSession(): BelongsTo
    {
        return $this->belongsTo(MinecraftPlayerSession::class, 'session_id');
    }

    public function minecraftServerWorld(): BelongsTo
    {
        return $this->belongsTo(MinecraftServerWorld::class, 'minecraft_server_world_id');
    }
}