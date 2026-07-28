<?php

namespace App\Models;

use App\Enums\StoreCategoryDisplayType;
use App\Enums\StoreComparisonFieldType;
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
        'display_type' => StoreCategoryDisplayType::class,
        'comparison_fields' => 'array',
        'is_cumulative' => 'boolean',
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

    /**
     * The comparison table's rows, normalised and only the usable ones.
     *
     * Read through here rather than off the column so a hand-edited or half-written row cannot
     * reach the storefront as a nameless column.
     *
     * @return array<int, array{key: string, name: string, description: string|null, type: string}>
     */
    public function comparisonFields(): array
    {
        if (! $this->display_type?->usesComparisonFields()) {
            return [];
        }

        return collect($this->comparison_fields ?? [])
            ->filter(fn ($field) => is_array($field) && ! empty($field['key']) && ! empty($field['name']))
            ->map(fn (array $field) => [
                'key' => (string) $field['key'],
                'name' => (string) $field['name'],
                'description' => $field['description'] ?? null,
                'type' => StoreComparisonFieldType::tryFrom((string) ($field['type'] ?? ''))?->value
                    ?? StoreComparisonFieldType::TEXT->value,
            ])
            ->values()
            ->all();
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
