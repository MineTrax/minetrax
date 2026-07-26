<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StoreCategory extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $appends = ['photo_url'];

    protected $with = ['media'];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $storeModuleDisk = config('store.module_disk');
        $this->addMediaCollection('store-category')
            ->useDisk($storeModuleDisk)
            ->singleFile();
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $photo_url = null;
        if ($this->getFirstMediaUrl('store-category')) {
            $photo_url = $this->getFirstMediaUrl('store-category');
        }

        return $photo_url;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StoreCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(StoreCategory::class, 'parent_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(StorePackage::class, 'store_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
