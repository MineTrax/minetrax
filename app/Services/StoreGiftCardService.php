<?php

namespace App\Services;

use App\Enums\StoreGiftCardTransactionType;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
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
