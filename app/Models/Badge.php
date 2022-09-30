<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Badge extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $appends = ['photo_url'];
    protected $with = ['media'];
    protected $casts = [
        'is_sticky' => 'boolean'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('badge')
            ->singleFile();
    }

    public function getPhotoUrlAttribute()
    {
        $photo_url = url('images/default_badge.svg');
        if ($this->getFirstMediaUrl('badge')) {
            $photo_url = $this->getFirstMediaUrl('badge');
        }
        return $photo_url;
    }

    public function users()
    {
        return $this->morphedByMany(User::class, 'badgeable');
    }
}
