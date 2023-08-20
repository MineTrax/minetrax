<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends BaseModel
{
    use HasFactory;

    const UNKNOWN_COUNTRY_ID = 251; // Terra Incognita ID

    protected $casts = [
        'currency' => 'json',
        'languages' => 'json',
        'latlng' => 'array'
    ];

    protected $appends = ['photo_path'];

    public function players(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function minecraftPlayers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MinecraftPlayer::class);
    }

    public function servers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function getPhotoPathAttribute()
    {
        if (!$this->iso_code) {
            return url('/images/flags/flags-iso/shiny/48/_unknown.png');
        }
        return url('/images/flags/flags-iso/shiny/48/'. $this->iso_code. '.png');
    }
}
