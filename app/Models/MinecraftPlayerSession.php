<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinecraftPlayerSession extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'session_started_at' => 'datetime',
        'session_ended_at' => 'datetime',
        'vault_groups' => 'array',
    ];

    public function minecraftPlayer(): BelongsTo
    {
        return $this->belongsTo(MinecraftPlayer::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function minecraftPlayerEvents(): HasMany
    {
        return $this->hasMany(MinecraftPlayerEvent::class, 'session_id');
    }
}