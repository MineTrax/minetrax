<?php

namespace App\Utils\Payments;

use App\Enums\StorePaymentGateway;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Utils\Payments\Data\StorePaymentSessionData;

/**
 * Offline payment: bank transfer, in-person, or anything the owner settles outside the site.
 *
 * The order simply stays PENDING until an admin with `update store_orders` marks it paid, which
 * runs the exact same state transition and delivery path as a real gateway webhook. That makes it
 * the driver the whole commerce and delivery path is tested against, with no third-party
 * dependency and no credentials.
 */
class ManualPaymentGateway extends AbstractStorePaymentGateway
{
    public function gateway(): StorePaymentGateway
    {
        return StorePaymentGateway::MANUAL;
    }

    public function label(): string
    {
        return __('Manual Payment');
    }

    public function description(): ?string
    {
        return __('Your order will be held until a staff member confirms payment.');
    }

    public function isOffline(): bool
    {
        return true;
    }

    /**
     * No credentials, so isEnabled() reduces to "the admin switched it on".
     */
    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'instructions',
                'label' => __('Payment Instructions'),
                // Rich text, because this field carries the whole offline payment flow — bank
                // details, a reference to quote, who to contact — and a wall of unformatted text is
                // the easiest possible way for a buyer to mis-transcribe an account number.
                'type' => 'richtext',
                'required' => false,
                'help' => __('Shown to the buyer while the order is awaiting payment. Bank details, a reference to quote, who to contact.'),
            ],
        ];
    }

    /**
     * The whole point of this driver: the money moves somewhere else, so the buyer has to be told
     * where. Returned as stored and sanitised at render.
     *
     * An editor that has been typed into and then cleared leaves `<p></p>` behind rather than an
     * empty string, which is filled() as far as the storage layer is concerned. Without the check
     * below that renders as a "How to pay" heading over nothing at all.
     */
    public function paymentInstructions(): ?string
    {
        $instructions = $this->credential('instructions');

        if (blank($instructions)) {
            return null;
        }

        // <img> and <hr> are content in their own right; every other tag only counts for the text
        // it wraps.
        return filled(trim(strip_tags($instructions, '<img><hr>'))) ? $instructions : null;
    }

    public function createPaymentSession(StoreOrder $order, StorePayment $payment): StorePaymentSessionData
    {
        // Nowhere to send the buyer; they go straight to the order result page, which reads the
        // instructions from paymentInstructions() rather than from anything carried here.
        return new StorePaymentSessionData(
            redirectUrl: null,
            sessionId: null,
        );
    }
}
