<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, router } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import { Button } from "@/Components/ui/button";
import StatusChip from "@/Shared/StatusChip.vue";
import {
    BanIcon,
    CheckIcon,
    CircleCheckIcon,
    CircleXIcon,
    ClockIcon,
    CopyIcon,
    CreditCardIcon,
    HourglassIcon,
    LoaderCircleIcon,
    LogInIcon,
    PackageCheckIcon,
    PartyPopperIcon,
    TriangleAlertIcon,
} from "lucide-vue-next";

const { __ } = useTranslations();
const { formatToDayDateString, purifyText } = useHelpers();

const props = defineProps({
    order: { type: Object, required: true },
    // Empty once there is nothing left to pay, which is what hides the resume block.
    gateways: { type: Array, default: () => [] },
    // Admin-authored rich text for an offline gateway, and null for everything else. Sanitised
    // before it is rendered: it reaches the page as markup, and an admin account is not a reason
    // to hand the buyer's browser unfiltered HTML.
    paymentInstructions: { type: String, default: null },
});

// Preselected with whatever they chose at checkout, so continuing is one click and switching is a
// deliberate act.
const chosenGateway = ref(props.order.gateway);
const paying = ref(false);

const canResume = computed(() => props.gateways.length > 0);
const isSwitching = computed(() => chosenGateway.value !== props.order.gateway);

// An offline method takes the money somewhere else, so resuming it would post the form, find no
// hosted page to redirect to, and land the buyer back on this exact screen having paid nothing.
// The instructions above are the call to action; the button only appears once they pick a method
// that actually has somewhere to send them.
const chosenIsOffline = computed(
    () => props.gateways.find((gateway) => gateway.key === chosenGateway.value)?.is_offline ?? false
);

// Nothing to switch to means nothing to show: a lone offline method needs no picker and no button.
const canPayOnline = computed(() => !chosenIsOffline.value);
const showResumeBlock = computed(() => canResume.value && (canPayOnline.value || props.gateways.length > 1));

const resumePayment = () => {
    paying.value = true;

    router.post(route("store.order.pay", props.order.uuid), { gateway: chosenGateway.value }, {
        onFinish: () => {
            paying.value = false;
        },
    });
};

// --- Live state -----------------------------------------------------------------------------
// Seeded from the props and then owned by the poll, so a change shows up without a page visit.
const status = ref(props.order.status.value);
const deliveryStatus = ref(props.order.delivery_status.value);
const delivery = ref(props.order.delivery ?? { total: 0, completed: 0, in_progress: 0, failed: 0, waiting_for_player: false });

const PAID_STATES = ["paid", "completed", "partially_refunded"];

const isPending = computed(() => status.value === "pending");
const isPaid = computed(() => PAID_STATES.includes(status.value));
const isCancelled = computed(() => ["cancelled", "refunded", "chargeback"].includes(status.value));

// One word for where delivery stands, resolved from the two statuses and the per-command summary.
// "paid" without "completed" means the fulfilment job has not run yet, which is a different wait
// from commands sitting on the queue, and a command deferred until the player logs in is a wait the
// buyer can end themselves — so it gets its own state rather than a generic "delivering".
const deliveryState = computed(() => {
    if (!isPaid.value) {
        return null;
    }

    if (status.value === "paid") {
        return "preparing";
    }

    if (deliveryStatus.value === "delivered") {
        return "delivered";
    }

    if (deliveryStatus.value === "partial") {
        return "partial";
    }

    if (deliveryStatus.value === "failed") {
        return "failed";
    }

    return delivery.value.waiting_for_player ? "waiting_for_player" : "delivering";
});

const isDeliveryInProgress = computed(() => ["preparing", "delivering", "waiting_for_player"].includes(deliveryState.value));

// Only worth saying when there is more than one thing to send.
const progressText = computed(() => {
    if (deliveryState.value !== "delivering" || delivery.value.total < 2) {
        return null;
    }

    return __(":sent of :total sent", { sent: delivery.value.completed, total: delivery.value.total });
});

