<?php

namespace App\Models;

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageRequirementMode;
use App\Enums\StorePackageType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StorePackage extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $appends = ['photo_url', 'is_available'];

    protected $with = ['media'];

    protected $casts = [
        'type' => StorePackageType::class,
        'price' => 'integer',
        'discount_bp' => 'integer',
        'is_pay_what_you_want' => 'boolean',
        'pay_what_you_want_max' => 'integer',
        'gift_card_amount' => 'integer',
        'is_gift_card_amount_same_as_price' => 'boolean',
        'sort_order' => 'integer',
        'is_visible' => 'boolean',
        'is_enabled' => 'boolean',
        'requires_login' => 'boolean',
        'is_featured' => 'boolean',
        'is_giftable' => 'boolean',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'player_purchase_limit' => 'integer',
        'player_purchase_limit_period_days' => 'integer',
        'global_purchase_limit' => 'integer',
        'global_purchase_limit_period_days' => 'integer',
        'sold_count' => 'integer',
        'required_packages_mode' => StorePackageRequirementMode::class,
        'comparison_values' => 'array',
        'expiry_duration_days' => 'integer',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
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

    /**
     * Every grant ever issued for this package, across all players.
     *
     * Used to ask what a given player already holds — for prerequisites, and for the upgrade credit
     * in a cumulative category.
     */
    public function grants(): HasMany
    {
        return $this->hasMany(StorePackageGrant::class, 'store_package_id');
    }

    /**
     * Inputs the buyer fills in while ordering this package.
     *
     * Ordered on the relation itself so every eager load agrees, and the pivot's sort_order is what
     * the admin arranged in the package form.
     */
    public function variables(): BelongsToMany
    {
        return $this->belongsToMany(StoreVariable::class, 'store_package_variable', 'store_package_id', 'store_variable_id')
            ->withPivot('sort_order')
            ->orderBy('store_package_variable.sort_order')
            ->orderBy('store_variables.id');
    }

    /**
     * Packages the buyer has to own before this one can be purchased.
     */
    public function requiredPackages(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'store_package_requirement',
            'store_package_id',
            'required_store_package_id'
        );
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

    /**
     * Enabled and inside its publish window.
     *
     * The window is evaluated here on every read rather than by a job that flips is_enabled, so
     * there is no scheduled task to miss and no stored flag to disagree with the dates.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_enabled', true)
            ->where(fn (Builder $q) => $q->whereNull('available_from')->orWhere('available_from', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('available_until')->orWhere('available_until', '>=', now()));
    }

    public function getIsAvailableAttribute(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }

        return ! ($this->available_until && $this->available_until->isPast());
    }

    /**
     * The package's own discount, in the same minor units as the price it is given.
     *
     * Basis points, matching coupons and sales: 2000 is 20% off. Applied before any sale, so a
     * store-wide sale discounts the already-reduced price.
     */
    public function discountFor(int $amountMinor): int
    {
        $bp = min(10000, max(0, (int) $this->discount_bp));

        if ($bp === 0 || $amountMinor <= 0) {
            return 0;
        }

        return intdiv($amountMinor * $bp, 10000);
    }
}
