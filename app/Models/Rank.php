<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Rank extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $appends = ['photo_url'];
    protected $with = ['media'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('rank')
        ->singleFile();
    }

    public function getPhotoUrlAttribute()
    {
        $photo_url = url('images/default-ranks/'. $this->shortname. '.png');
        if ($this->getFirstMediaUrl('rank')) {
            $photo_url = $this->getFirstMediaUrl('rank');
        }
        return $photo_url;
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}
