<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StoreGiftCardTransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreGiftCardRequest;
use App\Http\Requests\UpdateStoreGiftCardRequest;
use App\Models\StoreGiftCard;
use App\Models\User;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCurrencyService;
use App\Services\StoreGiftCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Store credit, and where it went.
 *
 * Until this screen a card could only come into existence by somebody buying one, so support had no
 * way to compensate a player for a botched delivery. Balances are only ever moved by an adjustment
 * that writes a ledger row: the transactions are the card's history, and an edit that set the total
 * directly would leave the two disagreeing.
 */
class StoreGiftCardController extends Controller
{
    public function __construct(
        private StoreCurrencyService $currencies,
        private StoreGiftCardService $giftCards,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', StoreGiftCard::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        // `created_by` sorts by user id rather than username, which is not alphabetical but does
        // group one person's cards together — and puts every purchased card, which has no creator
        // at all, at one end.
        $sorts = ['id', 'code', 'currency_code', 'original_balance', 'balance', 'is_enabled', 'expires_at', 'created_by', 'created_at'];

        $user = request()->user();
        $seesEveryCard = $user->can('viewAll', StoreGiftCard::class);

        $cards = QueryBuilder::for(StoreGiftCard::class)
            ->with(['issuedToUser:id,username', 'creator:id,username'])
            ->withCount('orders')
            // Staff holding only `read_own store_gift_cards` see the cards they issued. Purchased
            // cards have no creator, so this hides every one of them — which is the point.
            ->unless($seesEveryCard, fn ($query) => $query->where('created_by', $user->id))
            ->allowedFilters(...[
                'is_enabled',
                'currency_code',
                AllowedFilter::custom('q', new FilterMultipleFields([
                    'id', 'code', 'issuedToUser.username',
                ])),
                // What support is usually asked: is there anything left on this card?
                AllowedFilter::callback('spendable', function ($query, $value) {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                        ? $query->where('balance', '>', 0)
                        : $query->where('balance', '<=', 0);
                }),
            ])
            ->allowedSorts(...$sorts)
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        // Money is formatted server-side in each card's own currency, because a balance is minor
        // units and how many digits that is belongs to the currency, not to the template.
        $cards->getCollection()->transform(function (StoreGiftCard $card) use ($user) {
            $card->balance_formatted = $this->currencies->format((int) $card->balance, $card->currency_code);
            $card->original_balance_formatted = $this->currencies->format((int) $card->original_balance, $card->currency_code);

            // Per row, not per page: with the `_own` permissions a listing can hold cards this user
            // may edit beside cards they may not, so one flag for the whole table would be wrong.
            $card->can_update = $user->can('update', $card);
            $card->can_delete = $user->can('delete', $card);

            return $card;
        });

        return Inertia::render('Admin/StoreGiftCard/IndexStoreGiftCard', [
            'cards' => $cards,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
            'currencies' => $this->currencies->enabled()->map->code->values(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreGiftCard::class);

        return Inertia::render('Admin/StoreGiftCard/CreateStoreGiftCard', $this->formData());
    }

    public function store(CreateStoreGiftCardRequest $request): RedirectResponse
    {
        $card = $this->giftCards->issueManually(
            currency: $request->string('currency_code')->value(),
            amountMinor: $request->integer('balance'),
            issuedTo: $this->userFrom($request),
            expiresAt: $request->date('expires_at'),
            note: $request->note,
            creator: $request->user(),
        );

        // Straight to the card rather than the listing: whoever issued it needs the code to pass on,
        // and it is not shown anywhere else in full.
        return redirect()->route('admin.store.gift-card.show', $card->id)
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Gift card :code has been issued', ['code' => $card->code])]]);
    }

    /**
     * The card and its ledger — issue, every redemption, reversals, and staff adjustments.
     */
    public function show(StoreGiftCard $storeGiftCard): Response
    {
        $this->authorize('view', $storeGiftCard);

        $storeGiftCard->load([
            'issuedToUser:id,username',
            'creator:id,username',
            'transactions' => fn ($query) => $query->latest('id'),
            'transactions.order:id,uuid,player_username',
            'transactions.creator:id,username',
        ]);

        $storeGiftCard->balance_formatted = $this->currencies->format((int) $storeGiftCard->balance, $storeGiftCard->currency_code);
        $storeGiftCard->original_balance_formatted = $this->currencies->format((int) $storeGiftCard->original_balance, $storeGiftCard->currency_code);

        $storeGiftCard->transactions->transform(function ($transaction) use ($storeGiftCard) {
            // Signed, and formatted as such: a redemption reads as -€5.00 rather than €5.00 that
            // the reader has to work out the direction of from the type column.
            $transaction->amount_formatted = ($transaction->amount < 0 ? '-' : '+')
                .$this->currencies->format(abs((int) $transaction->amount), $storeGiftCard->currency_code);
            $transaction->balance_after_formatted = $this->currencies->format((int) $transaction->balance_after, $storeGiftCard->currency_code);

            return $transaction;
        });

        return Inertia::render('Admin/StoreGiftCard/ShowStoreGiftCard', [
            'card' => $storeGiftCard,
            // The adjustment box types a decimal and sends minor units, and how many digits that is
            // belongs to this card's currency: JPY has none, KWD has three.
            'exponent' => $this->currencies->exponentFor($storeGiftCard->currency_code),
            // Against this card rather than the bare permission: somebody holding only
            // `update_own store_gift_cards` may edit a card they issued but not one they did not.
            'cardPermissions' => [
                'update' => request()->user()->can('update', $storeGiftCard),
                'delete' => request()->user()->can('delete', $storeGiftCard),
            ],
        ]);
    }

    public function edit(StoreGiftCard $storeGiftCard): Response
    {
        $this->authorize('update', $storeGiftCard);

        $storeGiftCard->load('issuedToUser:id,username');

        return Inertia::render('Admin/StoreGiftCard/EditStoreGiftCard', array_merge($this->formData(), [
            'storeGiftCard' => $storeGiftCard,
            'username' => $storeGiftCard->issuedToUser?->username,
            'balanceFormatted' => $this->currencies->format((int) $storeGiftCard->balance, $storeGiftCard->currency_code),
        ]));
    }

    public function update(UpdateStoreGiftCardRequest $request, StoreGiftCard $storeGiftCard): RedirectResponse
    {
        $storeGiftCard->update([
            'issued_to_user_id' => $this->userFrom($request)?->id,
            'expires_at' => $request->expires_at,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return redirect()->route('admin.store.gift-card.show', $storeGiftCard->id)
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Gift card has been updated successfully')]]);
    }

    /**
     * Top a card up, or take credit off it.
     *
     * Entered as a signed amount in the card's own currency so one action covers both, and every
     * move leaves a ledger row naming who made it.
     */
    public function adjust(Request $request, StoreGiftCard $storeGiftCard): RedirectResponse
    {
        $this->authorize('update', $storeGiftCard);

        $validated = $request->validate([
            // Minor units of the card's currency, signed. Zero is not an adjustment.
            'amount' => ['required', 'integer', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $adjusted = $this->giftCards->adjustBalance(
            $storeGiftCard,
            (int) $validated['amount'],
            $validated['note'] ?? null,
            $request->user(),
        );

        if (! $adjusted) {
            return back()->with(['toast' => ['type' => 'error', 'title' => __('Adjustment Failed'), 'body' => __('That would take the balance below zero. The card has :balance left.', [
                'balance' => $this->currencies->format((int) $storeGiftCard->fresh()->balance, $storeGiftCard->currency_code),
            ])]]);
        }

        return back()->with(['toast' => ['type' => 'success', 'title' => __('Balance Adjusted')]]);
    }

    public function destroy(StoreGiftCard $storeGiftCard): RedirectResponse
    {
        $this->authorize('delete', $storeGiftCard);

        // A card that has paid for something is part of an order's money trail, and the ledger
        // cascades away with it. Disabling stops it being spent and keeps the history.
        if ($storeGiftCard->transactions()->where('type', StoreGiftCardTransactionType::REDEEM)->exists()) {
            return back()->with(['toast' => ['type' => 'error', 'title' => __('Cannot Delete'), 'body' => __('This card has already paid for an order. Disable it instead so the history survives.')]]);
        }

        $storeGiftCard->delete();

        return redirect()->route('admin.store.gift-card.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Gift card has been deleted permanently')]]);
    }

    /**
     * Shared props for the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            // A card is denominated in one currency and converts at the order's snapshot rate when
            // spent elsewhere, so the picker only offers currencies the store actually accepts.
            'currencies' => $this->currencies->enabled()->map(fn ($currency) => [
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'exponent' => (int) $currency->exponent,
            ])->values(),
            'baseCurrency' => [
                'code' => $this->currencies->base()->code,
                'exponent' => (int) $this->currencies->base()->exponent,
            ],
        ];
    }

    private function userFrom(Request $request): ?User
    {
        if (! $request->filled('username')) {
            return null;
        }

        return User::where('username', $request->string('username')->value())->first();
    }
}
