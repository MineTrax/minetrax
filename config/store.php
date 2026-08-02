<?php

use App\Models\StorePackage;
use App\Models\StoreReferral;
use App\Models\StoreSale;
use App\Utils\ExchangeRates\FrankfurterExchangeRateProvider;
use App\Utils\Payments\ManualPaymentGateway;
use App\Utils\Payments\PayPalPaymentGateway;
use App\Utils\Payments\StripePaymentGateway;

return [

    /*
    |--------------------------------------------------------------------------
    | Store Module
    |--------------------------------------------------------------------------
    |
    | Deploy-time configuration for the Store module. Runtime, admin-editable
    | values (currency, tax, guest checkout, gateway credentials) live in
    | App\Settings\StoreSettings instead.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | If enabled, the Store feature will be enabled.
    |--------------------------------------------------------------------------
    */
    'enabled' => env('STORE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Payment gateway drivers, keyed by their gateway key.
    |
    | Adding a new gateway is a new driver class plus one line here, e.g.
    | 'stripe' => \App\Utils\Payments\StripePaymentGateway::class
    |
    | The key is stored on orders and payments and appears in the webhook URL,
    | so it must be url-safe and must never change once orders exist.
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'manual' => ManualPaymentGateway::class,
        'stripe' => StripePaymentGateway::class,
        'paypal' => PayPalPaymentGateway::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exchange rate providers, keyed by their provider key.
    |
    | Adding a feed is a new class implementing
    | App\Contracts\StoreExchangeRateProviderContract plus one line here, so
    | swapping to a paid feed never touches the refresh job or the currencies.
    |
    | Whether rates refresh at all is a runtime setting
    | (StoreSettings::$currency_rate_source); which feed does it is deploy-time,
    | because a paid one needs its credential in the environment.
    |--------------------------------------------------------------------------
    */
    'rate_providers' => [
        'frankfurter' => FrankfurterExchangeRateProvider::class,
    ],

    'rate_provider' => env('STORE_RATE_PROVIDER', 'frankfurter'),

    /*
    |--------------------------------------------------------------------------
    | Disk to use for Store package and category images.
    |--------------------------------------------------------------------------
    */
    'module_disk' => env('STORE_MODULE_DISK', 'media'),

    /*
    |--------------------------------------------------------------------------
    | Disk to use for generated invoices. Should not be publicly readable.
    |--------------------------------------------------------------------------
    */
    'invoice_disk' => env('STORE_INVOICE_DISK', 'private'),

    /*
    |--------------------------------------------------------------------------
    | Deliveries waiting on an offline player for longer than this many days
    | are flagged for attention on the admin order detail. They are never
    | auto-cancelled; the player may still return and receive them.
    |--------------------------------------------------------------------------
    */
    'deferred_attention_days' => env('STORE_DEFERRED_ATTENTION_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Unpaid orders older than this are cancelled, releasing any reserved
    | coupon use and re-crediting any redeemed gift card balance.
    |--------------------------------------------------------------------------
    */
    'pending_order_ttl_hours' => env('STORE_PENDING_ORDER_TTL_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Abandoned carts older than this are pruned.
    |--------------------------------------------------------------------------
    */
    'cart_ttl_days' => env('STORE_CART_TTL_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Maximum number of distinct line items allowed in a single cart.
    |--------------------------------------------------------------------------
    */
    'cart_max_items' => env('STORE_CART_MAX_ITEMS', 20),

    /*
    |--------------------------------------------------------------------------
    | Attempts allowed for a store delivery command before it stays failed.
    | Retries are picked up by the existing every-minute command queue sweeper,
    | which only retries rows where attempts < max_attempts.
    |--------------------------------------------------------------------------
    */
    'command_max_attempts' => env('STORE_COMMAND_MAX_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | What may own a store command, keyed by model class.
    |
    | Commands all live in one table, `store_commands`, with a polymorphic owner
    | — see the comment on that table for why a table per owner would break the
    | double-delivery guard on store_order_deliveries.
    |
    | Adding an owner is: `use HasStoreCommandsTrait` on the model, one line
    | here, and a commands section on its admin form. No migration, and no edit
    | to StoreCommandDispatchService.
    |
    | `triggers` is the subset of StoreCommandTrigger that owner may use, so a
    | referral offering only `purchase` is stated once here rather than being
    | hardcoded into a form and a request that can disagree. The class key is
    | written to store_commands.commandable_type, so it must not change once
    | commands exist. StoreCommand::booted() refuses anything not listed.
    |--------------------------------------------------------------------------
    */
    'command_owners' => [
        StorePackage::class => [
            'label' => 'Package',
            'triggers' => ['purchase', 'expiry', 'refund', 'chargeback'],
        ],
        StoreSale::class => [
            'label' => 'Sale',
            'triggers' => ['purchase', 'expiry', 'refund', 'chargeback'],
        ],
        // Purchase only: a referral's commands are a thank-you for a sale that landed, and there is
        // nothing to expire or take back when it unwinds — the money is clawed back instead.
        StoreReferral::class => [
            'label' => 'Referral',
            'triggers' => ['purchase'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate limits, in requests per minute.
    |--------------------------------------------------------------------------
    */
    'ratelimit' => [
        'checkout' => env('STORE_RATELIMIT_CHECKOUT', 10),
        'code' => env('STORE_RATELIMIT_CODE', 20),
        'webhook' => env('STORE_RATELIMIT_WEBHOOK', 300),
    ],
];
