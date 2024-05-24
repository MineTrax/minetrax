<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommandQueue extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'execute_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
