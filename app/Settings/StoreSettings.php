<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class StoreSettings extends Settings
{
    public string $store_name;

    public ?string $store_description;

    /**
     * ISO-4217 code of the reporting currency. Every order stores its own currency plus a
     * base_total converted at the rate in force at purchase time, so this cannot be changed
     * freely once orders exist.
     */
    public string $base_currency;

    /** manual | api */
    public string $currency_rate_source;

    /** none | inclusive | exclusive */
    public string $tax_mode;

    /** Basis points, so 2000 = 20%. Kept as an integer to avoid float rounding. */
    public int $tax_rate_bp;

    public ?string $tax_label;

    public bool $enable_guest_checkout;

    public bool $require_email_on_guest_checkout;

    /**
     * When false, checkout accepts any username and derives an offline-mode UUID rather than
     * verifying against Mojang. Required for cracked/offline servers.
     */
    public bool $mojang_username_verification;

    public ?string $terms_text;

    /** Gateway keys the admin has switched on, e.g. ['manual', 'stripe']. */
    public array $enabled_gateways;

    /**
     * Credentials keyed by gateway, e.g. ['stripe' => ['secret_key' => '...']]. Kept as one
     * encrypted bag so adding a gateway never needs a settings migration.
     */
    public array $gateway_credentials;

    public bool $show_recent_purchases;

    public bool $hide_buyer_identity;

    public bool $notify_staff_on_purchase;

    /**
     * Raise a store ban automatically when a chargeback lands, on the buyer's account, player uuid,
     * email and IP. Off by default: a chargeback is sometimes the buyer's bank rather than the
     * buyer, and an IP is shared more often than people expect.
     */
    public bool $auto_ban_on_chargeback;

    public static function group(): string
    {
        return 'store';
    }

    public static function encrypted(): array
    {
        return [
            'gateway_credentials',
        ];
    }
}
