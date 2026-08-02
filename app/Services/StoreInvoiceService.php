<?php

namespace App\Services;

use App\Models\StoreOrder;
use App\Settings\GeneralSettings;
use App\Settings\StoreSettings;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * The buyer's invoice, as a PDF.
 *
 * Written to the private disk and re-served rather than rendered on every request: PDF generation is
 * slow, and an invoice is a record of something that already happened. The stored copy is discarded
 * when the order changes underneath it — a refund has to reach the paperwork.
 */
class StoreInvoiceService
{
    public function __construct(
        private StoreCurrencyService $currencies,
        private StoreSettings $settings,
        private GeneralSettings $general,
    ) {}

    /**
     * The invoice's bytes, generating and caching them if needed.
     */
    public function pdfFor(StoreOrder $order): string
    {
        $diskName = config('store.invoice_disk');
        $disk = Storage::disk($diskName);
        $path = $this->pathFor($order);

        if ($disk->exists($path) && ! $this->isStale($order, (int) $disk->lastModified($path))) {
            return $disk->get($path);
        }

        // Written through the disk rather than returned and stored by hand: `->disk()` keeps the
        // file private, which for an invoice is the point.
        Pdf::view('store.invoice', $this->invoiceData($order))
            ->format('a4')
            ->disk($diskName)
            ->save($path);

        return $disk->get($path);
    }

    /**
     * What the browser should call the download.
     */
    public function filenameFor(StoreOrder $order): string
    {
        return 'invoice-'.$this->numberFor($order).'.pdf';
    }

    /**
     * Where the cached copy lives.
     *
     * Keyed on the uuid rather than the id: it is the order's public identifier, so a stray path in a
     * log leaks nothing about how many orders the store has taken.
     */
    public function pathFor(StoreOrder $order): string
    {
        return 'store/invoices/'.$order->uuid.'.pdf';
    }

    /**
     * A refund, a resend or a status change all touch `updated_at`, and any of them can change what
     * the invoice should say. Cheaper than diffing the document.
     */
    private function isStale(StoreOrder $order, int $fileModifiedAt): bool
    {
        return $order->updated_at?->getTimestamp() > $fileModifiedAt;
    }

    /**
     * The short, human-quotable form of the uuid, matching what every other page and the receipt
     * email call the order.
     */
    public function numberFor(StoreOrder $order): string
    {
        return strtoupper(substr($order->uuid, 0, 8));
    }

    /**
     * Everything the template prints.
     *
     * Money is formatted here, in the order's own currency, because the template must never do
     * arithmetic on minor units — the whole reason the invariant holds anywhere else.
     *
     * Public so a test can render the view directly and assert what an invoice actually says. Going
     * through the PDF for that would mean either parsing compressed PDF streams or faking the
     * renderer, and a fake writes no file for pdfFor() to return.
     *
     * @return array<string, mixed>
     */
    public function invoiceData(StoreOrder $order): array
    {
        $order->loadMissing(['items', 'payments.refunds', 'user:id,username,email']);

        $format = fn (?int $amount) => $this->currencies->format((int) $amount, $order->currency);

        // Refunds hang off the payment, not the order — one order can have several charge attempts.
        $refunded = (int) $order->payments->flatMap->refunds->sum('amount');

        return [
            'storeName' => $this->settings->store_name,
            'siteName' => $this->general->site_name ?? config('app.name'),
            'number' => $this->numberFor($order),
            'order' => $order,
            'issuedAt' => ($order->paid_at ?? $order->created_at)->toDayDateTimeString(),
            'buyer' => $order->user?->username ?? $order->player_username ?? __('Guest'),
            'buyerEmail' => $order->user?->email ?? $order->email,
            // Empty unless the store collects one. Read from the order's own snapshot rather than
            // from any saved address, so reprinting an old invoice reproduces it exactly.
            'billingAddress' => $this->billingAddressLines($order),
            'status' => __(ucfirst(str_replace('_', ' ', $order->status->value))),
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->package_name,
                'quantity' => (int) $item->quantity,
                'unit' => $format((int) $item->unit_price),
                'total' => $format((int) $item->total),
            ])->all(),
            'money' => [
                'subtotal' => $format((int) $order->subtotal),
                'sale_discount' => (int) $order->sale_discount > 0 ? $format((int) $order->sale_discount) : null,
                'coupon_discount' => (int) $order->coupon_discount > 0 ? $format((int) $order->coupon_discount) : null,
                'tax_amount' => (int) $order->tax_amount > 0 ? $format((int) $order->tax_amount) : null,
                'tax_label' => $order->tax_name ?: __('Tax'),
                'total' => $format((int) $order->total),
                'gift_card_amount' => (int) $order->gift_card_amount > 0 ? $format((int) $order->gift_card_amount) : null,
                'amount_due' => $format((int) $order->amount_due),
                // Only shown when there is one, so a clean invoice is not carrying an empty row that
                // makes a buyer wonder whether they were refunded.
                'refunded' => $refunded > 0 ? $format($refunded) : null,
            ],
            'couponCode' => $order->coupon_code,
            'gateway' => $order->gateway?->value,
            'playerUsername' => $order->player_username,
        ];
    }

    /**
     * The billing address as lines to print, or an empty array when none was collected.
     *
     * Blank parts are dropped rather than printed empty: an optional flat number, or a country with
     * no states, must not leave a hole in the middle of the block.
     *
     * @return array<int, string>
     */
    private function billingAddressLines(StoreOrder $order): array
    {
        $cityAndState = collect([$order->billing_city, $order->billing_state])
            ->filter()
            ->implode(', ');

        return collect([
            $order->billing_name,
            $order->billing_address_line1,
            $order->billing_address_line2,
            $cityAndState,
            $order->billing_postal_code,
            $order->billing_country,
        ])->filter()->values()->all();
    }
}
