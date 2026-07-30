<?php

namespace App\Http\Controllers\Admin\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreBanRequest;
use App\Http\Requests\UpdateStoreBanRequest;
use App\Models\StoreBan;
use App\Models\User;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Who is not allowed to buy.
 *
 * A ban may target any combination of account, player UUID, IP and email, and any single match
 * blocks checkout. Chargebacks raise these automatically when the setting is on, which is why this
 * screen exists: without it a wrongly banned buyer needs a database edit to be let back in.
 */
class StoreBanController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', StoreBan::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $sorts = ['id', 'player_uuid', 'ip_address', 'email', 'is_automatic', 'expires_at', 'created_at'];

        $bans = QueryBuilder::for(StoreBan::class)
            ->with(['user:id,username', 'creator:id,username'])
            ->allowedFilters(...[
                'is_automatic',
                AllowedFilter::custom('q', new FilterMultipleFields([
                    'id', 'player_uuid', 'ip_address', 'email', 'reason', 'user.username',
                ])),
                // Expiry is the difference between a ban that still blocks and one kept as a
                // record, and it is not a column anyone can filter on directly.
                AllowedFilter::callback('active', function ($query, $value) {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                        ? $query->active()
                        : $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
                }),
            ])
            ->allowedSorts(...$sorts)
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/StoreBan/IndexStoreBan', [
            'bans' => $bans,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreBan::class);

        return Inertia::render('Admin/StoreBan/CreateStoreBan');
    }

    public function store(CreateStoreBanRequest $request): RedirectResponse
    {
        StoreBan::create($this->attributesFrom($request) + [
            // Only the chargeback listener raises an automatic ban; anything from this screen was
            // a decision somebody made, and the listing separates the two.
            'is_automatic' => false,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.store.ban.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Store ban has been created successfully')]]);
    }

    public function edit(StoreBan $storeBan): Response
    {
        $this->authorize('update', $storeBan);

        $storeBan->load(['user:id,username', 'creator:id,username']);

        return Inertia::render('Admin/StoreBan/EditStoreBan', [
            'storeBan' => $storeBan,
            // The form edits an account by name, so the current one has to arrive as a name too.
            'username' => $storeBan->user?->username,
        ]);
    }

    public function update(UpdateStoreBanRequest $request, StoreBan $storeBan): RedirectResponse
    {
        // `is_automatic` is not in the payload: how a ban came about is history, and editing the
        // chargeback ban it raised does not make it a manual one. Nor is `created_by` — the table
        // records who raised the ban, not who last touched it.
        $storeBan->update($this->attributesFrom($request));

        return redirect()->route('admin.store.ban.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Store ban has been updated successfully')]]);
    }

    public function destroy(StoreBan $storeBan): RedirectResponse
    {
        $this->authorize('delete', $storeBan);

        // Deleting lifts the ban and loses the record with it. An admin who wants to keep the
        // record can instead edit the expiry to a past moment, which stops it blocking.
        $storeBan->delete();

        return redirect()->route('admin.store.ban.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Store ban has been lifted')]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFrom(CreateStoreBanRequest $request): array
    {
        return [
            'user_id' => $request->filled('username')
                ? User::where('username', $request->string('username')->value())->value('id')
                : null,
            'player_uuid' => $request->player_uuid,
            'ip_address' => $request->ip_address,
            'email' => $request->email,
            'reason' => $request->reason,
            'expires_at' => $request->expires_at,
        ];
    }
}