const paymentChip = computed(() => {
    switch (status.value) {
    case "paid":
    case "completed":
        return { tone: "success", icon: CircleCheckIcon, label: __("Paid") };
    case "pending":
        return { tone: "warning", icon: ClockIcon, label: __("Awaiting payment") };
    case "partially_refunded":
        return { tone: "warning", icon: CreditCardIcon, label: __("Partly refunded") };
    case "refunded":
        return { tone: "danger", icon: CreditCardIcon, label: __("Refunded") };
    case "chargeback":
        return { tone: "danger", icon: TriangleAlertIcon, label: __("Disputed") };
    case "cancelled":
        return { tone: "muted", icon: BanIcon, label: __("Cancelled") };
    default:
        return { tone: "muted", icon: null, label: status.value };
    }
});

const deliveryChip = computed(() => {
    switch (deliveryState.value) {
    case "preparing":
        return { tone: "info", loading: true, label: __("Preparing") };
    case "delivering":
        return { tone: "info", loading: true, label: __("Delivering") };
    case "waiting_for_player":
        return { tone: "warning", icon: LogInIcon, label: __("Waiting for you to join") };
    case "delivered":
        return { tone: "success", icon: PackageCheckIcon, label: __("Delivered") };
    case "partial":
        return { tone: "warning", icon: TriangleAlertIcon, label: __("Partly delivered") };
    case "failed":
        return { tone: "danger", icon: CircleXIcon, label: __("Delivery failed") };
    default:
        return null;
    }
});

// The three things that happen to a paid order, in the order they happen. Each is done, active
// (something is happening right now), waiting (on the buyer), a problem, or not yet reached.
const steps = computed(() => {
    const deliveryStepState = {
        preparing: "upcoming",
        delivering: "active",
        waiting_for_player: "waiting",
        delivered: "done",
        partial: "warning",
        failed: "error",
    }[deliveryState.value] ?? "upcoming";

    return [
        { key: "paid", label: __("Payment received"), state: "done" },
        { key: "preparing", label: __("Preparing"), state: deliveryState.value === "preparing" ? "active" : "done" },
        { key: "delivery", label: __("Delivered"), state: deliveryStepState },
    ];
});

const stepIcon = (state) => ({
    done: CircleCheckIcon,
    active: LoaderCircleIcon,
    waiting: LogInIcon,
    warning: TriangleAlertIcon,
    error: CircleXIcon,
    upcoming: null,
}[state]);

const stepClass = (state) => ({
    done: "border-success bg-success/15 text-success",
    active: "border-primary bg-primary/10 text-primary",
    waiting: "border-amber-500 bg-amber-500/15 text-amber-700 dark:text-amber-300",
    warning: "border-amber-500 bg-amber-500/15 text-amber-700 dark:text-amber-300",
    error: "border-destructive bg-destructive/15 text-destructive",
    upcoming: "border-border bg-card text-muted-foreground",
}[state]);

// --- Order number ---------------------------------------------------------------------------
// The one thing a buyer pastes into a support ticket, so it is one click to copy.
const copied = ref(false);
let copiedTimer = null;

const copyOrderNumber = async () => {
    try {
        await navigator.clipboard?.writeText(props.order.number);
        copied.value = true;
        clearTimeout(copiedTimer);
        copiedTimer = setTimeout(() => {
            copied.value = false;
        }, 1500);
    } catch {
        // Clipboard access refused. The number is still on screen and selectable.
    }
};

// --- Polling --------------------------------------------------------------------------------
// Fulfilment runs on a queue, so the page watches for it rather than the response blocking on it.
// Quick at first, because most deliveries land within seconds, then backing off, and giving up
// after a few minutes with an honest message rather than spinning forever against a stalled worker.
// Paused while the tab is hidden: nobody is looking, and the first poll on return catches up.
const POLL_DELAYS_MS = [3000, 3000, 5000, 5000, 8000, 10000, 15000];
const POLL_BUDGET_MS = 5 * 60 * 1000;

const stalled = ref(false);
let timer = null;
let pollCount = 0;
let pollingSince = 0;

const shouldKeepPolling = () => {
    if (status.value === "pending" || status.value === "paid") {
        return true;
    }

    return PAID_STATES.includes(status.value) && deliveryStatus.value === "pending";
};

