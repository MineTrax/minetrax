<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StoreReferralAttributionMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreReferralRequest;
use App\Http\Requests\RecordStoreReferralPayoutRequest;
use App\Http\Requests\UpdateStoreReferralRequest;
use App\Models\Server;
use App\Models\StoreCoupon;
use App\Models\StoreOrder;
use App\Models\StoreReferral;
use App\Models\StoreReferralPayout;
use App\Models\User;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCurrencyService;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StoreReferralController extends Controller
{
    public function __construct(private StoreCurrencyService $currencies) {}

    public function index(): Response
    {
        $this->authorize('viewAny', StoreReferral::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'code',
            'referrer_name',
            'share_bp',
            'visit_count',
            'last_visited_at',
            'is_enabled',
            'created_at',
            'updated_at',
        ];

        $user = request()->user();
        $seesEvery = $user->can('viewAll', StoreReferral::class);

        $referrals = QueryBuilder::for(StoreReferral::class)
            ->select([...$fields, 'created_by', 'user_id'])
            ->with(['creator:id,username', 'user:id,username'])
            ->withBalance()
            ->withCount('orders')
            ->unless($seesEvery, fn ($query) => $query->where('created_by', $user->id))
            ->allowedFilters(...[
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'code', 'referrer_name'])),
            ])
            // The two aggregates scopeWithBalance() adds are sortable; `owed` is not, because it is
            // their difference and exists only per row.
            ->allowedSorts(...[...$fields, 'earned_base', 'paid_out'])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        $referrals->getCollection()->transform(function (StoreReferral $referral) use ($user) {
            return $this->withMoney($referral)->setAttribute('can_update', $user->can('update', $referral))
                ->setAttribute('can_delete', $user->can('delete', $referral));
        });

        return Inertia::render('Admin/StoreReferral/IndexStoreReferral', [
            'referrals' => $referrals,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreReferral::class);

        return Inertia::render('Admin/StoreReferral/CreateStoreReferral', $this->formData());
    }

    public function store(CreateStoreReferralRequest $request)
    {
        $referral = DB::transaction(function () use ($request) {
            $referral = StoreReferral::create($this->attributesFrom($request) + [
                'created_by' => $request->user()->id,
            ]);

            $this->syncCommands($referral, $request->input('commands', []));

            return $referral;
        });

        return redirect()->route('admin.store.referral.show', $referral->id)
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Referral code has been created successfully')]]);
    }

    /**
     * The code's own page: what it has brought in, what has been paid for it, and what is left.
     */
    public function show(StoreReferral $storeReferral): Response
    {
        $this->authorize('view', $storeReferral);

        $storeReferral->loadCount('orders');
        $storeReferral->load('user:id,username', 'creator:id,username');

        // No withBalance() here: the two accessors fall back to their own queries, which is two
        // reads on a page that is already doing several rather than an aggregate worth optimising.
        $this->withMoney($storeReferral);

        return Inertia::render('Admin/StoreReferral/ShowStoreReferral', [
            'referral' => $storeReferral,
            'trackingBaseUrl' => $this->trackingBaseUrl(),
            'orders' => $this->earningsFor($storeReferral),
            'payouts' => $this->payoutsFor($storeReferral),
            'canPayout' => request()->user()->can('payout', StoreReferral::class),
        ]);
    }

    public function edit(StoreReferral $storeReferral): Response
    {
        $this->authorize('update', $storeReferral);

        $storeReferral->load('commands.servers:id,name,hostname');

        return Inertia::render('Admin/StoreReferral/EditStoreReferral', array_merge($this->formData(), [
            'storeReferral' => $storeReferral,
        ]));
    }

    public function update(UpdateStoreReferralRequest $request, StoreReferral $storeReferral)
    {
        DB::transaction(function () use ($request, $storeReferral) {
            $storeReferral->update($this->attributesFrom($request) + [
                'updated_by' => $request->user()->id,
            ]);

            $this->syncCommands($storeReferral, $request->input('commands', []));
        });

        return redirect()->route('admin.store.referral.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Referral code has been updated successfully')]]);
    }

    public function destroy(StoreReferral $storeReferral)
    {
        $this->authorize('delete', $storeReferral);

        // Soft: the orders it earned on and the payouts made against it are a money trail, and both
        // point here. It stops crediting anyone the moment it goes, which is the part that matters.
        $storeReferral->delete();

        return redirect()->route('admin.store.referral.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Referral code has been deleted')]]);
    }

    /**
     * Record money actually handed over.
     */
    public function payout(RecordStoreReferralPayoutRequest $request, StoreReferral $storeReferral)
    {
        $storeReferral->payouts()->create([
            'amount' => $request->integer('amount'),
            // The base currency as it stands now, so the history reads correctly even if it changes.
            'currency' => $this->currencies->base()->code,
            'reference' => $request->input('reference'),
            'note' => $request->input('note'),
            'paid_at' => $request->input('paid_at') ?: now(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.store.referral.show', $storeReferral->id)
            ->with(['toast' => ['type' => 'success', 'title' => __('Payout Recorded'), 'body' => __('The balance has been updated')]]);
    }

    /**
     * Undo a mis-entered payout, which puts the amount straight back into what is owed.
     */
    public function payoutDestroy(Request $request, StoreReferral $storeReferral, StoreReferralPayout $payout)
    {
        $this->authorize('payout', StoreReferral::class);

        abort_unless($payout->store_referral_id === $storeReferral->id, 404);

        $payout->delete();

        return redirect()->route('admin.store.referral.show', $storeReferral->id)
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('The payout has been removed')]]);
    }

    /**
     * Format the three figures every surface shows, so none of them can render raw minor units.
     */
    private function withMoney(StoreReferral $referral): StoreReferral
    {
        $base = $this->currencies->base();

        return $referral
            // withSum() hands back a string, and the two aggregates go to the frontend as-is. Cast
            // here so every money figure in the payload is an integer of minor units, whether it
            // came from an aggregate or an accessor.
            ->setAttribute('earned_base', $referral->earnedBase())
            ->setAttribute('paid_out', $referral->paidOut())
            ->setAttribute('earned_formatted', $this->currencies->format($referral->earnedBase(), $base))
            ->setAttribute('paid_out_formatted', $this->currencies->format($referral->paidOut(), $base))
            ->setAttribute('owed', $referral->owed())
            ->setAttribute('owed_formatted', $this->currencies->format($referral->owed(), $base));
    }

    /**
     * One row per referred order, newest first.
     *
     * A refunded or charged-back order is kept in the list with a zero contribution rather than
     * dropped, so it is visible *why* the earned total moved instead of it silently shrinking.
     */
    private function earningsFor(StoreReferral $referral): LengthAwarePaginator
    {
        $base = $this->currencies->base();

        $orders = $referral->orders()
            ->select(['id', 'uuid', 'status', 'currency', 'total', 'referral_earning', 'referral_earning_base', 'referral_source', 'created_at'])
            ->latest('id')
            ->paginate(15, pageName: 'orders')
            ->withQueryString();

        $orders->getCollection()->transform(function (StoreOrder $order) use ($base) {
            $counts = $order->status->isPaidState();

            $order->total_formatted = $this->currencies->format((int) $order->total, $order->currency);
            $order->earning_formatted = $this->currencies->format(
                $counts ? (int) $order->referral_earning_base : 0,
                $base
            );
            $order->counts_towards_balance = $counts;

            return $order;
        });

        return $orders;
    }

    private function payoutsFor(StoreReferral $referral): LengthAwarePaginator
    {
        $payouts = $referral->payouts()
            ->with('creator:id,username')
            ->latest('paid_at')
            ->paginate(15, pageName: 'payouts')
            ->withQueryString();

        $payouts->getCollection()->transform(function (StoreReferralPayout $payout) {
            $payout->amount_formatted = $this->currencies->format(
                (int) $payout->amount,
                // Formatted in the currency it was recorded in, not today's base.
                $this->currencies->find($payout->currency) ?? $this->currencies->base()
            );

            return $payout;
        });

        return $payouts;
    }

    /**
     * What a tracking link is prefixed with.
     *
     * The storefront can be the site root, in which case /store only redirects there — so the link
     * handed to a creator should be the canonical one rather than a hop.
     */
    private function trackingBaseUrl(): string
    {
        return app(GeneralSettings::class)->homepage_route === 'store'
            ? url('/')
            : route('store.index');
    }

    /**
     * Shared props for the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'coupons' => StoreCoupon::select(['id', 'code', 'discount_type', 'discount_value'])->orderBy('code')->get(),
            'servers' => Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->orderBy('name')->get(),
            'attributionModes' => collect(StoreReferralAttributionMode::cases())
                ->mapWithKeys(fn (StoreReferralAttributionMode $mode) => [$mode->value => $mode->name])
                ->all(),
            'trackingBaseUrl' => $this->trackingBaseUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFrom(CreateStoreReferralRequest $request): array
    {
        return [
            'code' => $request->string('code')->value(),
            'referrer_name' => $request->string('referrer_name')->value(),
            // Named by username rather than picked from a list of every account, the same choice
            // store bans made. Null unlinks: the code keeps working, its holder just loses the
            // self-serve page and the guard against earning on their own orders.
            'user_id' => $request->filled('username')
                ? User::where('username', $request->string('username')->value())->value('id')
                : null,
            'share_bp' => $request->integer('share_bp'),
            'store_coupon_id' => $request->input('store_coupon_id'),
            'is_url_tracking_enabled' => $request->boolean('is_url_tracking_enabled'),
            // Blank means lifetime, which is a real choice rather than a missing value.
            'attribution_window_days' => $request->input('attribution_window_days') ?: null,
            'attribution_mode' => StoreReferralAttributionMode::from($request->string('attribution_mode')->value()),
            'is_command_execution_enabled' => $request->boolean('is_command_execution_enabled'),
            'is_enabled' => $request->boolean('is_enabled'),
            'notes' => $request->input('notes'),
        ];
    }

    /**
     * Reconcile the referral's command set: update rows that carry an id, create those that do not,
     * then delete whatever the form no longer references.
     *
     * Scoped through $referral->commands() throughout, so this can neither read, steal nor delete
     * a package's or a sale's commands — all three share store_commands.
     *
     * @param  array<int, array<string, mixed>>  $commands
     */
    private function syncCommands(StoreReferral $referral, array $commands): void
    {
        $keptIds = [];

        foreach ($commands as $index => $command) {
            $attributes = [
                // Fixed rather than taken from the form. A referral's commands are a thank-you for a
                // sale that landed; the registry says purchase is the only trigger it may use.
                'trigger' => 'purchase',
                'command' => $command['command'],
                'is_player_online_required' => (bool) ($command['is_player_online_required'] ?? false),
                'delay_seconds' => $command['delay_seconds'] ?? 0,
                // An order-level command runs once, so repeating it per unit of a line it is not
                // attached to would be meaningless.
                'is_repeat_per_quantity' => false,
                'is_run_on_all_packages' => true,
                'sort_order' => $command['sort_order'] ?? $index,
                'is_run_on_all_servers' => count($command['servers'] ?? []) === 0,
            ];

            $serverIds = Arr::pluck($command['servers'] ?? [], 'id');

            $existing = ! empty($command['id'])
                ? $referral->commands()->whereKey($command['id'])->first()
                : null;

            if ($existing) {
                $existing->update($attributes);
                $row = $existing;
            } else {
                $row = $referral->commands()->create($attributes);
            }

            $row->servers()->sync($serverIds);
            $keptIds[] = $row->id;
        }

        $referral->commands()->whereNotIn('id', $keptIds ?: [0])->delete();
    }
}
