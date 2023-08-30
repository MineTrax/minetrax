<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinecraftPlayer extends BaseModel
{
    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'vault_groups' => 'array',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function minecraftPlayerSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MinecraftPlayerSession::class);
    }

    public function scopeMaxRowForCol($query, $column, $serverId)
    {
        $query->select(["id","player_uuid","player_username","player_id",$column])
            ->where($column, function($q) use ($column, $serverId) {
                $q->from($this->getTable())
                    ->selectRaw("MAX({$column})")
                    ->where("server_id", $serverId);
            });
    }

    public function getAvatarUrlAttribute(): string
    {
        return route('player.avatar.get', [$this->player_uuid, $this->player_username, 'size' => 100]);
    }
}
