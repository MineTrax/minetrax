<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Download extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $casts = [
        'is_external' => 'boolean',
        'is_external_url_hidden' => 'boolean',
        'is_only_auth' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $downloadsModuleDisk = config('minetrax.downloads_module_disk');
        $this->addMediaCollection('download')
            ->useDisk($downloadsModuleDisk)
            ->singleFile();
    }

    public function getFileAttribute()
    {
        $file = null;
        if ($this->getFirstMedia('download')) {
            $file = $this->getFirstMedia('download');
        }

        return $file;
    }

    public function getDescriptionHtmlAttribute(): ?string
    {
        $converter = new GithubFlavoredMarkdownConverter();

        return $this->description ? $converter->convertToHtml($this->description) : null;
    }
}
