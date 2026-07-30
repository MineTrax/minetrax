<?php

use App\Enums\StorePaymentStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\Store\SendStorePurchaseDiscordWebhookJob;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Models\User;
use App\Services\StoreCurrencyService;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

const DISCORD_WEBHOOK = 'https://discord.com/api/webhooks/123/abcdef';

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    // Receipts and staff notices are not what this file is about, and a real Discord DM channel in
    // the middle of them would be a second outbound call.
    Notification::fake();
});

/**
 * @param  array<string, mixed>  $values
 */
function discordSettings(array $values): void
{
    $settings = app(StoreSettings::class);

    foreach ($values as $key => $value) {
        $settings->{$key} = $value;
    }

    $settings->save();
}

/**
 * A paid-through order, so the announcement runs off the real StoreOrderPaid event.
 */
function announceableOrder(array $overrides = []): StoreOrder
{
    $order = StoreOrder::factory()->create(array_merge([
        'total' => 1500,
        'amount_due' => 1500,
        'player_username' => 'Notch',
    ], $overrides));

    $order->items()->create([
        'package_name' => 'Gold Rank',
        'quantity' => 1,
        'unit_price_original' => 1500,
        'unit_price' => 1500,
        'total' => 1500,
    ]);

    StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'amount' => 1500,
        'currency' => $order->currency,
        'status' => StorePaymentStatus::PENDING,
    ]);

    return $order->fresh();
}

function markOrderPaid(StoreOrder $order): void
{
    app(StoreOrderService::class)->markPaid(
        $order,
        $order->payments->first(),
        1500,
        $order->currency,
        'txn_'.$order->id,
    );
}

test('nothing is queued when no webhook is configured', function () {
    discordSettings(['discord_purchase_webhook_url' => null]);
    Queue::fake();

    markOrderPaid(announceableOrder());

    Queue::assertNotPushed(SendStorePurchaseDiscordWebhookJob::class);
});

test('a paid order queues the announcement', function () {
    discordSettings(['discord_purchase_webhook_url' => DISCORD_WEBHOOK]);
    Queue::fake();

    markOrderPaid(announceableOrder());

    Queue::assertPushed(SendStorePurchaseDiscordWebhookJob::class);
});

test('the announcement posts an embed to the configured webhook', function () {
    discordSettings(['discord_purchase_webhook_url' => DISCORD_WEBHOOK, 'hide_buyer_identity' => false]);
    Http::fake([DISCORD_WEBHOOK => Http::response('', 204)]);

    $order = announceableOrder();
    (new SendStorePurchaseDiscordWebhookJob($order))->handle(app(StoreSettings::class), app(StoreCurrencyService::class));

    Http::assertSent(function ($request) {
        $embed = $request->data()['embeds'][0];

        return $request->url() === DISCORD_WEBHOOK
            && str_contains($embed['description'], 'Notch')
            && str_contains($embed['fields'][0]['value'], 'Gold Rank')
            && $embed['fields'][1]['value'] === '$15.00';
    });
});

test('a members username is used in preference to their player name', function () {
    discordSettings(['discord_purchase_webhook_url' => DISCORD_WEBHOOK, 'hide_buyer_identity' => false]);
    Http::fake([DISCORD_WEBHOOK => Http::response('', 204)]);

    $buyer = User::factory()->create(['username' => 'bigspender']);
    $order = announceableOrder(['user_id' => $buyer->id]);
    (new SendStorePurchaseDiscordWebhookJob($order))->handle(app(StoreSettings::class), app(StoreCurrencyService::class));

    Http::assertSent(fn ($request) => str_contains($request->data()['embeds'][0]['description'], 'bigspender'));
});

