<?php

namespace App\Models;

use App\Traits\HasCommentsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableInterface;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends BaseModel implements ReactableInterface, HasMedia
{
    use HasFactory;
    use Reactable;
    use HasCommentsTrait;
    use InteractsWithMedia;

    protected $with = ['media'];
    protected $appends = ['media_url_array'];

    public function toArray(): array
    {
        return parent::toArray() + [
            'permissions' => $this->permissions(['delete'])
            ];
    }

    public function getMediaUrlArrayAttribute(): \Illuminate\Support\Collection
    {
        return $this->getMedia('posts')->map(fn($media) => $media->getUrl());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('posts');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Delete the reactions when the post is deleted.
    protected static function booted()
    {
        static::deleted(function ($post) {
            $post->loveReactant()->delete();
            $post->comments()->delete();
        });
    }
}
