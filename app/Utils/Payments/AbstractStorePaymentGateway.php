<?php

namespace App\Utils\Payments;

use App\Contracts\StorePaymentGatewayContract;
use App\Models\StorePayment;
use App\Settings\StoreSettings;
use App\Utils\Payments\Data\StoreGatewayEventData;
use App\Utils\Payments\Data\StorePaymentSessionData;
use Illuminate\Http\Request;

/**
 * Shared plumbing so a concrete driver only has to implement what is actually vendor-specific.
 */
abstract class AbstractStorePaymentGateway implements StorePaymentGatewayContract
{
    public function __construct(protected StoreSettings $settings) {}

    public function description(): ?string
    {
        return null;
    }

    public function settingsSchema(): array
    {
        return [];
    }

    public function supportedCurrencies(): ?array
    {
        return null;
    }

    /**
     * Enabled means: the admin switched it on, and every credential the driver declares as
     * required actually has a value. A half-configured gateway is never offered at checkout.
     */
    public function isEnabled(): bool
    {
        if (! in_array($this->gateway()->value, $this->settings->enabled_gateways ?? [], true)) {
            return false;
        }

        foreach ($this->settingsSchema() as $field) {
            if (($field['required'] ?? false) && blank($this->credential($field['key']))) {
                return false;
            }
        }

        return true;
    }

    public function verifyWebhook(Request $request): bool
    {
        return false;
    }

    public function parseWebhook(Request $request): StoreGatewayEventData
    {
        return new StoreGatewayEventData(eventId: '', kind: StoreGatewayEventData::KIND_IGNORED);
    }

    public function confirmOnReturn(StorePayment $payment): ?StoreGatewayEventData
    {
        return null;
    }

    /**
     * Nothing to reopen by default: a gateway with no hosted checkout has no session to return to.
     */
    public function resumePaymentSession(StorePayment $payment): ?StorePaymentSessionData
    {
        return null;
    }

    /**
     * Nothing to close by default.
     */
    public function abandonPaymentSession(StorePayment $payment): void
    {
        //
    }

    public function refund(StorePayment $payment, int $amountMinor, ?string $reason = null): string
    {
        throw new \RuntimeException(__(':gateway does not support automated refunds.', [
            'gateway' => $this->label(),
        ]));
    }

    /**
     * Read one of this gateway's credentials out of the shared encrypted bag.
     */
    protected function credential(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings->gateway_credentials, $this->gateway()->value.'.'.$key, $default);
    }

    public function supportsCurrency(string $code): bool
    {
        $supported = $this->supportedCurrencies();

        return $supported === null || in_array(strtoupper($code), array_map('strtoupper', $supported), true);
    }
}
