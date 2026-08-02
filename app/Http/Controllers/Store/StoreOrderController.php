<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Services\StoreCurrencyService;
use App\Services\StoreInvoiceService;
use App\Utils\Helpers\Helper;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A buyer's own purchase history.
 *
 * Deliberately narrow: it only ever reads orders belonging to the signed-in user. Guest orders are
 * reachable through the result page instead, where knowledge of the order's uuid is the credential.
 */
class StoreOrderController extends Controller
{
    public function __construct(
        private StoreCurrencyService $currencies,
        private StorePaymentGatewayManager $gateways,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('browse', StorePackage::class);

        $orders = StoreOrder::where('user_id', $request->user()->id)
            ->with('items:id,store_order_id,package_name,quantity')
            ->latest('id')
            ->paginate(10)
            ->through(fn (StoreOrder $order) => [
                'uuid' => $order->uuid,
                'number' => strtoupper(substr($order->uuid, 0, 8)),
                'status' => Helper::enumKeyValue($order->status),
                'delivery_status' => Helper::enumKeyValue($order->delivery_status),
                'player_username' => $order->player_username,
                'total_formatted' => $this->currencies->format((int) $order->total, $order->currency),
                // An order left unpaid is the most recoverable money the store has, so the list
                // offers the way back to the gateway rather than only a status badge.
                'is_resumable' => $order->isResumable(),
                'amount_due_formatted' => $this->currencies->format((int) $order->amount_due, $order->currency),
                'created_at' => $order->created_at,
                'items' => $order->items->map->only(['package_name', 'quantity']),
            ]);

        return Inertia::render('Store/IndexMyStoreOrder', [
            'orders' => $orders,
        ]);
    }

    /**
     * The order's invoice, as a PDF.
     *
     * Streamed rather than redirected to a file URL: the invoice disk is private, and a signed URL
     * would be a second way in to guard. `downloadInvoice` on the order policy is the one gate —
     * staff with `read store_orders`, the buyer themselves, or anyone holding a guest order's uuid.
     */
    public function invoice(StoreOrder $order, StoreInvoiceService $invoices): StreamedResponse
    {
        $this->authorize('browse', StorePackage::class);
        $this->authorize('downloadInvoice', $order);

        // A pending order is a basket and a cancelled one is nothing at all, so neither has an
        // invoice to issue. A refunded one does: it still needs its paper trail.
        abort_unless($order->status->isInvoiceable(), 404);

        return response()->streamDownload(
            fn () => print $invoices->pdfFor($order),
            $invoices->filenameFor($order),
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function show(Request $request, StoreOrder $order): Response
    {
        $this->authorize('browse', StorePackage::class);

        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['items.grant', 'items.giftCard', 'payments:id,store_order_id,gateway,status,paid_at']);

        return Inertia::render('Store/ShowMyStoreOrder', [
            // Repeated here, not only on the result page: a buyer who closed that tab comes back
            // through their purchase history, and an offline order is unpayable without them.
            'paymentInstructions' => $order->isResumable()
                ? $this->gateways->driver($order->gateway?->value)?->paymentInstructions()
                : null,
            'order' => [
                'uuid' => $order->uuid,
                'number' => strtoupper(substr($order->uuid, 0, 8)),
                'status' => Helper::enumKeyValue($order->status),
                'delivery_status' => Helper::enumKeyValue($order->delivery_status),
                // Decided by the enum rather than by the template listing statuses of its own, so the
                // button and the route agree about what has an invoice.
                'can_download_invoice' => $order->status->isInvoiceable(),
                'is_resumable' => $order->isResumable(),
                'player_username' => $order->player_username,
                'currency' => $order->currency,
                'created_at' => $order->created_at,
                'paid_at' => $order->paid_at,
                'coupon_code' => $order->coupon_code,
                'items' => $order->items->map(fn ($item) => [
                    'package_name' => $item->package_name,
                    'quantity' => $item->quantity,
                    'total_formatted' => $this->currencies->format((int) $item->total, $order->currency),
                    'variables' => $item->variable_values,
                    'grant' => $item->grant ? [
                        'status' => Helper::enumKeyValue($item->grant->status),
                        'expires_at' => $item->grant->expires_at,
                    ] : null,
                    // The buyer's own gift card code. Shown here because the receipt email links
                    // to this page rather than carrying the code itself.
                    'gift_card' => $item->giftCard ? [
                        'code' => $item->giftCard->code,
                        'balance_formatted' => $this->currencies->format(
                            (int) $item->giftCard->balance,
                            $item->giftCard->currency_code
                        ),
                    ] : null,
                ]),
                'money' => [
                    'subtotal' => $this->currencies->format((int) $order->subtotal, $order->currency),
                    'coupon_discount' => $this->currencies->format((int) $order->coupon_discount, $order->currency),
                    'tax_amount' => $this->currencies->format((int) $order->tax_amount, $order->currency),
                    'total' => $this->currencies->format((int) $order->total, $order->currency),
                    'gift_card_amount' => $this->currencies->format((int) $order->gift_card_amount, $order->currency),
                    'amount_due' => $this->currencies->format((int) $order->amount_due, $order->currency),
                ],
                'raw' => [
                    'coupon_discount' => (int) $order->coupon_discount,
                    'tax_amount' => (int) $order->tax_amount,
                    'gift_card_amount' => (int) $order->gift_card_amount,
                ],
            ],
        ]);
    }
}
