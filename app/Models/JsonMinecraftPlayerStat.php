<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class JsonMinecraftPlayerStat extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'killed_by' => 'json',
        'used' => 'json',
        'mined' => 'json',
        'picked_up' => 'json',
        'custom' => 'json',
        'dropped' => 'json',
        'broken' => 'json',
        'crafted' => 'json',
        'killed' => 'json',
        'essentials' => 'json'
    ];

    public function server(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function player(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function scopeMaxRowForCol($query, $column, $serverId)
    {
        $query->select(["id","uuid","username","player_id",$column])
            ->where($column, function($q) use ($column, $serverId) {
                $q->from($this->getTable())
                    ->selectRaw("MAX({$column})")
                    ->where("server_id", $serverId);
            });
    }
}