const stopPolling = () => {
    clearTimeout(timer);
    timer = null;
};

const schedule = () => {
    stopPolling();

    if (!shouldKeepPolling() || document.hidden) {
        return;
    }

    if (Date.now() - pollingSince > POLL_BUDGET_MS) {
        stalled.value = true;

        return;
    }

    const delay = POLL_DELAYS_MS[Math.min(pollCount, POLL_DELAYS_MS.length - 1)];
    timer = setTimeout(poll, delay);
};

const poll = async () => {
    pollCount++;

    try {
        const response = await fetch(route("store.order.status", props.order.uuid), {
            headers: { Accept: "application/json" },
        });

        if (!response.ok) {
            stopPolling();

            return;
        }

        const data = await response.json();
        const paymentSettled = status.value !== data.status && !["pending", "paid"].includes(data.status);

        status.value = data.status;
        deliveryStatus.value = data.delivery_status;
        delivery.value = data.delivery ?? delivery.value;

        if (paymentSettled) {
            // The invoice button, the receipt figures and the resume block all hang off the
            // server-rendered props, so pull those once the money side has settled.
            router.reload({ only: ["order", "gateways", "paymentInstructions"] });
        }

        schedule();
    } catch {
        stopPolling();
    }
};

const startPolling = () => {
    stalled.value = false;
    pollCount = 0;
    pollingSince = Date.now();
    schedule();
};

const onVisibilityChange = () => {
    if (document.hidden) {
        stopPolling();
    } else if (!stalled.value && shouldKeepPolling()) {
        // Catch up at once rather than after a full back-off delay.
        pollCount = 0;
        poll();
    }
};

