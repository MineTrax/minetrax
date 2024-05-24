<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Command extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_run_on_all_servers' => 'boolean',
        'config' => 'array',
    ];

    public function servers()
    {
        return $this->belongsToMany(Server::class);
    }
}
