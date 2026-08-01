<?php

namespace App\Models;

use App\Enums\StorePaymentGateway as StorePaymentGatewayEnum;

/**
 * An installed payment gateway: whether it is switched on, and its credentials.
 *
 * A row per gateway rather than two keys in the settings bag. The bag made "which gateways exist"
 * a question only the config file could answer, and every read had to reach through
 * `data_get($settings->gateway_credentials, 'stripe.secret_key')`. Here a gateway is a record —
 * listable, orderable, and seedable one at a time.
 *
 * The driver class, label and field schema are deliberately NOT stored: those belong to code, are
 * translatable, and would go stale the moment a driver changed. This table holds only what an
 * administrator decides.
 *
 * Shares its short name with {@see StorePaymentGatewayEnum}, which is the key this row is
 * identified by. Files needing both alias one of them.
 */
class StorePaymentGateway extends BaseModel
{
    protected $fillable = [
        'key',
        'is_enabled',
        'credentials',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        // Encrypted at rest, as the settings bag was. Secrets must never be readable from a
        // database dump.
        'credentials' => 'encrypted:array',
        'sort_order' => 'integer',
    ];

    /**
     * Hidden everywhere by default: this model is only ever sent to the browser through the admin
     * gateway screen, which masks secrets field by field. A stray toArray() must not leak keys.
     */
    protected $hidden = ['credentials'];

    public function gatewayEnum(): ?StorePaymentGatewayEnum
    {
        return StorePaymentGatewayEnum::tryFrom($this->key);
    }

    /**
     * One credential, or the default when it was never set.
     */
    public function credential(string $field, mixed $default = null): mixed
    {
        return data_get($this->credentials, $field, $default);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
