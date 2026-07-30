<?php

use App\Enums\StorePaymentRefundType;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Models\User;
use App\Services\StoreInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    // Invoices are cached to the private disk; a real one would leave files behind between runs.
    Storage::fake(config('store.invoice_disk'));
});

/**
 * A completed order with a line and a payment — enough for a real invoice.
 */
function invoiceableOrder(array $overrides = []): StoreOrder
{
    $order = StoreOrder::factory()->completed()->create(array_merge([
        'subtotal' => 2000,
        'total' => 2000,
        'amount_due' => 2000,
        'player_username' => 'Notch',
    ], $overrides));

    $order->items()->create([
        'package_name' => 'Gold Rank',
        'quantity' => 2,
        'unit_price_original' => 1000,
        'unit_price' => 1000,
        'total' => 2000,
    ]);

    StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'amount' => 2000,
        'currency' => $order->currency,
    ]);

    return $order->fresh();
}

test('the buyer can download their own invoice', function () {
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);

    $response = $this->actingAs($buyer)->get(route('store.order.invoice', $order->uuid));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('the download is named after the order', function () {
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);
    $number = strtoupper(substr($order->uuid, 0, 8));

    $this->actingAs($buyer)->get(route('store.order.invoice', $order->uuid))
        ->assertDownload('invoice-'.$number.'.pdf');
});

test('the file really is a pdf', function () {
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);

    $pdf = app(StoreInvoiceService::class)->pdfFor($order);

    expect(substr($pdf, 0, 5))->toBe('%PDF-');
});

test('another member cannot download somebody elses invoice', function () {
    // The whole reason this is policy-gated: the uuid is guessable to nobody, but the route is not
    // a secret and an order belonging to an account must check that account.
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('store.order.invoice', $order->uuid))
        ->assertForbidden();
});

test('a guest cannot download a members invoice', function () {
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);

    $this->get(route('store.order.invoice', $order->uuid))->assertForbidden();
});

test('a guest order is downloadable by whoever holds its uuid', function () {
    // The same rule the result page uses: a guest has no account to authorise against, and the uuid
    // is a v4 that never appears in a listing.
    $order = invoiceableOrder(['user_id' => null, 'email' => 'guest@example.com']);

    $this->get(route('store.order.invoice', $order->uuid))->assertOk();
});

test('staff who can read orders can download any invoice', function () {
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);

    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('store.order.invoice', $order->uuid))->assertOk();
});

test('a pending order has no invoice', function () {
    // It is a basket, not a transaction.
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);
    $order->update(['status' => 'pending']);

    $this->actingAs($buyer)->get(route('store.order.invoice', $order->uuid))->assertNotFound();
});

test('a cancelled order has no invoice', function () {
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);
    $order->update(['status' => 'cancelled']);

    $this->actingAs($buyer)->get(route('store.order.invoice', $order->uuid))->assertNotFound();
});

test('a refunded order still has its invoice', function () {
    // The money moved, so the paper trail has to survive the refund.
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);
    $order->update(['status' => 'refunded', 'refunded_at' => now()]);

    $this->actingAs($buyer)->get(route('store.order.invoice', $order->uuid))->assertOk();
});

test('the invoice is unavailable when the module is disabled', function () {
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);

    config(['store.enabled' => false]);

    $this->actingAs($buyer)->get(route('store.order.invoice', $order->uuid))->assertForbidden();
});

test('the invoice is cached to the private disk', function () {
    $order = invoiceableOrder();
    $invoices = app(StoreInvoiceService::class);

    $invoices->pdfFor($order);

    Storage::disk(config('store.invoice_disk'))->assertExists($invoices->pathFor($order));
});

test('a second request serves the cached copy', function () {
    // Rendering a PDF is slow and an invoice records something that already happened.
    $order = invoiceableOrder();
    $invoices = app(StoreInvoiceService::class);
    $disk = Storage::disk(config('store.invoice_disk'));

    $invoices->pdfFor($order);
    // A marker only a cache hit can return.
    $disk->put($invoices->pathFor($order), 'CACHED');

    expect($invoices->pdfFor($order))->toBe('CACHED');
});

