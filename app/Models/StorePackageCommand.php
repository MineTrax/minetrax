<?php

namespace App\Models;

use App\Enums\StorePackageCommandTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StorePackageCommand extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'trigger' => StorePackageCommandTrigger::class,
        'is_player_online_required' => 'boolean',
        'is_repeat_per_quantity' => 'boolean',
        'is_run_on_all_servers' => 'boolean',
        'delay_seconds' => 'integer',
        'sort_order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(StorePackage::class, 'store_package_id');
    }

    /**
     * The servers this command runs on. Empty means all of them, which is what
     * is_run_on_all_servers records.
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'store_package_command_server');
    }
}
