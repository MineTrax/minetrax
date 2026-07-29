<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\CommandQueueStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentGateway;
use App\Enums\StorePaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCommandDispatchService;
use App\Services\StoreCurrencyService;
use App\Services\StoreOrderService;
use App\Utils\Payments\AbstractStorePaymentGateway;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StoreOrderController extends Controller
{
    public function __construct(
        private StoreOrderService $orders,
        private StoreCurrencyService $currencies,
        private StoreCommandDispatchService $dispatcher,
        private StorePaymentGatewayManager $gateways,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', StoreOrder::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'uuid',
            'user_id',
            'email',
            'player_uuid',
            'player_username',
            'currency',
            'total',
            'amount_due',
            'base_total',
            'status',
            'delivery_status',
            'gateway',
            'paid_at',
            'created_at',
        ];

        $orders = QueryBuilder::for(StoreOrder::class)
            ->select($fields)
            ->with('user:id,username,name')
            ->withCount('items')
            ->allowedFilters(...[
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'uuid', 'email', 'player_username', 'player_uuid'])),
            ])
            ->allowedSorts(...$fields)
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        // Money is stored in each order's own currency, so it has to be formatted per row rather
        // than once for the page.
        $orders->getCollection()->transform(function (StoreOrder $order) {
            $order->total_formatted = $this->currencies->format((int) $order->total, $order->currency);

            return $order;
        });

        return Inertia::render('Admin/StoreOrder/IndexStoreOrder', [
            'orders' => $orders,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
            'statuses' => collect(StoreOrderStatus::cases())->map->value,
        ]);
    }

    public function show(StoreOrder $order): Response
    {
        $this->authorize('view', $order);

        $order->load([
            'items',
            'items.grant',
            'payments.refunds',
            'user:id,username,name',
            'country:id,name,iso_code',
            'coupon:id,code',
            'deliveries.commandQueue:id,status,attempts,max_attempts,execute_at,output,updated_at',
            'deliveries.server:id,name',
            'activities.causer:id,name,username',
        ]);

        $attentionCutoff = now()->subDays((int) config('store.deferred_attention_days', 3));

        return Inertia::render('Admin/StoreOrder/ShowStoreOrder', [
            'order' => $order,
            'money' => [
                'subtotal' => $this->currencies->format((int) $order->subtotal, $order->currency),
                'sale_discount' => $this->currencies->format((int) $order->sale_discount, $order->currency),
                'coupon_discount' => $this->currencies->format((int) $order->coupon_discount, $order->currency),
                'tax_amount' => $this->currencies->format((int) $order->tax_amount, $order->currency),
                'total' => $this->currencies->format((int) $order->total, $order->currency),
                'gift_card_amount' => $this->currencies->format((int) $order->gift_card_amount, $order->currency),
                'amount_due' => $this->currencies->format((int) $order->amount_due, $order->currency),
                'base_total' => $this->currencies->format((int) $order->base_total, $order->base_currency),
            ],
            // Surfaced rather than auto-cancelled: the player may still come back for it.
            'stuckDeliveries' => $order->deliveries
                ->filter(fn ($delivery) => $delivery->commandQueue?->status === CommandQueueStatus::DEFERRED
                    && $delivery->created_at?->lt($attentionCutoff))
                ->pluck('id')
                ->values(),
            'canRefundAtGateway' => $this->canRefundAtGateway($order),
            'timeline' => $this->timeline($order),
            'permissions' => [
                'update' => request()->user()->can('update', $order),
                'refund' => request()->user()->can('refund', $order),
                'resend' => request()->user()->can('resend', $order),
            ],
        ]);
    }

    /**
     * Confirm a payment settled outside the site: a bank transfer, or a gateway whose webhook
     * never arrived. Runs the identical transition and delivery path a real webhook would.
     */
    public function markPaid(Request $request, StoreOrder $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $payment = $order->payments()->where('status', StorePaymentStatus::PENDING)->latest('id')->first()
            ?? $order->payments()->create([
                // Falls back to manual: an admin confirming payment by hand is, by definition,
                // settling it outside any gateway.
                'gateway' => $order->gateway ?? StorePaymentGateway::MANUAL,
                'status' => StorePaymentStatus::PENDING,
                'amount' => $order->amount_due,
                'currency' => $order->currency,
            ]);

        $marked = $this->orders->markPaid($order, $payment, (int) $order->amount_due, $order->currency);

        if (! $marked) {
            return back()->with(['toast' => ['type' => 'error', 'title' => __('This order cannot be marked paid.')]]);
        }

        $order->update(['updated_by' => $request->user()->id]);

        return back()->with(['toast' => ['type' => 'success', 'title' => __('Order Marked as Paid')]]);
    }

    public function cancel(Request $request, StoreOrder $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        if (! $this->orders->cancel($order, $validated['reason'] ?? __('Cancelled by staff.'))) {
            return back()->with(['toast' => ['type' => 'error', 'title' => __('This order cannot be cancelled.')]]);
        }

        $order->update(['updated_by' => $request->user()->id]);

        return back()->with(['toast' => ['type' => 'success', 'title' => __('Order Cancelled')]]);
    }

    /**
     * Refund, at the gateway when the driver can do it and as a book entry otherwise.
     *
     * A gateway failure aborts before anything is recorded: an order marked refunded when the
     * money never moved is worse than an error message.
     */
    public function refund(Request $request, StoreOrder $order): RedirectResponse
    {
        $this->authorize('refund', $order);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
            'at_gateway' => ['required', 'boolean'],
        ]);

        $payment = $order->payments()->where('status', StorePaymentStatus::COMPLETED)->latest('id')->first();

        if (! $payment) {
            throw ValidationException::withMessages(['amount' => __('This order has no completed payment to refund.')]);
        }

        $remaining = (int) $payment->amount - (int) $payment->refunded_amount;

        if ($validated['amount'] > $remaining) {
            throw ValidationException::withMessages([
                'amount' => __('Only :amount is left to refund on this payment.', [
                    'amount' => $this->currencies->format($remaining, $payment->currency),
                ]),
            ]);
        }

        $gatewayRefundId = null;

        if ($validated['at_gateway']) {
            try {
                $gatewayRefundId = $this->gateways
                    ->driverOrFail($payment->gateway->value)
                    ->refund($payment, $validated['amount'], $validated['reason'] ?? null);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages(['amount' => $e->getMessage()]);
            }
        }

        $recorded = $this->orders->recordRefund(
            payment: $payment,
            amountMinor: $validated['amount'],
            gatewayRefundId: $gatewayRefundId,
            reason: $validated['reason'] ?? null,
            createdBy: $request->user()->id,
        );

        if (! $recorded) {
            return back()->with(['toast' => ['type' => 'error', 'title' => __('This order cannot be refunded in its current state.')]]);
        }

        $order->update(['updated_by' => $request->user()->id]);

        return back()->with(['toast' => ['type' => 'success', 'title' => __('Refund Recorded')]]);
    }

    /**
     * Re-queue deliveries that never landed. Idempotent by design: it reuses the existing
     * delivery rows rather than creating new ones, so a buyer cannot be given the same package
     * twice by an impatient admin.
     */
    public function resend(Request $request, StoreOrder $order): RedirectResponse
    {
        $this->authorize('resend', $order);

        if (! $order->status->isPaidState()) {
            return back()->with(['toast' => ['type' => 'error', 'title' => __('Only a paid order can be delivered.')]]);
        }

        $validated = $request->validate(['include_unfinished' => ['nullable', 'boolean']]);

        $count = $this->dispatcher->redispatchForOrder($order, (bool) ($validated['include_unfinished'] ?? false));

        if ($count > 0) {
            // Worth a line in the history: a buyer who says they were delivered twice, or not at
            // all, is answered by who pressed this and when.
            activity(StoreOrderService::ACTIVITY_LOG)
                ->performedOn($order)
                ->causedBy($request->user())
                ->event('delivery_resent')
                ->withProperties(['commands' => $count])
                ->log(__('Delivery re-sent'));
        }

        return back()->with(['toast' => [
            'type' => $count > 0 ? 'success' : 'info',
            'title' => $count > 0
                ? __(':count command(s) re-queued', ['count' => $count])
                : __('Nothing needed re-sending'),
        ]]);
    }

    /**
     * The order's history, oldest first.
     *
     * "Placed" is derived from the order itself rather than logged, because the log only starts at
     * the first transition and an order with no history at all should still show where it began.
     * Money is formatted here, in the order's own currency, as everywhere else.
     *
     * @return array<int, array<string, mixed>>
     */
    private function timeline(StoreOrder $order): array
    {
        $entries = [[
            'event' => 'placed',
            'description' => __('Order placed'),
            'causer' => $order->user?->only('id', 'name', 'username'),
            'at' => $order->created_at?->toIso8601String(),
            'detail' => null,
        ]];

        foreach ($order->activities as $activity) {
            $properties = (array) $activity->properties->all();
            $amount = $properties['amount'] ?? null;

            $entries[] = [
                'event' => (string) $activity->event,
                'description' => (string) $activity->description,
                'causer' => $activity->causer?->only('id', 'name', 'username'),
                'at' => $activity->created_at?->toIso8601String(),
                'detail' => $amount !== null
                    ? $this->currencies->format((int) $amount, $properties['currency'] ?? $order->currency)
                    : ($properties['reason'] ?? null),
            ];
        }

        return $entries;
    }

    private function canRefundAtGateway(StoreOrder $order): bool
    {
        $payment = $order->payments->firstWhere('status', StorePaymentStatus::COMPLETED);

        if (! $payment || ! $payment->gateway_transaction_id) {
            return false;
        }

        $driver = $this->gateways->driver($payment->gateway?->value);

        // The base driver throws for anything that has no automated refund API.
        return $driver !== null
            && (new \ReflectionMethod($driver, 'refund'))->getDeclaringClass()->getName() !== AbstractStorePaymentGateway::class;
    }
}
