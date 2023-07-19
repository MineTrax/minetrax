<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MinecraftPlayerSession extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'session_started_at' => 'datetime',
        'session_ended_at' => 'datetime',
    ];
}
