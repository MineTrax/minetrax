<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Http\Controllers\Controller;
use App\Models\StorePackageGrant;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCommandDispatchService;
use App\Utils\Helpers\Helper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * What players currently hold.
 *
 * The order pages answer "what was bought"; this answers "who has what right now", which is the
 * question support actually gets asked — a player claiming their rank vanished, or a refund that
 * needs its perk taken back by hand.
 */
class StoreGrantController extends Controller
{
    public function __construct(private StoreCommandDispatchService $dispatcher) {}

    public function index(): Response
    {
        $this->authorize('viewAny', StorePackageGrant::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $sorts = ['id', 'status', 'player_uuid', 'granted_at', 'expires_at', 'revoked_at'];

        $grants = QueryBuilder::for(StorePackageGrant::class)
            ->with([
                'package:id,name,slug',
                'orderItem:id,store_order_id,package_name',
                'orderItem.order:id,uuid,player_username,user_id,status',
                'orderItem.order.user:id,username',
            ])
            ->allowedFilters(...[
                'status',
                'player_uuid',
                AllowedFilter::custom('q', new FilterMultipleFields(['player_uuid', 'package.name'])),
                // The username lives on the order, two relations away, which is past what
                // FilterMultipleFields walks.
                AllowedFilter::callback('player_username', function ($query, $value) {
                    $query->whereHas('orderItem.order', function ($query) use ($value) {
                        $query->where('player_username', 'LIKE', '%'.Helper::escapeLike($value).'%');
                    });
                }),
            ])
            ->allowedSorts(...$sorts)
            ->defaultSort('-granted_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/StoreGrant/IndexStoreGrant', [
            'grants' => $grants,
            'statuses' => collect(StorePackageGrantStatus::cases())->map->value,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
            // Not `permissions`: that name is a globally shared array of the user's permission
            // names, and a page prop of the same name replaces it, breaking useAuthorizable.
            'grantPermissions' => [
                // The permission the grant policy delegates to. Checked directly rather than
                // against a row, because whether staff may revoke does not vary per grant.
                'update' => request()->user()->can('update store_orders'),
            ],
        ]);
    }

    /**
     * Take a perk back by hand.
     *
     * Runs the package's EXPIRY set, because that is the command set written to remove the thing —
     * the REFUND set belongs to the refund path, which has its own money side. `sold_count` is
     * deliberately left alone: the sale still happened, so revoking a perk is not un-selling it.
     */
    public function revoke(StorePackageGrant $grant): RedirectResponse
    {
        $this->authorize('update', $grant);

        if ($grant->status !== StorePackageGrantStatus::ACTIVE) {
            return back()->with(['toast' => ['type' => 'error', 'title' => __('Only an active grant can be revoked.')]]);
        }

        $item = $grant->orderItem;
        $order = $item?->order;

        if ($order && $item) {
            try {
                // Idempotent through the unique index on store_order_deliveries, so a grant whose
                // expiry commands already ran sends nothing a second time.
                $this->dispatcher->dispatchForItem($order, $item, StorePackageCommandTrigger::EXPIRY);
            } catch (\Throwable $exception) {
                Log::error('Store grant revocation dispatch failed.', [
                    'grant_id' => $grant->id,
                    'exception' => $exception->getMessage(),
                ]);

                return back()->with(['toast' => ['type' => 'error', 'title' => __('The removal commands could not be queued. The grant is unchanged.')]]);
            }
        }

        // Conditional on the status, so a concurrent expiry sweep cannot end up double-marking it.
        StorePackageGrant::whereKey($grant->id)
            ->where('status', StorePackageGrantStatus::ACTIVE)
            ->update([
                'status' => StorePackageGrantStatus::REVOKED,
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with(['toast' => ['type' => 'success', 'title' => __('Grant Revoked')]]);
    }

    /**
     * Push an expiry further out — the fix for downtime, or an expiry that fired in error.
     *
     * Only for a grant that is both active and timed: a permanent grant has no expiry to move, and
     * re-granting something already expired would mean re-running its purchase commands, which is
     * a resend on the order rather than an edit here.
     */
    public function extend(Request $request, StorePackageGrant $grant): RedirectResponse
    {
        $this->authorize('update', $grant);

        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        if ($grant->status !== StorePackageGrantStatus::ACTIVE || ! $grant->expires_at) {
            return back()->with(['toast' => ['type' => 'error', 'title' => __('Only an active grant with an expiry date can be extended.')]]);
        }

        $grant->update([
            'expires_at' => $grant->expires_at->copy()->addDays($validated['days']),
        ]);

        return back()->with(['toast' => ['type' => 'success', 'title' => __('Grant Extended')]]);
    }
}
