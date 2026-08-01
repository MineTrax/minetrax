<?php

namespace App\Utils\Payments;

use App\Contracts\StorePaymentGatewayContract;
use App\Models\StorePayment;
use App\Models\StorePaymentGateway as GatewayRecord;
use App\Utils\Payments\Data\StoreGatewayEventData;
use App\Utils\Payments\Data\StorePaymentSessionData;
use Illuminate\Http\Request;

/**
 * Shared plumbing so a concrete driver only has to implement what is actually vendor-specific.
 */
abstract class AbstractStorePaymentGateway implements StorePaymentGatewayContract
{
    /**
     * This gateway's row, loaded once per driver instance.
     *
     * The manager caches drivers for the request, so isEnabled() plus a handful of credential()
     * calls cost one query rather than one each.
     */
    private ?GatewayRecord $record = null;

    private bool $recordLoaded = false;

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
        // No row means the seeder has not run for this driver yet, which is not the same as being
        // switched off — but it cannot be charged against either, so it is not offered.
        if (! $this->record()?->is_enabled) {
            return false;
        }

        foreach ($this->settingsSchema() as $field) {
            if (($field['required'] ?? false) && blank($this->credential($field['key']))) {
                return false;
            }
        }

        return true;
    }

    /**
     * This gateway's stored configuration, or null if it has no row.
     */
    protected function record(): ?GatewayRecord
    {
        if (! $this->recordLoaded) {
            $this->record = GatewayRecord::firstWhere('key', $this->gateway()->value);
            $this->recordLoaded = true;
        }

        return $this->record;
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
     * Read one of this gateway's credentials off its row.
     */
    protected function credential(string $key, mixed $default = null): mixed
    {
        return $this->record()?->credential($key, $default) ?? $default;
    }

    public function supportsCurrency(string $code): bool
    {
        $supported = $this->supportedCurrencies();

        return $supported === null || in_array(strtoupper($code), array_map('strtoupper', $supported), true);
    }
}
