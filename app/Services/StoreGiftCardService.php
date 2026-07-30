<?php

namespace App\Services;

use App\Enums\StoreGiftCardTransactionType;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Issues store credit for a package that sells a gift card.
 *
 * The card is always denominated in the currency the order was paid in, so a buyer who paid €20
 * gets €20 of credit back rather than a figure converted twice at two different moments.
 */
class StoreGiftCardService
{
    /**
     * Characters a human can read off a screen and type back without ambiguity: no O/0, no I/1.
     */
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(private StoreCurrencyService $currencies) {}

    /**
     * Mint the card an order item is owed, or return the one it already has.
     *
     * Idempotent through store_order_items.store_gift_card_id, which is what lets the fulfilment
     * job be retried without minting a second code.
     */
    public function issueForOrderItem(StoreOrder $order, StoreOrderItem $item): ?StoreGiftCard
    {
        if ($item->store_gift_card_id) {
            return $item->giftCard;
        }

        $package = $item->package;

        if (! $package || ! $package->type->issuesGiftCard()) {
            return null;
        }

        $amount = $this->amountFor($order, $item);

        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order, $item, $amount) {
            $card = StoreGiftCard::create([
                'code' => $this->generateCode(),
                'currency_code' => $order->currency,
                'original_balance' => $amount,
                'balance' => $amount,
                'is_enabled' => true,
                'issued_to_user_id' => $order->user_id,
            ]);

            $card->transactions()->create([
                'store_order_id' => $order->id,
                'type' => StoreGiftCardTransactionType::ISSUE,
                'amount' => $amount,
                'balance_after' => $amount,
                'note' => __('Issued by order :uuid', ['uuid' => $order->uuid]),
            ]);

            $item->update(['store_gift_card_id' => $card->id]);

            return $card;
        });
    }

    /**
     * Issue a card nobody bought — compensation, a giveaway prize, a goodwill gesture.
     *
     * The ledger row carries the causer, which is the whole reason this does not simply insert a
     * row: a card that appeared with no buyer behind it needs to say who conjured it.
     */
    public function issueManually(
        StoreCurrency|string $currency,
        int $amountMinor,
        ?User $issuedTo = null,
        ?CarbonInterface $expiresAt = null,
        ?string $note = null,
        ?User $creator = null,
    ): StoreGiftCard {
        $code = $currency instanceof StoreCurrency ? $currency->code : $currency;

        return DB::transaction(function () use ($code, $amountMinor, $issuedTo, $expiresAt, $note, $creator) {
            $card = StoreGiftCard::create([
                'code' => $this->generateCode(),
                'currency_code' => $code,
                'original_balance' => $amountMinor,
                'balance' => $amountMinor,
                'expires_at' => $expiresAt,
                'is_enabled' => true,
                'issued_to_user_id' => $issuedTo?->id,
                'created_by' => $creator?->id,
            ]);

            $card->transactions()->create([
                'type' => StoreGiftCardTransactionType::ISSUE,
                'amount' => $amountMinor,
                'balance_after' => $amountMinor,
                'note' => $note ?: __('Issued by staff'),
                'created_by' => $creator?->id,
            ]);

            return $card;
        });
    }

    /**
     * Move a card's balance by hand, up or down.
     *
     * The only sanctioned way to change a balance: the ledger is what the card's history is read
     * from, so an edit that skipped it would leave the rows disagreeing with the total. Locked
     * because a redemption can be settling against the same card at the same moment.
     *
     * @return bool false when the delta would take the balance below zero
     */
    public function adjustBalance(StoreGiftCard $card, int $deltaMinor, ?string $note = null, ?User $creator = null): bool
    {
        if ($deltaMinor === 0) {
            return false;
        }

        return (bool) DB::transaction(function () use ($card, $deltaMinor, $note, $creator) {
            /** @var StoreGiftCard|null $locked */
            $locked = StoreGiftCard::lockForUpdate()->find($card->id);

            if (! $locked) {
                return false;
            }

            $balanceAfter = (int) $locked->balance + $deltaMinor;

            if ($balanceAfter < 0) {
                // Refused rather than clamped: an admin taking off more than is left has the wrong
                // figure in mind, and silently zeroing it hides that.
                return false;
            }

            $locked->update([
                'balance' => $balanceAfter,
                // A top-up raises what the card was ever worth; taking credit off does not lower it,
                // because original_balance is what was issued, not what remains.
                'original_balance' => max((int) $locked->original_balance, $balanceAfter),
            ]);

            $locked->transactions()->create([
                'type' => StoreGiftCardTransactionType::ADJUSTMENT,
                'amount' => $deltaMinor,
                'balance_after' => $balanceAfter,
                'note' => $note,
                'created_by' => $creator?->id,
            ]);

            return true;
        });
    }

    /**
     * What the card is worth, in the order's currency.
     *
     * "Same as package price" means what the buyer actually paid for the line, sale and package
     * discount included. A fixed amount is configured in the base currency and converted, then
     * multiplied by the quantity: two gift-card packages are worth twice as much.
     */
    private function amountFor(StoreOrder $order, StoreOrderItem $item): int
    {
        $package = $item->package;

        if ($package->is_gift_card_amount_same_as_price) {
            return (int) $item->total;
        }

        $configured = (int) ($package->gift_card_amount ?? 0);

        if ($configured <= 0) {
            return 0;
        }

        $currency = $this->currencies->find($order->currency) ?? $this->currencies->base();

        return $this->currencies->fromBase($configured, $currency) * max(1, (int) $item->quantity);
    }

    /**
     * A code no one else holds. The loop exists because uniqueness is enforced by the column, not
     * by hoping 32^12 never collides.
     */
    private function generateCode(): string
    {
        do {
            $code = collect(range(1, 3))
                ->map(fn () => $this->randomChunk())
                ->join('-');
        } while (StoreGiftCard::where('code', $code)->exists());

        return $code;
    }

    private function randomChunk(): string
    {
        $alphabet = self::CODE_ALPHABET;
        $chunk = '';

        for ($i = 0; $i < 4; $i++) {
            $chunk .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $chunk;
    }
}
