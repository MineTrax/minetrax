<?php

namespace App\Models;

use App\Enums\StorePackageCommandTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StorePackage extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $appends = ['photo_url'];

    protected $with = ['media'];

    protected $casts = [
        'price' => 'integer',
        'sort_order' => 'integer',
        'is_visible' => 'boolean',
        'is_enabled' => 'boolean',
        'requires_login' => 'boolean',
        'is_player_online_required' => 'boolean',
        'is_command_repeated_per_quantity' => 'boolean',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'stock_limit' => 'integer',
        'player_purchase_limit' => 'integer',
        'purchase_limit_period_days' => 'integer',
        'sold_count' => 'integer',
        'expiry_duration_days' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $storeModuleDisk = config('store.module_disk');
        $this->addMediaCollection('store-package')
            ->useDisk($storeModuleDisk)
            ->singleFile();
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $photo_url = null;
        if ($this->getFirstMediaUrl('store-package')) {
            $photo_url = $this->getFirstMediaUrl('store-package');
        }

        return $photo_url;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }

    public function commands(): HasMany
    {
        return $this->hasMany(StorePackageCommand::class, 'store_package_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(StorePackagePrice::class, 'store_package_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function commandsForTrigger(StorePackageCommandTrigger $trigger): HasMany
    {
        return $this->commands()->where('trigger', $trigger);
    }
}
