<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialAccount extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'extra' => 'json',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
        'secret',
        'refresh_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
