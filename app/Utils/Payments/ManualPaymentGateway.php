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

    /**
     * No credentials, so isEnabled() reduces to "the admin switched it on".
     */
    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'instructions',
                'label' => __('Payment Instructions'),
                'type' => 'textarea',
                'required' => false,
                'help' => __('Shown to the buyer after they place an order.'),
            ],
        ];
    }

    public function createPaymentSession(StoreOrder $order, StorePayment $payment): StorePaymentSessionData
    {
        // Nowhere to send the buyer; they go straight to the order result page.
        return new StorePaymentSessionData(
            redirectUrl: null,
            sessionId: null,
            raw: ['instructions' => $this->credential('instructions')],
        );
    }
}
