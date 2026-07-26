<?php

namespace App\Models;

use App\Enums\StoreCommandTarget;
use App\Enums\StorePackageCommandTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePackageCommand extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'trigger' => StorePackageCommandTrigger::class,
        'target' => StoreCommandTarget::class,
        'is_player_online_required' => 'boolean',
        'is_repeat_per_quantity' => 'boolean',
        'delay_seconds' => 'integer',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(StorePackage::class, 'store_package_id');
    }
}
