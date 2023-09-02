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
        'is_banned' => 'boolean',
        'is_kicked' => 'boolean',
        'is_op' => 'boolean',
    ];

    protected $hidden = [
        'player_ip_address',
        'join_address',
        'minecraft_version',
        'player_ping',
        'is_banned',
        'is_kicked',
        'is_op',
        'vault_groups',
        'vault_balance',
        'player_ping',
    ];

    protected $appends = ['avatar_url'];

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

    public function getAvatarUrlAttribute(): string
    {
        return route('player.avatar.get', [$this->player_uuid, $this->player_username, 'size' => 100]);
    }
}