onMounted(() => {
    document.addEventListener("visibilitychange", onVisibilityChange);

    if (shouldKeepPolling()) {
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
    clearTimeout(copiedTimer);
    document.removeEventListener("visibilitychange", onVisibilityChange);
});
</script>

<template>
  <AppLayout>
    <AppHead :title="__('Order :number', { number: order.number })" />

    <div class="px-4 py-12 mx-auto max-w-2xl text-foreground">
      <div class="bg-card rounded-lg shadow p-8 text-center">
        <!-- Paid -->
        <template v-if="isPaid">
          <div
            class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full"
            :class="{
              'bg-success/15 text-success': deliveryState === 'delivered',
              'bg-amber-500/15 text-amber-600 dark:text-amber-300': deliveryState === 'partial',
              'bg-destructive/15 text-destructive': deliveryState === 'failed',
              'bg-primary/10 text-primary': isDeliveryInProgress,
            }"
          >
            <CircleCheckIcon
              v-if="deliveryState === 'delivered'"
              class="h-8 w-8"
              aria-hidden="true"
            />
            <TriangleAlertIcon
              v-else-if="deliveryState === 'partial' || deliveryState === 'failed'"
              class="h-8 w-8"
              aria-hidden="true"
            />
            <PartyPopperIcon
              v-else
              class="h-8 w-8"
              aria-hidden="true"
            />
          </div>

          <h1 class="text-2xl font-semibold mb-2">
            {{ __("Thank you!") }}
          </h1>
          <p class="text-muted-foreground">
            {{ __("Your payment went through and your items are on their way to :player.", { player: order.player_username }) }}
          </p>

          <!-- Where delivery stands, as three steps rather than two unlabelled pills. -->
          <ol class="mt-8 flex items-start justify-center">
            <li
              v-for="(step, index) in steps"
              :key="step.key"
              class="flex flex-1 max-w-40 flex-col items-center relative"
            >
              <!-- Connector to the previous step, coloured once that step is behind us. -->
              <span
                v-if="index > 0"
                class="absolute top-4 right-1/2 h-0.5 w-full -translate-y-1/2"
                :class="steps[index - 1].state === 'done' ? 'bg-success/60' : 'bg-border'"
                aria-hidden="true"
              />
              <span
                class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full border-2"
                :class="stepClass(step.state)"
              >
                <component
                  :is="stepIcon(step.state)"
                  v-if="stepIcon(step.state)"
                  class="h-4 w-4"
                  :class="{ 'animate-spin': step.state === 'active' }"
                  aria-hidden="true"
                />
                <span
                  v-else
                  class="h-2 w-2 rounded-full bg-current opacity-50"
                />
              </span>
              <span
                class="mt-2 text-xs font-medium"
                :class="step.state === 'upcoming' ? 'text-muted-foreground' : 'text-foreground'"
              >
                {{ step.label }}
              </span>
            </li>
          </ol>

          <!-- The sentence under the steps says what is happening now and, where there is one,
               what the buyer can do about it. -->
          <div class="mt-6 text-sm">
            <p
              v-if="deliveryState === 'preparing'"
              class="text-muted-foreground inline-flex items-center gap-2"
            >
              <LoaderCircleIcon
                class="h-4 w-4 animate-spin"
                aria-hidden="true"
              />
              {{ __("Getting your items ready…") }}
            </p>
            <p
              v-else-if="deliveryState === 'delivering'"
              class="text-muted-foreground inline-flex items-center gap-2"
            >
              <LoaderCircleIcon
                class="h-4 w-4 animate-spin"
                aria-hidden="true"
              />
              {{ __("Delivering now…") }}
              <span
                v-if="progressText"
                class="text-xs"
              >{{ progressText }}</span>
            </p>
            <p
              v-else-if="deliveryState === 'waiting_for_player'"
              class="text-amber-700 dark:text-amber-300"
            >
              {{ __("Join the server as :player to receive your items. They will be handed over the moment you are online.", { player: order.player_username }) }}
            </p>
            <p
              v-else-if="deliveryState === 'delivered'"
              class="text-success"
            >
              {{ __("Everything has been sent to the server. If you were offline, it will arrive the moment you next join.") }}
            </p>
            <p
              v-else-if="deliveryState === 'partial'"
              class="text-amber-700 dark:text-amber-300"
            >
              {{ __("Some items are still on their way. Nothing is lost — staff can see this order and will make sure it lands.") }}
            </p>
            <p
              v-else
              class="text-destructive"
            >
              {{ __("We could not reach the server to deliver this. Staff have been notified and will sort it out.") }}
            </p>

            <!-- The poll has given up. Better to say so than to spin forever against a worker
                 that may be down; the order is safe either way. -->
            <p
              v-if="stalled && isDeliveryInProgress"
              class="mt-3 text-xs text-muted-foreground"
            >
              {{ __("This is taking longer than usual. You can safely leave this page: your items will arrive on their own, and you can check back here or in My Purchases at any time.") }}
              <button
                type="button"
                class="ml-1 underline hover:text-foreground"
                @click="startPolling"
              >
                {{ __("Check again") }}
              </button>
            </p>
          </div>
        </template>

        <!-- Awaiting payment -->
        <template v-else-if="isPending">
          <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-300">
            <HourglassIcon
              class="h-8 w-8"
              aria-hidden="true"
            />
          </div>
          <h1 class="text-2xl font-semibold mb-2">
            {{ __("Waiting for payment") }}
          </h1>
          <p class="text-muted-foreground">
            {{ __("We have not received your payment yet. If you have just paid, this page will update by itself in a moment.") }}
            <button
              v-if="stalled"
              type="button"
              class="underline hover:text-foreground"
              @click="startPolling"
            >
              {{ __("Check again") }}
            </button>
          </p>

          <!-- The figure was already on the wire and never rendered, so the screen asked for a
               payment without naming its amount and the button said "Continue" against nothing. -->
          <p class="mt-4 text-sm text-muted-foreground">
            {{ __("Amount due") }}
            <span class="ml-1 text-2xl font-bold text-foreground align-middle">{{ order.amount_due_formatted }}</span>
          </p>

          <!-- How to actually pay, for a gateway that takes the money somewhere else. Left-aligned
               against the centred column above it because this is the one block on the page a
               buyer has to read carefully and copy from. -->
          <div
            v-if="paymentInstructions"
            class="mt-6 pt-6 border-t border-border text-left"
          >
            <h2 class="text-sm font-medium mb-3">
              {{ __("How to pay") }}
            </h2>
            <div
              class="prose prose-sm dark:prose-invert max-w-none text-foreground/90 prose-headings:text-foreground prose-p:text-foreground/90 prose-strong:text-foreground prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-blockquote:border-primary/30 prose-blockquote:text-foreground/70 prose-code:text-primary prose-code:bg-muted prose-code:rounded prose-code:px-1 prose-pre:bg-muted prose-img:rounded-lg"
              v-html="purifyText(paymentInstructions)"
            />
          </div>

          <!-- The way back in. Without this the buyer's only options are to abandon the order or
               rebuild the whole basket, because the cart was emptied when the order was placed. -->
          <div
            v-if="showResumeBlock"
            class="mt-6 pt-6 border-t border-border text-left"
          >
            <h2 class="text-sm font-medium text-center mb-3">
              {{ gateways.length > 1 ? __("Pay another way") : __("Pay for this order") }}
            </h2>

            <div
              v-if="gateways.length > 1"
              class="space-y-2 mb-4"
            >
              <label
                v-for="gateway in gateways"
                :key="gateway.key"
                class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                :class="chosenGateway === gateway.key ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted'"
              >
                <input
                  v-model="chosenGateway"
                  type="radio"
                  name="resume_gateway"
                  :value="gateway.key"
                  class="mt-1"
                >
                <span>
                  <span class="block text-sm font-medium">{{ gateway.label }}</span>
                  <span
                    v-if="gateway.description"
                    class="block text-xs text-muted-foreground"
                  >{{ gateway.description }}</span>
                </span>
              </label>
            </div>

            <template v-if="canPayOnline">
              <Button
                class="w-full"
                :disabled="paying"
                @click="resumePayment"
              >
                {{ isSwitching
                  ? __("Pay :amount with this method instead", { amount: order.amount_due_formatted })
                  : __("Pay :amount now", { amount: order.amount_due_formatted }) }}
              </Button>

              <p class="text-xs text-muted-foreground mt-2 text-center">
                {{ __("You will be taken back to the same checkout you left. Nothing is charged twice.") }}
              </p>
            </template>

            <!-- The offline branch. No button, because there is no page to send them to: the
                 order simply waits for the transfer to arrive and for staff to confirm it. -->
            <p
              v-else
              class="text-xs text-muted-foreground text-center"
            >
              {{ __("Follow the instructions above. Your order is held until a staff member confirms the payment.") }}
            </p>
          </div>
        </template>

        <!-- Cancelled or refunded -->
        <template v-else>
          <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-muted text-muted-foreground">
            <CircleXIcon
              class="h-8 w-8"
              aria-hidden="true"
            />
          </div>
          <h1 class="text-2xl font-semibold mb-2">
            {{ __("This order is no longer active") }}
          </h1>
          <p class="text-muted-foreground">
            {{ __("Nothing has been charged, or the charge has been returned to you.") }}
          </p>
        </template>

        <div class="flex flex-wrap items-center justify-center gap-2 mt-6">
          <StatusChip
            :tone="paymentChip.tone"
            :icon="paymentChip.icon"
            :label="paymentChip.label"
          />
          <StatusChip
            v-if="deliveryChip"
            :tone="deliveryChip.tone"
            :icon="deliveryChip.icon"
            :loading="deliveryChip.loading"
            :label="deliveryChip.label"
          />
        </div>
      </div>

      <!-- Receipt -->
      <div class="bg-card rounded-lg shadow mt-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex justify-between items-center gap-3">
          <h2 class="text-sm font-medium inline-flex items-center gap-2">
            {{ __("Order") }}
            <span class="font-mono select-all">{{ order.number }}</span>
            <button
              type="button"
              class="inline-flex items-center text-muted-foreground hover:text-foreground"
              :title="__('Copy order number')"
              :aria-label="__('Copy order number')"
              @click="copyOrderNumber"
            >
              <CheckIcon
                v-if="copied"
                class="h-3.5 w-3.5 text-success"
                aria-hidden="true"
              />
              <CopyIcon
                v-else
                class="h-3.5 w-3.5"
                aria-hidden="true"
              />
            </button>
          </h2>
          <span class="text-xs text-muted-foreground">{{ formatToDayDateString(order.created_at) }}</span>
        </div>

        <ul class="divide-y divide-border">
          <li
            v-for="(item, index) in order.items"
            :key="index"
            class="px-6 py-4 flex justify-between gap-4 text-sm"
          >
            <span>
              <span class="block font-medium">{{ item.package_name }}</span>
              <span class="block text-xs text-muted-foreground">&times;{{ item.quantity }}</span>
              <span
                v-for="variable in item.variables || []"
                :key="variable.identifier"
                class="block text-xs text-muted-foreground"
              >
                {{ variable.name }}: <span class="text-foreground">{{ variable.value }}</span>
              </span>
              <span
                v-if="item.gift_card"
                class="block mt-2 text-xs text-muted-foreground"
              >
                {{ __("Gift card code") }}:
                <code class="px-1 py-0.5 rounded bg-muted text-foreground font-mono select-all">{{ item.gift_card.code }}</code>
                <span class="ml-1">({{ item.gift_card.balance_formatted }})</span>
              </span>
            </span>
            <span class="whitespace-nowrap">{{ item.total_formatted }}</span>
          </li>
        </ul>

        <!-- Every figure between the line totals and what was charged. Without these the receipt
             showed items adding to one number and a total that was another, which reads as a bug. -->
        <dl class="px-6 py-4 border-t border-border space-y-1 text-sm">
          <div
            v-if="order.raw && (order.raw.coupon_discount > 0 || order.raw.tax_amount > 0 || order.raw.gift_card_amount > 0)"
            class="flex justify-between"
          >
            <dt class="text-muted-foreground">
              {{ __("Subtotal") }}
            </dt>
            <dd>{{ order.money.subtotal }}</dd>
          </div>
          <div
            v-if="order.raw?.coupon_discount > 0"
            class="flex justify-between text-success"
          >
            <dt>
              {{ __("Coupon") }}
              <span v-if="order.coupons?.length">
                ({{ order.coupons.map((coupon) => coupon.code).join(", ") }})
              </span>
            </dt>
            <dd>-{{ order.money.coupon_discount }}</dd>
          </div>
          <div
            v-if="order.raw?.tax_amount > 0"
            class="flex justify-between"
          >
            <dt class="text-muted-foreground">
              {{ order.tax_name || __("Tax") }}
            </dt>
            <dd>{{ order.money.tax_amount }}</dd>
          </div>
          <div class="flex justify-between font-medium">
            <dt>{{ __("Total") }}</dt>
            <dd>{{ order.total_formatted }}</dd>
          </div>
          <div
            v-if="order.raw?.gift_card_amount > 0"
            class="flex justify-between text-success"
          >
            <dt>{{ __("Gift Card") }}</dt>
            <dd>-{{ order.money.gift_card_amount }}</dd>
          </div>
          <div
            v-if="order.raw?.gift_card_amount > 0"
            class="flex justify-between font-medium border-t border-border pt-2 mt-2"
          >
            <dt>{{ isPaid ? __("Paid") : __("Amount due") }}</dt>
            <dd>{{ order.amount_due_formatted }}</dd>
          </div>
        </dl>
      </div>

      <div class="flex flex-wrap justify-center gap-3 mt-6">
        <Button
          variant="outline"
          as-child
        >
          <Link :href="route('store.index')">
            {{ __("Back to Store") }}
          </Link>
        </Button>
        <!-- A plain anchor, not <Link>: the response is a PDF download, and Inertia would try to
             parse it as a page visit. For a guest this page is the only route to their invoice. -->
        <Button
          v-if="order.can_download_invoice"
          variant="outline"
          as="a"
          :href="route('store.order.invoice', order.uuid)"
        >
          {{ __("Download Invoice") }}
        </Button>
        <Button
          v-if="isPending && !isCancelled"
          variant="outline"
          class="text-destructive hover:text-destructive"
          @click="router.post(route('store.order.cancel', order.uuid))"
        >
          {{ __("Cancel Order") }}
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
