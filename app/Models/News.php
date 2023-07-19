<?php

namespace App\Models;

use App\Enums\NewsType;
use App\Utils\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $appends = ['photo_url'];
    protected $with = ['media'];
    protected $casts = [
        'type' => NewsType::class,
        'published_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('news')
            ->singleFile();
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $photo_url = null;
        if ($this->getFirstMediaUrl('news')) {
            $photo_url = $this->getFirstMediaUrl('news');
        }
        return $photo_url;
    }

    public function getBodyHtmlAttribute(): string
    {
        $converter = new GithubFlavoredMarkdownConverter();

        return $converter->convertToHtml($this->body);
    }

    public function getTimeToReadAttribute(): string
    {
        return Helper::getEstimateReadingTime($this->body);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
