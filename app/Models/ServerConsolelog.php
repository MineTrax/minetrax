<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServerConsolelog extends BaseModel
{
    use HasFactory;

    public function server(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
