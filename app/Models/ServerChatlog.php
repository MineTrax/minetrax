<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServerChatlog extends BaseModel
{
    use HasFactory;

    public function server(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function setDataAttribute($value)
    {
        // Fix: Temporary fix for VentureChat weird §x issue https://i.imgur.com/VNVUWxD.png
        if ($value) {
            $this->attributes['data'] = str_replace('§x', '', $value);
        }
    }
}
