<?php

namespace App\Http\Controllers\Store;

use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Services\GeolocationService;
use App\Services\StoreCartService;
use App\Services\StoreCheckoutService;
use App\Services\StoreCurrencyService;
use App\Services\StoreOrderService;
use App\Services\StorePlayerResolver;
use App\Services\StoreReferralService;
use App\Settings\StoreSettings;
use App\Utils\Helpers\Helper;
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
        private StoreReferralService $referrals,
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
            'requiresBillingAddress' => $this->settings->collect_billing_address,
            // Only shipped when the form will actually render the picker: the full country list is
            // a couple of hundred rows on every checkout that has no use for it.
            'countries' => $this->settings->collect_billing_address
                ? Country::orderBy('name')->get(['id', 'name'])
                : [],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('browse', StorePackage::class);

        $requiresEmail = ! $request->user() && $this->settings->require_email_on_guest_checkout;
        // The same fields for a guest and a member: an account holds no address, so being signed in
        // is not a reason to skip asking.
        $requiresAddress = $this->settings->collect_billing_address;
        $addressRule = $requiresAddress ? 'required' : 'nullable';

        $validated = $request->validate([
            'player_username' => 'required|string|max:16',
            'email' => ($requiresEmail ? 'required' : 'nullable').'|email|max:255',
            'gateway' => 'required|string',
            'accept_terms' => 'accepted',

            'billing_name' => $addressRule.'|string|max:255',
            'billing_address_line1' => $addressRule.'|string|max:255',
            // Line two is the flat number, so it stays optional even when the rest is not.
            'billing_address_line2' => 'nullable|string|max:255',
            'billing_city' => $addressRule.'|string|max:255',
            // Not every country has one, so this is never required.
            'billing_state' => 'nullable|string|max:255',
            'billing_postal_code' => $addressRule.'|string|max:32',
            'billing_country_id' => [$addressRule, 'integer', 'exists:countries,id'],
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

        $billingCountry = $requiresAddress
            ? Country::find($validated['billing_country_id'])
            : null;

        // Resolved here because this is the last point the referral cookie is in scope: the order
        // service and the delivery job both run long after the buyer's request is gone.
        $attribution = $this->referrals->resolveFor($request, $cart);

        $order = $this->checkout->placeOrder($cart, [
            'email' => $validated['email'] ?? null,
            'gateway' => $validated['gateway'],
            'referral' => $attribution['referral'],
            'referral_source' => $attribution['source'],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            // A country the buyer declared beats one guessed from their IP, and it is what the tax
            // rule is chosen by. Falls back to geolocation when no address was asked for.
            'country_id' => $billingCountry?->id
                ?? app(GeolocationService::class)->getCountryIdFromIP($request->ip()),
            'billing' => $requiresAddress ? [
                'billing_name' => $validated['billing_name'],
                'billing_address_line1' => $validated['billing_address_line1'],
                'billing_address_line2' => $validated['billing_address_line2'] ?? null,
                'billing_city' => $validated['billing_city'],
                'billing_state' => $validated['billing_state'] ?? null,
                'billing_postal_code' => $validated['billing_postal_code'],
                // Snapshotted alongside country_id, so a renamed or deleted country cannot rewrite
                // what an old invoice says.
                'billing_country' => $billingCountry?->name,
            ] : [],
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

        $order->load('items.giftCard', 'coupons');

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
            // Only while there is still something to pay: an offline gateway's "send us a bank
            // transfer" is noise once the money has landed, and worse than noise if it tempts a
            // buyer into paying twice.
            'paymentInstructions' => $order->isResumable()
                ? $this->gateways->driver($order->gateway?->value)?->paymentInstructions()
                : null,
            // Only while there is still something to pay. Listed against the order's own currency,
            // not the visitor's, because that is what will be charged.
            'gateways' => $order->isResumable()
                ? $this->gateways->availableFor($order->currency)
                    ->map(fn ($driver) => [
                        'key' => $driver->gateway()->value,
                        'label' => $driver->label(),
                        'description' => $driver->description(),
                        // An offline method has nowhere to send the buyer, so the page offers
                        // instructions instead of a button that would reload it and charge nothing.
                        'is_offline' => $driver->isOffline(),
                    ])->values()
                : collect(),
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
            // Per-command progress, so the page can say "2 of 3 sent" and, for a command waiting
            // on the player, "join the server" — which is something the buyer can actually do.
            'delivery' => $this->orders->deliverySummary($order),
        ]);
    }

    /**
     * Pick the payment back up, optionally by another route.
     *
     * A buyer who closes the gateway tab has no way back otherwise: the cart was emptied when the
     * order was placed, so "start again" means rebuilding the basket by hand.
     *
     * The one rule this has to keep is that an order never has two live sessions against it. Two
     * could each be captured, and markPaid() credits only the first — money taken with nothing to
     * show for it. So the existing session is reopened whenever the gateway still considers it
     * usable, and a new one is only opened once the old is dead or deliberately abandoned.
     */
    public function pay(Request $request, StoreOrder $order)
    {
        $this->authorizeOrderView($request, $order);

        $validated = $request->validate(['gateway' => 'nullable|string']);

        // Anything but PENDING has either been paid or been closed; neither wants another session.
        if ($order->status !== StoreOrderStatus::PENDING) {
            return redirect()->route('store.order.result', $order->uuid);
        }

        // Refused rather than allowed through: the sweeper cancels stale pending orders, and a
        // capture landing against an order it has just cancelled is money markPaid() cannot credit.
        if ($order->isPastPaymentWindow()) {
            return redirect()->route('store.order.result', $order->uuid)
                ->with(['toast' => ['type' => 'error', 'title' => __('This order has expired'), 'body' => __('Please place it again.')]]);
        }

        $payment = $order->payments()
            ->where('status', StorePaymentStatus::PENDING)
            ->latest('id')
            ->first();

        // The order's own currency, never the one the visitor happens to be browsing in — the
        // amount was fixed when the order was placed.
        $chosen = $validated['gateway'] ?? $payment?->gateway?->value ?? $order->gateway?->value;
        $driver = $this->gateways->driver($chosen);

        if (! $driver || ! $driver->isEnabled() || ! $this->gateways->availableFor($order->currency)->has($chosen)) {
            throw ValidationException::withMessages(['gateway' => __('That payment method is not available.')]);
        }

        $isSwitching = $payment && $payment->gateway?->value !== $chosen;

        if ($payment && ! $isSwitching) {
            $session = $driver->resumePaymentSession($payment);

            if ($session?->redirectUrl) {
                return Inertia::location($session->redirectUrl);
            }
        }

        if ($isSwitching) {
            // Close the old one before opening another, so the two can never both be paid. Stripe
            // can expire a session outright; PayPal cannot, and relies on markPaid()'s lock.
            $this->gateways->driver($payment->gateway?->value)?->abandonPaymentSession($payment);
            $this->orders->failPaymentAttempt($payment, __('Abandoned: the buyer chose a different payment method.'));
            $payment = null;
        }

        $payment ??= $this->orders->startPaymentAttempt($order, $chosen);

        // The buyer's current intent, so the pending screen names the method they are actually
        // using. markPaid() sets this again from whichever payment settles.
        $order->update(['gateway' => $chosen]);

        $session = $driver->createPaymentSession($order, $payment);

        if ($session->sessionId) {
            $payment->update(['gateway_session_id' => $session->sessionId]);
        }

        if ($session->redirectUrl) {
            return Inertia::location($session->redirectUrl);
        }

        // A gateway with no hosted page — the manual one — leaves the buyer here to follow whatever
        // instructions the admin configured.
        return redirect()->route('store.order.result', $order->uuid);
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
            'number' => strtoupper(substr($order->uuid, 0, 8)),
            // Hand-built arrays skip BaseModel::attributesToArray(), so enums are run through
            // the same {key, value} shape the rest of the frontend expects.
            'status' => Helper::enumKeyValue($order->status),
            'delivery_status' => Helper::enumKeyValue($order->delivery_status),
            // The same shape the poll returns, so the first paint can already say "waiting for you
            // to join" instead of a generic "delivering" that the first poll then corrects.
            'delivery' => $this->orders->deliverySummary($order),
            // For a guest this page is the only route to their invoice, so the button has to be here.
            'can_download_invoice' => $order->status->isInvoiceable(),
            // Preselects the picker on the pending screen with whatever they chose at checkout.
            'gateway' => $order->gateway?->value,
            'player_username' => $order->player_username,
            'currency' => $order->currency,
            'total_formatted' => $this->currencies->format((int) $order->total, $order->currency),
            'amount_due_formatted' => $this->currencies->format((int) $order->amount_due, $order->currency),
            'created_at' => $order->created_at,
            // The receipt used to show line totals and then a smaller grand total with nothing in
            // between, which reads as a pricing bug. Each figure between the two gets its own row.
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
            'tax_name' => $order->tax_name,
            'coupons' => $order->coupons->map(fn ($coupon) => ['code' => $coupon->code])->values(),
            'items' => $order->items->map(fn ($item) => [
                'package_name' => $item->package_name,
                'quantity' => $item->quantity,
                'total_formatted' => $this->currencies->format((int) $item->total, $order->currency),
                'variables' => $item->variable_values,
                // Present only for a package that sells store credit.
                'gift_card' => $item->giftCard ? [
                    'code' => $item->giftCard->code,
                    'balance_formatted' => $this->currencies->format(
                        (int) $item->giftCard->balance,
                        $item->giftCard->currency_code
                    ),
                ] : null,
            ]),
        ];
    }
}
