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

    public bool $enable_guest_checkout;

    public bool $require_email_on_guest_checkout;

    /**
     * When false, checkout accepts any username and derives an offline-mode UUID rather than
     * verifying against Mojang. Required for cracked/offline servers.
     */
    public bool $mojang_username_verification;

    /**
     * Ask every buyer for a billing address at checkout, guests included.
     *
     * Off by default: a Minecraft store ships nothing, so most owners have no use for one. When it
     * is on the country the buyer names also decides their tax rule, in place of the guess made
     * from their IP — a declared address is better evidence than a geolocation lookup, and holding
     * both while charging against the weaker one is indefensible on an invoice.
     */
    public bool $collect_billing_address;

    public ?string $terms_text;

    public bool $show_recent_purchases;

    /**
     * Show the progress bar towards this month's target on the homepage and the storefront.
     */
    public bool $show_purchase_goal;

    /**
     * The monthly target, in minor units of the base currency. Zero means there is no goal to show,
     * whatever the toggle says — a bar against nothing would sit permanently at 100%.
     */
    public int $purchase_goal_amount;

    /**
     * Show whoever has spent the most this month. Off by default: some communities would rather not
     * put a leaderboard on money.
     */
    public bool $show_top_donor;

    /**
     * Replaces buyer names with "Anonymous" everywhere the public can see them — the recent
     * purchases list and the top spender. Applies to guests' Minecraft usernames too, since that is
     * an identity as much as an account name is.
     */
    public bool $hide_buyer_identity;

    public bool $notify_staff_on_purchase;

    /**
     * A Discord incoming-webhook URL to announce each sale in.
     *
     * Empty is the off switch — there is no separate toggle, because a webhook with nowhere to post
     * is not a feature waiting to be enabled. Distinct from the per-user Discord notification
     * channel: that direct-messages one member, this posts publicly to a server channel.
     */
    public ?string $discord_purchase_webhook_url;

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

    /**
     * Nothing here is secret any more: gateway credentials moved to store_payment_gateways, where
     * the model encrypts them per row.
     */
    public static function encrypted(): array
    {
        return [];
    }
}
