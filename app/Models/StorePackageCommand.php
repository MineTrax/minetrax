<?php

namespace App\Models;

use App\Enums\StorePackageCommandTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LogicException;

class StorePackageCommand extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'trigger' => StorePackageCommandTrigger::class,
        'is_player_online_required' => 'boolean',
        'is_repeat_per_quantity' => 'boolean',
        'is_run_on_all_servers' => 'boolean',
        'is_run_on_all_packages' => 'boolean',
        'delay_seconds' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // The invariant the schema cannot state portably: a command has exactly one owner, a
        // package or a sale. Enforced here rather than as a CHECK constraint, which would need raw
        // DB::statement — something no store migration does — and would report worse.
        static::saving(function (self $command) {
            if (($command->store_package_id === null) === ($command->store_sale_id === null)) {
                throw new LogicException('A store command must belong to exactly one of a package or a sale.');
            }
        });
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(StorePackage::class, 'store_package_id');
    }

    /**
     * The sale that owns this command, for a sale's commands. withTrashed(), so a retired sale can
     * still run the refund that takes its bonus back.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(StoreSale::class, 'store_sale_id')->withTrashed();
    }

    /**
     * The servers this command runs on. Empty means all of them, which is what
     * is_run_on_all_servers records.
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'store_package_command_server');
    }

    /**
     * The packages a sale's command applies to. Empty means every package the sale discounted,
     * which is what is_run_on_all_packages records.
     *
     * withTrashed(), so a package retired after the sale was set up still earns the bonus on an
     * order that already bought it.
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(StorePackage::class, 'store_package_command_package')->withTrashed();
    }

    /**
     * Whether this command covers a package. Only ever asked of a sale's commands.
     *
     * The flag is read first rather than inferring "all" from an empty list: a picked package that
     * has since been soft-deleted could drop out of the relation, and inferring would then silently
     * widen a command from one package to every one of them.
     */
    public function appliesToPackage(?int $packageId): bool
    {
        if ($this->is_run_on_all_packages) {
            return true;
        }

        return $packageId !== null && $this->packages->contains('id', $packageId);
    }
}