test('hiding buyer identity anonymises the announcement', function () {
    // A channel post is as public as the homepage list, so the same setting has to reach it.
    discordSettings(['discord_purchase_webhook_url' => DISCORD_WEBHOOK, 'hide_buyer_identity' => true]);
    Http::fake([DISCORD_WEBHOOK => Http::response('', 204)]);

    $order = announceableOrder();
    (new SendStorePurchaseDiscordWebhookJob($order))->handle(app(StoreSettings::class), app(StoreCurrencyService::class));

    Http::assertSent(fn ($request) => ! str_contains($request->data()['embeds'][0]['description'], 'Notch'));
});

test('the total is announced in the currency it was paid in', function () {
    // ¥1500 is 1500 minor units; announcing $15.00 would misreport the sale by a factor of ten.
    discordSettings(['discord_purchase_webhook_url' => DISCORD_WEBHOOK]);
    StoreCurrency::factory()->zeroDecimal()->create();
    Http::fake([DISCORD_WEBHOOK => Http::response('', 204)]);

    $order = announceableOrder(['currency' => 'JPY']);
    (new SendStorePurchaseDiscordWebhookJob($order))->handle(app(StoreSettings::class), app(StoreCurrencyService::class));

    Http::assertSent(fn ($request) => str_contains($request->data()['embeds'][0]['fields'][1]['value'], '1,500'));
});

test('the job posts nothing once the webhook has been cleared', function () {
    // The job may already be queued when an admin removes the URL.
    discordSettings(['discord_purchase_webhook_url' => null]);
    Http::fake();

    (new SendStorePurchaseDiscordWebhookJob(announceableOrder()))
        ->handle(app(StoreSettings::class), app(StoreCurrencyService::class));

    Http::assertNothingSent();
});

test('a rejected webhook is logged rather than failing the sale', function () {
    // The money has already arrived. A wrong URL must never look like a failed purchase.
    discordSettings(['discord_purchase_webhook_url' => DISCORD_WEBHOOK]);
    Http::fake([DISCORD_WEBHOOK => Http::response('', 404)]);
    Log::spy();

    $job = new SendStorePurchaseDiscordWebhookJob(announceableOrder());
    // The queue would normally supply this; without it release() has nothing to talk to.
    $job->setJob(Mockery::mock(Job::class)->shouldIgnoreMissing());

    $job->handle(app(StoreSettings::class), app(StoreCurrencyService::class));

    Log::shouldHaveReceived('warning')->once();
});

test('an order that failed to pay announces nothing', function () {
    discordSettings(['discord_purchase_webhook_url' => DISCORD_WEBHOOK]);
    Queue::fake([SendStorePurchaseDiscordWebhookJob::class, RunCommandQueueJob::class]);

    $order = announceableOrder();

    // The wrong amount fails the payment and leaves the order untouched, so no sale happened.
    app(StoreOrderService::class)->markPaid($order, $order->payments->first(), 500, $order->currency, 'txn_short');

    Queue::assertNotPushed(SendStorePurchaseDiscordWebhookJob::class);
});

test('the webhook url must be a discord one', function () {
    // It is fetched server-side, so accepting any host would turn the settings form into an
    // outbound request to wherever an admin was told to paste.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.setting.store.update'), [
        'store_name' => 'My Store',
        'store_description' => null,
        'base_currency' => 'USD',
        'currency_rate_source' => 'manual',
        'tax_mode' => 'none',
        'tax_rate_bp' => 0,
        'tax_label' => 'Tax',
        'enable_guest_checkout' => true,
        'require_email_on_guest_checkout' => true,
        'mojang_username_verification' => true,
        'terms_text' => null,
        'show_recent_purchases' => true,
        'show_purchase_goal' => false,
        'purchase_goal_amount' => 0,
        'show_top_donor' => false,
        'hide_buyer_identity' => false,
        'notify_staff_on_purchase' => true,
        'discord_purchase_webhook_url' => 'https://evil.example.com/api/webhooks/1/2',
        'auto_ban_on_chargeback' => false,
    ])->assertSessionHasErrors('discord_purchase_webhook_url');
});
