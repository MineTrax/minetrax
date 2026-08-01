<?php

namespace Tests\Feature\Store;

use App\Enums\StorePaymentGateway;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Utils\Payments\AbstractStorePaymentGateway;
use App\Utils\Payments\Data\StorePaymentSessionData;

/**
 * A hosted gateway with no vendor behind it, registered in place of the real Stripe driver.
 *
 * Resuming a payment is defined entirely by what the gateway says about a stored session, and the
 * real driver hides that behind HTTP. This one says it out loud: whether a session was reopened,
 * replaced, or expired is readable from the statics below.
 *
 * Not a test class — Pest only collects *Test.php, and this is autoloaded by the Tests\ PSR-4 map.
 */
class FakeStripeGateway extends AbstractStorePaymentGateway
{
    /** Whether the stored session is still usable. False models a checkout that timed out. */
    public static bool $sessionIsOpen = true;

    /** @var array<int, string> Session ids passed to abandonPaymentSession(). */
    public static array $abandoned = [];

    /** @var array<int, string> Payment uuids a brand new session was opened for. */
    public static array $created = [];

    public static function reset(): void
    {
        self::$sessionIsOpen = true;
        self::$abandoned = [];
        self::$created = [];
    }

    public function gateway(): StorePaymentGateway
    {
        return StorePaymentGateway::STRIPE;
    }

    public function label(): string
    {
        return 'Fake Card';
    }

    /**
     * Declares no required credentials, so the inherited check passes on the toggle alone. The
     * real driver needs a secret key, which a test has no way to supply.
     */
    public function settingsSchema(): array
    {
        return [];
    }

    public function createPaymentSession(StoreOrder $order, StorePayment $payment): StorePaymentSessionData
    {
        self::$created[] = $payment->uuid;

        return new StorePaymentSessionData(
            redirectUrl: 'https://fake-gateway.test/pay/'.$payment->uuid,
            sessionId: 'sess_'.$payment->uuid,
        );
    }

    public function resumePaymentSession(StorePayment $payment): ?StorePaymentSessionData
    {
        if (! self::$sessionIsOpen || ! $payment->gateway_session_id) {
            return null;
        }

        return new StorePaymentSessionData(
            redirectUrl: 'https://fake-gateway.test/resume/'.$payment->gateway_session_id,
            sessionId: $payment->gateway_session_id,
        );
    }

    public function abandonPaymentSession(StorePayment $payment): void
    {
        self::$abandoned[] = (string) $payment->gateway_session_id;
    }
}
