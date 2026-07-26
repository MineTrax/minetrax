<?php

use App\Utils\Payments\ManualPaymentGateway;
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
    ],

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
    | Rate limits, in requests per minute.
    |--------------------------------------------------------------------------
    */
    'ratelimit' => [
        'checkout' => env('STORE_RATELIMIT_CHECKOUT', 10),
        'code' => env('STORE_RATELIMIT_CODE', 20),
        'webhook' => env('STORE_RATELIMIT_WEBHOOK', 300),
    ],
];
