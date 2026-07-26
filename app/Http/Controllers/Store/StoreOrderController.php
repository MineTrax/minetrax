<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Services\StoreCurrencyService;
use App\Utils\Helpers\Helper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A buyer's own purchase history.
 *
 * Deliberately narrow: it only ever reads orders belonging to the signed-in user. Guest orders are
 * reachable through the result page instead, where knowledge of the order's uuid is the credential.
 */
class StoreOrderController extends Controller
{
    public function __construct(private StoreCurrencyService $currencies) {}

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
                'created_at' => $order->created_at,
                'items' => $order->items->map->only(['package_name', 'quantity']),
            ]);

        return Inertia::render('Store/IndexMyStoreOrder', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, StoreOrder $order): Response
    {
        $this->authorize('browse', StorePackage::class);

        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['items.grant', 'payments:id,store_order_id,gateway,status,paid_at']);

        return Inertia::render('Store/ShowMyStoreOrder', [
            'order' => [
                'uuid' => $order->uuid,
                'number' => strtoupper(substr($order->uuid, 0, 8)),
                'status' => Helper::enumKeyValue($order->status),
                'delivery_status' => Helper::enumKeyValue($order->delivery_status),
                'player_username' => $order->player_username,
                'currency' => $order->currency,
                'created_at' => $order->created_at,
                'paid_at' => $order->paid_at,
                'coupon_code' => $order->coupon_code,
                'items' => $order->items->map(fn ($item) => [
                    'package_name' => $item->package_name,
                    'quantity' => $item->quantity,
                    'total_formatted' => $this->currencies->format((int) $item->total, $order->currency),
                    // The stored `value` feeds server commands; the buyer only needs the label.
                    'options' => collect($item->options ?? [])->map(fn ($option) => [
                        'name' => $option['name'] ?? null,
                        'label' => $option['label'] ?? null,
                    ]),
                    'grant' => $item->grant ? [
                        'status' => Helper::enumKeyValue($item->grant->status),
                        'expires_at' => $item->grant->expires_at,
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
