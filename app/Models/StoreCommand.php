<?php

namespace App\Models;

use App\Enums\StoreCommandTrigger;
use App\Traits\HasStoreCommandsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

/**
 * One in-game command, owned by whatever the store runs it on behalf of — a package, a sale, a
 * referral. The owner is a morph, so exactly one owner holds by construction and a new kind of
 * owner needs no schema change.
 *
 * @see HasStoreCommandsTrait for what an owner has to do
 * @see config('store.command_owners') for which classes may own one
 */
class StoreCommand extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'trigger' => StoreCommandTrigger::class,
        'is_player_online_required' => 'boolean',
        'is_repeat_per_quantity' => 'boolean',
        'is_run_on_all_servers' => 'boolean',
        'is_run_on_all_packages' => 'boolean',
        'delay_seconds' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // A morph column will accept any class name at all, and a command owned by something the
        // dispatcher does not know about is a row nothing will ever run and no admin form will ever
        // show. Refusing it here turns a silent orphan into an immediate error.
        static::saving(function (self $command) {
            $owners = array_keys(config('store.command_owners', []));

            if (! in_array($command->commandable_type, $owners, true)) {
                throw new LogicException(
                    "[{$command->commandable_type}] is not a registered store command owner. ".
                    'Add it to config/store.php under command_owners.'
                );
            }
        });
    }

    /**
     * The package, sale or referral this command belongs to.
     *
     * withTrashed on the relation itself is not possible for a morph, so owners that soft-delete
     * handle it on their own side — a retired sale must still run the refund that takes its bonus
     * back.
     */
    public function commandable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The servers this command runs on. Empty means all of them, which is what
     * is_run_on_all_servers records.
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'store_command_server');
    }

    /**
     * The packages this command is scoped to, for an owner whose commands cover only part of an
     * order — a sale's, today. Empty means every package the owner covered, which is what
     * is_run_on_all_packages records.
     *
     * withTrashed(), so a package retired after the sale was set up still earns the bonus on an
     * order that already bought it.
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(StorePackage::class, 'store_command_package')->withTrashed();
    }

    /**
     * Whether this command covers a package. Only ever asked of a scoped owner's commands.
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
