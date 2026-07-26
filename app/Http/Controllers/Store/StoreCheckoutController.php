<?php

namespace App\Http\Controllers\Store;

use App\Enums\StoreOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Services\GeolocationService;
use App\Services\StoreCartService;
use App\Services\StoreCheckoutService;
use App\Services\StoreCurrencyService;
use App\Services\StoreOrderService;
use App\Services\StorePlayerResolver;
use App\Settings\StoreSettings;
use App\Utils\Payments\Data\StoreGatewayEventData;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class StoreCheckoutController extends Controller
{
    public function __construct(
        private StoreCartService $carts,
        private StoreCheckoutService $checkout,
        private StoreCurrencyService $currencies,
        private StorePlayerResolver $players,
        private StoreOrderService $orders,
        private StorePaymentGatewayManager $gateways,
        private StoreSettings $settings,
    ) {}

    public function create(Request $request)
    {
        $this->authorize('browse', StorePackage::class);

        $cart = $this->carts->current($request, create: false);

        if (! $cart || $cart->items()->count() === 0) {
            return redirect()->route('store.cart.show');
        }

        if (! $this->settings->enable_guest_checkout && ! $request->user()) {
            return redirect()->route('login');
        }

        $currency = $this->currencies->resolve();

        return Inertia::render('Store/CheckoutStore', [
            'quote' => $this->carts->quote($cart, $request),
            'gateways' => $this->gateways->availableFor($currency->code)
                ->map(fn ($driver) => [
                    'key' => $driver->gateway()->value,
                    'label' => $driver->label(),
                    'description' => $driver->description(),
                ])->values(),
            'linkedPlayers' => $request->user()?->players->map->only(['id', 'uuid', 'username'])->values() ?? [],
            'requiresEmail' => ! $request->user() && $this->settings->require_email_on_guest_checkout,
            'termsText' => $this->settings->terms_text,
            'mojangVerification' => $this->settings->mojang_username_verification,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('browse', StorePackage::class);

        $requiresEmail = ! $request->user() && $this->settings->require_email_on_guest_checkout;

        $validated = $request->validate([
            'player_username' => 'required|string|max:16',
            'email' => ($requiresEmail ? 'required' : 'nullable').'|email|max:255',
            'gateway' => 'required|string',
            'accept_terms' => 'accepted',
        ]);

        if (! $this->settings->enable_guest_checkout && ! $request->user()) {
            return redirect()->route('login');
        }

        $cart = $this->carts->current($request, create: false);

        if (! $cart || $cart->items()->count() === 0) {
            return redirect()->route('store.cart.show');
        }

        $driver = $this->gateways->driver($validated['gateway']);
        $currency = $this->currencies->resolve();

        if (! $driver || ! $driver->isEnabled() || ! $this->gateways->availableFor($currency->code)->has($validated['gateway'])) {
            throw ValidationException::withMessages(['gateway' => __('That payment method is not available.')]);
        }

        $resolved = $this->players->resolve($validated['player_username'], $request->user());

        $order = $this->checkout->placeOrder($cart, [
            'email' => $validated['email'] ?? null,
            'gateway' => $validated['gateway'],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'country_id' => app(GeolocationService::class)->getCountryIdFromIP($request->ip()),
        ], $request->user(), $resolved);

        $payment = $order->getRelation('pendingPayment');
        $session = $driver->createPaymentSession($order, $payment);

        if ($session->sessionId) {
            $payment->update(['gateway_session_id' => $session->sessionId]);
        }

        // An order fully covered by a gift card or a 100% coupon has nothing to charge, so it
        // skips the gateway entirely rather than opening a zero-value checkout.
        if ((int) $order->amount_due === 0) {
            $this->orders->markPaid($order, $payment, 0, $order->currency);

            return redirect()->route('store.order.result', $order->uuid);
        }

        if ($session->redirectUrl) {
            // Inertia needs an explicit hard redirect to leave the SPA for a hosted checkout.
            return Inertia::location($session->redirectUrl);
        }

        return redirect()->route('store.order.result', $order->uuid);
    }

    /**
     * Where the buyer lands after checkout, whether or not a gateway was involved.
     */
    public function result(Request $request, StoreOrder $order)
    {
        $this->authorizeOrderView($request, $order);

        $order->load('items');

        // Gateways that capture on return (rather than purely by webhook) get their chance here.
        $payment = $order->payments()->latest('id')->first();
        if ($payment && $order->status->canTransitionTo(StoreOrderStatus::PAID)) {
            $driver = $this->gateways->driver($payment->gateway?->value);
            $event = $driver?->confirmOnReturn($payment);

            if ($event && $event->kind === StoreGatewayEventData::KIND_COMPLETED) {
                $this->orders->markPaid($order, $payment, $event->amountMinor, $event->currency, $event->transactionId);
                $order->refresh();
            }
        }

        return Inertia::render('Store/ResultStoreOrder', [
            'order' => $this->presentOrder($order),
        ]);
    }

    /**
     * Lightweight poll for the result page: delivery happens on a queue, so the page watches for
     * it rather than blocking the response.
     */
    public function status(Request $request, StoreOrder $order)
    {
        $this->authorizeOrderView($request, $order);

        return response()->json([
            'status' => $order->status->value,
            'delivery_status' => $order->delivery_status->value,
        ]);
    }

    public function cancel(Request $request, StoreOrder $order)
    {
        $this->authorizeOrderView($request, $order);

        $this->orders->cancel($order, __('Cancelled by the buyer at the payment page.'));

        return redirect()->route('store.cart.show')
            ->with(['toast' => ['type' => 'info', 'title' => __('Checkout cancelled')]]);
    }

    /**
     * A guest has no account to authorise against, so knowledge of the order's uuid is the
     * credential. It is a v4 uuid and never exposed in a listing.
     */
    private function authorizeOrderView(Request $request, StoreOrder $order): void
    {
        $this->authorize('browse', StorePackage::class);

        if ($order->user_id && $order->user_id !== $request->user()?->id) {
            abort_unless($request->user()?->can('read store_orders'), 403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrder(StoreOrder $order): array
    {
        return [
            'uuid' => $order->uuid,
            'status' => $order->status,
            'delivery_status' => $order->delivery_status,
            'player_username' => $order->player_username,
            'currency' => $order->currency,
            'total_formatted' => $this->currencies->format((int) $order->total, $order->currency),
            'amount_due_formatted' => $this->currencies->format((int) $order->amount_due, $order->currency),
            'created_at' => $order->created_at,
            'items' => $order->items->map(fn ($item) => [
                'package_name' => $item->package_name,
                'quantity' => $item->quantity,
                'total_formatted' => $this->currencies->format((int) $item->total, $order->currency),
                'options' => $item->options,
            ]),
        ];
    }
}
