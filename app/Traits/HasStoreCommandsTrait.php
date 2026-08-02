<?php

namespace App\Traits;

use App\Models\StoreCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Makes a model an owner of store commands.
 *
 * Everything an owner needs is here: the relation, and the cleanup a foreign key used to do. To add
 * a new kind of owner — a rank, a milestone, whatever comes next — put this trait on the model and
 * register the class in `config('store.command_owners')`. There is no migration and no edit to
 * StoreCommandDispatchService, because the owner is a morph rather than a column of its own.
 *
 * @see StoreCommand
 */
trait HasStoreCommandsTrait
{
    /**
     * Replaces the cascade a foreign key gave us before the owner became polymorphic. Without this
     * a force-deleted owner leaves its commands behind, owned by an id that no longer resolves —
     * rows no admin form can reach and no dispatch will ever find.
     *
     * Soft deletes are deliberately left alone. A retired sale still has to run the refund that
     * takes its bonus back, which is the whole reason store_sales soft-deletes in the first place.
     */
    public static function bootHasStoreCommandsTrait(): void
    {
        // `deleted` alone covers both cases: SoftDeletes::forceDelete() sets the flag *before*
        // calling delete(), so this fires with isForceDeleting() already true. Hooking forceDeleted
        // as well would only repeat the work — and would fatal on an owner that does not soft-delete
        // at all, since that event does not exist there.
        static::deleted(function (Model $owner) {
            if (method_exists($owner, 'isForceDeleting') && ! $owner->isForceDeleting()) {
                return;
            }

            $owner->commands()->delete();
        });
    }

    public function commands(): MorphMany
    {
        return $this->morphMany(StoreCommand::class, 'commandable');
    }
}