test('changing the order discards the cached copy', function () {
    // A refund has to reach the paperwork.
    $order = invoiceableOrder();
    $invoices = app(StoreInvoiceService::class);
    $disk = Storage::disk(config('store.invoice_disk'));

    $invoices->pdfFor($order);
    $disk->put($invoices->pathFor($order), 'STALE');

    $this->travel(2)->seconds();
    $order->update(['status' => 'refunded', 'refunded_at' => now()]);

    expect($invoices->pdfFor($order->fresh()))->not->toBe('STALE');
});

test('the invoice is keyed on the uuid rather than the id', function () {
    // A path in a log then leaks nothing about how many orders the store has taken.
    $order = invoiceableOrder();

    expect(app(StoreInvoiceService::class)->pathFor($order))
        ->toBe('store/invoices/'.$order->uuid.'.pdf');
});

test('the buyer order page offers the download', function () {
    $buyer = User::factory()->create();
    $order = invoiceableOrder(['user_id' => $buyer->id]);

    $this->actingAs($buyer)->get(route('store.my-order.show', $order->uuid))
        ->assertInertia(fn ($page) => $page->where('order.can_download_invoice', true));
});

test('the result page offers the download once paid', function () {
    // A guest's only route to it.
    $order = invoiceableOrder(['user_id' => null]);

    $this->get(route('store.order.result', $order->uuid))
        ->assertInertia(fn ($page) => $page->where('order.can_download_invoice', true));
});

test('the result page offers no download while the order is pending', function () {
    $order = invoiceableOrder(['user_id' => null]);
    $order->update(['status' => 'pending']);

    $this->get(route('store.order.result', $order->uuid))
        ->assertInertia(fn ($page) => $page->where('order.can_download_invoice', false));
});

test('the admin order page offers the download', function () {
    $order = invoiceableOrder();

    $this->actingAs(User::whereId(1)->first())
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertInertia(fn ($page) => $page->where('order.can_download_invoice', true));
});

test('the pdf driver needs no chromium on the server', function () {
    // MineTrax is self-hosted. spatie/laravel-pdf defaults to browsershot, which needs Node.js and a
    // Chrome binary — an owner on shared hosting would discover that only when a buyer clicked
    // Download Invoice. dompdf is pure PHP, so the default is overridden in config/laravel-pdf.php.
    expect(config('laravel-pdf.driver'))->toBe('dompdf');
});

test('the invoice states the order figures', function () {
    // The %PDF- check only proves it is a PDF. This renders the template with the data the service
    // hands it, which is the half that can silently produce a blank invoice.
    $buyer = User::factory()->create(['username' => 'bigspender']);
    $order = invoiceableOrder(['user_id' => $buyer->id]);
    $number = strtoupper(substr($order->uuid, 0, 8));

    $html = view('store.invoice', app(StoreInvoiceService::class)->invoiceData($order))->render();

    foreach ([$number, 'Gold Rank', 'bigspender', 'Notch', '$20.00'] as $expected) {
        expect($html)->toContain($expected);
    }
});

test('a refunded invoice shows what went back', function () {
    $order = invoiceableOrder();
    $order->payments->first()->refunds()->create([
        'amount' => 500,
        'currency' => $order->currency,
        'type' => StorePaymentRefundType::REFUND,
        'reason' => 'Goodwill',
    ]);
    $order->update(['status' => 'partially_refunded']);

    $html = view('store.invoice', app(StoreInvoiceService::class)->invoiceData($order->fresh()))->render();

    expect($html)->toContain('Refunded');
    expect($html)->toContain('$5.00');
});

test('a clean invoice carries no refund row', function () {
    // An empty row would have a buyer wondering whether they had been refunded.
    $order = invoiceableOrder();

    $html = view('store.invoice', app(StoreInvoiceService::class)->invoiceData($order))->render();

    expect($html)->not->toContain('Refunded');
});
