<script setup>
import { computed, ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import AlertCard from "@/Components/AlertCard.vue";
import { Button } from "@/Components/ui/button";
import XInput from "@/Components/Form/XInput.vue";
import XSwitch from "@/Components/Form/XSwitch.vue";
import { Link, useForm, router } from "@inertiajs/vue3";

const { __ } = useTranslations();
const { formatToDayDateString } = useHelpers();

const props = defineProps({
    order: Object,
    money: Object,
    stuckDeliveries: Array,
    canRefundAtGateway: Boolean,
    orderPermissions: Object,
    timeline: Array,
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Store Orders"), url: route("admin.store.order.index"), current: false },
    { text: props.order.uuid.substring(0, 8).toUpperCase(), current: true },
];

// The address as the buyer gave it, one line per part and empties dropped — an optional flat
// number or a country with no states must not leave a blank line in the middle of the block.
const billingAddress = computed(() => [
    props.order.billing_name,
    props.order.billing_address_line1,
    props.order.billing_address_line2,
    [props.order.billing_city, props.order.billing_state].filter(Boolean).join(", "),
    props.order.billing_postal_code,
    props.order.billing_country,
].filter(Boolean).join("\n"));

const status = computed(() => props.order.status.value);
const isPending = computed(() => status.value === "pending");
const isPaidState = computed(() => ["paid", "completed", "partially_refunded"].includes(status.value));
const isTerminal = computed(() => ["cancelled", "refunded", "chargeback"].includes(status.value));

const completedPayment = computed(() =>
    (props.order.payments ?? []).find((p) => p.status.value === "completed"),
);

const refundableRemaining = computed(() => {
    const payment = completedPayment.value;

    return payment ? Number(payment.amount) - Number(payment.refunded_amount) : 0;
});

const showRefund = ref(false);

const refundForm = useForm({
    amount: 0,
    reason: "",
    at_gateway: false,
});

const openRefund = () => {
    refundForm.amount = refundableRemaining.value;
    refundForm.at_gateway = props.canRefundAtGateway;
    showRefund.value = true;
};

const submitRefund = () => {
    refundForm.post(route("admin.store.order.refund", props.order.uuid), {
        preserveScroll: true,
        onSuccess: () => {
            showRefund.value = false;
        },
    });
};

const markPaid = () =>
    router.post(route("admin.store.order.mark-paid", props.order.uuid), {}, { preserveScroll: true });

const cancelOrder = () =>
    router.post(route("admin.store.order.cancel", props.order.uuid), {}, { preserveScroll: true });

const resend = (includeUnfinished = false) =>
    router.post(
        route("admin.store.order.resend", props.order.uuid),
        { include_unfinished: includeUnfinished },
        { preserveScroll: true },
    );

// The delivery row itself carries no status: health is always read from the joined command queue,
// so there is one source of truth for whether a command actually ran.
const deliveryStatus = (delivery) => delivery.command_queue?.status?.value ?? "unknown";

// Timestamps arrive as ISO strings. Rendering them raw put "2026-07-29T18:29:32.000000Z" on the page.
const formatDate = (value) => (value ? formatToDayDateString(value) : "—");
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Order :number', { number: order.uuid.substring(0, 8).toUpperCase() })" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <AlertCard
        v-if="stuckDeliveries.length"
        variant="warning"
        class="mb-6"
      >
        {{ __(":count delivery/deliveries have been waiting for this player to come online for a while. They are not lost — they will run the moment the player joins.", { count: stuckDeliveries.length }) }}
      </AlertCard>

      <!-- Summary -->
      <div class="bg-card rounded-lg shadow p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <h2 class="text-lg font-semibold font-mono">
                {{ order.uuid.substring(0, 8).toUpperCase() }}
              </h2>
              <CommonStatusBadge :status="status" />
              <CommonStatusBadge :status="order.delivery_status.value" />
            </div>
            <p class="text-sm text-muted-foreground">
              {{ __("Placed") }} {{ formatDate(order.created_at) }}
              <span v-if="order.gateway"> · {{ order.gateway.value }}</span>
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <Button
              v-if="orderPermissions.update && isPending"
              @click="markPaid"
            >
              {{ __("Mark as Paid") }}
            </Button>
            <Button
              v-if="orderPermissions.resend && isPaidState"
              variant="outline"
              @click="resend(false)"
            >
              {{ __("Re-send Failed Commands") }}
            </Button>
            <Button
              v-if="orderPermissions.refund && isPaidState && refundableRemaining > 0"
              variant="outline"
              @click="openRefund"
            >
              {{ __("Refund") }}
            </Button>
            <!-- A plain anchor, not <Link>: the response is a PDF download, and Inertia would try
                 to parse it as a page visit. -->
            <Button
              v-if="order.can_download_invoice"
              variant="outline"
              as="a"
              :href="route('store.order.invoice', order.uuid)"
            >
              {{ __("Invoice") }}
            </Button>
            <Button
              v-if="orderPermissions.update && !isTerminal"
              variant="outline"
              class="text-destructive hover:text-destructive"
              @click="cancelOrder"
            >
              {{ __("Cancel Order") }}
            </Button>
          </div>
        </div>
      </div>

      <!-- Refund form -->
      <div
        v-if="showRefund"
        class="bg-card rounded-lg shadow p-6 mb-6"
      >
        <h3 class="text-sm font-medium mb-4">
          {{ __("Issue a Refund") }}
        </h3>

        <form
          class="space-y-4"
          @submit.prevent="submitRefund"
        >
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <XInput
              id="refund_amount"
              v-model="refundForm.amount"
              :label="__('Amount (minor units)')"
              :error="refundForm.errors.amount"
              :help="__('Whole units of :currency times 10^exponent. Full remaining amount is pre-filled.', { currency: order.currency })"
              type="number"
              name="refund_amount"
            />
            <XInput
              id="refund_reason"
              v-model="refundForm.reason"
              :label="__('Reason')"
              :error="refundForm.errors.reason"
              type="text"
              name="refund_reason"
            />
          </div>

          <XSwitch
            id="at_gateway"
            v-model="refundForm.at_gateway"
            :label="__('Also refund the money at the payment gateway')"
            :help="canRefundAtGateway
              ? __('Leave this off to record a refund that was already issued elsewhere.')
              : __('This gateway has no automated refund API. Move the money yourself, then record it here.')"
            :disabled="!canRefundAtGateway"
            name="at_gateway"
          />

          <div class="flex justify-end gap-2">
            <Button
              type="button"
              variant="outline"
              @click="showRefund = false"
            >
              {{ __("Cancel") }}
            </Button>
            <Button
              type="submit"
              :disabled="refundForm.processing"
            >
              {{ __("Confirm Refund") }}
            </Button>
          </div>
        </form>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Items and totals -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-card rounded-lg shadow overflow-hidden">
            <h3 class="text-sm font-medium px-6 py-4 border-b border-border">
              {{ __("Items") }}
            </h3>
            <table class="min-w-full divide-y divide-border">
              <tbody class="divide-y divide-border">
                <tr
                  v-for="item in order.items"
                  :key="item.id"
                >
                  <td class="px-6 py-4">
                    <div class="font-medium">
                      {{ item.package_name }}
                    </div>
                    <div
                      v-if="item.sale_name"
                      class="text-xs text-success"
                    >
                      {{ item.sale_name }}
                    </div>
                    <div
                      v-for="variable in item.variable_values || []"
                      :key="variable.identifier"
                      class="text-xs text-muted-foreground"
                    >
                      {{ variable.name }}: <span class="text-foreground">{{ variable.value }}</span>
                    </div>
                    <div
                      v-if="item.grant"
                      class="text-xs text-muted-foreground mt-1"
                    >
                      {{ __("Grant") }}:
                      <CommonStatusBadge
                        :status="item.grant.status.value"
                        :tone="item.grant.status.value === 'active' ? 'success' : null"
                      />
                      <span v-if="item.grant.expires_at"> · {{ __("expires") }} {{ formatDate(item.grant.expires_at) }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center whitespace-nowrap">
                    &times;{{ item.quantity }}
                  </td>
                  <td class="px-6 py-4 text-right whitespace-nowrap">
                    <div>{{ item.total_formatted }}</div>
                    <div
                      v-if="item.quantity > 1"
                      class="text-xs text-muted-foreground"
                    >
                      {{ item.unit_price_formatted }} {{ __("each") }}
                    </div>
                    <div
                      v-if="item.upgrade_credit_formatted"
                      class="text-xs text-success"
                    >
                      {{ __("Upgrade credit") }} -{{ item.upgrade_credit_formatted }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <dl class="px-6 py-4 border-t border-border space-y-1 text-sm">
              <div class="flex justify-between">
                <dt class="text-muted-foreground">
                  {{ __("Subtotal") }}
                </dt>
                <dd>{{ money.subtotal }}</dd>
              </div>
              <div
                v-if="order.coupon_discount > 0"
                class="flex justify-between text-success"
              >
                <dt>{{ __("Coupon") }}</dt>
                <dd>-{{ money.coupon_discount }}</dd>
              </div>
              <!-- Broken out per code once there is more than one. The combined figure above is
                   what was charged; this is the only place that says which code was worth what. -->
              <div
                v-for="coupon in order.coupons"
                :key="coupon.id"
                class="flex justify-between text-xs text-muted-foreground pl-3"
              >
                <dt>{{ coupon.code }}</dt>
                <dd>-{{ coupon.discount_amount_formatted }}</dd>
              </div>
              <div
                v-if="order.tax_amount > 0"
                class="flex justify-between"
              >
                <dt class="text-muted-foreground">
                  {{ __("Tax") }}
                </dt>
                <dd>{{ money.tax_amount }}</dd>
              </div>
              <div class="flex justify-between font-medium">
                <dt>{{ __("Total") }}</dt>
                <dd>{{ money.total }}</dd>
              </div>
              <div
                v-if="order.gift_card_amount > 0"
                class="flex justify-between text-success"
              >
                <dt>{{ __("Gift Card") }}</dt>
                <dd>-{{ money.gift_card_amount }}</dd>
              </div>
              <div class="flex justify-between font-medium">
                <dt>{{ __("Charged") }}</dt>
                <dd>{{ money.amount_due }}</dd>
              </div>
              <div
                v-if="order.currency !== order.base_currency"
                class="flex justify-between text-xs text-muted-foreground pt-1"
              >
                <dt>{{ __("Recorded as") }} ({{ __("rate") }} {{ order.exchange_rate }})</dt>
                <dd>{{ money.base_total }}</dd>
              </div>
            </dl>
          </div>

          <!-- Deliveries -->
          <div class="bg-card rounded-lg shadow overflow-hidden">
            <h3 class="text-sm font-medium px-6 py-4 border-b border-border">
              {{ __("In-Game Delivery") }}
            </h3>

            <p
              v-if="!order.deliveries.length"
              class="px-6 py-4 text-sm text-muted-foreground"
            >
              <!-- Three different situations used to read identically here, which is no help at all
                   when an order looks unfulfilled: not paid yet, paid and waiting on the queue
                   worker, or paid with genuinely nothing to run. -->
              {{ isPaidState
                ? __("Nothing queued yet. Check that a queue worker is running, then use Re-send.")
                : __("Nothing is delivered until the payment is confirmed.") }}
            </p>

            <div class="overflow-x-auto">
              <table
                v-if="order.deliveries.length"
                class="min-w-full divide-y divide-border text-sm"
              >
                <thead class="bg-muted">
                  <tr>
                    <th class="px-6 py-2 text-left text-xs font-medium text-muted-foreground uppercase">
                      {{ __("Command") }}
                    </th>
                    <th class="px-6 py-2 text-left text-xs font-medium text-muted-foreground uppercase">
                      {{ __("Server") }}
                    </th>
                    <th class="px-6 py-2 text-left text-xs font-medium text-muted-foreground uppercase">
                      {{ __("Status") }}
                    </th>
                    <th class="px-6 py-2 text-right text-xs font-medium text-muted-foreground uppercase">
                      {{ __("Attempts") }}
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-border">
                  <tr
                    v-for="delivery in order.deliveries"
                    :key="delivery.id"
                  >
                    <td class="px-6 py-3">
                      <code class="text-xs break-all">{{ delivery.parsed_command }}</code>
                      <div class="text-xs text-muted-foreground">
                        {{ delivery.trigger.value }}
                      </div>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                      {{ delivery.server?.name ?? "—" }}
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                      <CommonStatusBadge :status="deliveryStatus(delivery)" />
                    </td>
                    <td class="px-6 py-3 text-right whitespace-nowrap text-muted-foreground">
                      {{ delivery.command_queue?.attempts ?? 0 }} / {{ delivery.command_queue?.max_attempts ?? "—" }}
                      <span
                        v-if="delivery.redispatch_count > 0"
                        class="text-xs"
                      >({{ __("re-sent :count", { count: delivery.redispatch_count }) }})</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Buyer and payments -->
        <div class="space-y-6">
          <div class="bg-card rounded-lg shadow p-6">
            <h3 class="text-sm font-medium mb-4">
              {{ __("Buyer") }}
            </h3>
            <dl class="space-y-2 text-sm">
              <div>
                <dt class="text-muted-foreground text-xs">
                  {{ __("Minecraft Account") }}
                </dt>
                <dd class="font-medium">
                  {{ order.player_username }}
                </dd>
                <dd class="text-xs text-muted-foreground font-mono break-all">
                  {{ order.player_uuid }}
                </dd>
              </div>
              <div>
                <dt class="text-muted-foreground text-xs">
                  {{ __("Website Account") }}
                </dt>
                <dd>
                  <Link
                    v-if="order.user"
                    class="text-primary hover:underline"
                    :href="route('user.public.get', order.user.username)"
                  >
                    @{{ order.user.username }}
                  </Link>
                  <span v-else>{{ __("Guest") }}</span>
                </dd>
              </div>
              <div v-if="order.email">
                <dt class="text-muted-foreground text-xs">
                  {{ __("Email") }}
                </dt>
                <dd class="break-all">
                  {{ order.email }}
                </dd>
              </div>
              <div v-if="order.ip_address">
                <dt class="text-muted-foreground text-xs">
                  {{ __("IP") }}
                </dt>
                <dd>{{ order.ip_address }} <span v-if="order.country">({{ order.country.name }})</span></dd>
              </div>
              <!-- Only when one was collected: the setting is off for most stores, and an empty
                   heading reads as missing data rather than as a question never asked. -->
              <div v-if="order.billing_address_line1">
                <dt class="text-muted-foreground text-xs">
                  {{ __("Billing Address") }}
                </dt>
                <dd class="whitespace-pre-line">
                  {{ billingAddress }}
                </dd>
              </div>
            </dl>
          </div>

          <div class="bg-card rounded-lg shadow p-6">
            <h3 class="text-sm font-medium mb-4">
              {{ __("Payments") }}
            </h3>

            <p
              v-if="!order.payments.length"
              class="text-sm text-muted-foreground"
            >
              {{ __("No payment attempts yet.") }}
            </p>

            <div
              v-for="payment in order.payments"
              :key="payment.id"
              class="text-sm border-b border-border last:border-0 py-2 first:pt-0"
            >
              <div class="flex justify-between items-center">
                <span>{{ payment.gateway.value }} · {{ payment.amount_formatted }}</span>
                <CommonStatusBadge :status="payment.status.value" />
              </div>
              <div
                v-if="payment.gateway_transaction_id"
                class="text-xs text-muted-foreground font-mono break-all mt-1"
              >
                {{ payment.gateway_transaction_id }}
              </div>
              <div
                v-if="payment.failure_reason"
                class="text-xs text-destructive mt-1"
              >
                {{ payment.failure_reason }}
              </div>
              <div
                v-for="refund in payment.refunds ?? []"
                :key="refund.id"
                class="text-xs text-muted-foreground mt-1"
              >
                {{ refund.type.value }}: {{ refund.amount_formatted }}
                <span v-if="refund.reason"> — {{ refund.reason }}</span>
              </div>
            </div>
          </div>

          <div class="bg-card rounded-lg shadow p-6">
            <h3 class="text-sm font-medium mb-4">
              {{ __("History") }}
            </h3>

            <ol class="relative border-l border-border ml-2 space-y-4">
              <li
                v-for="(entry, index) in timeline"
                :key="index"
                class="ml-4"
              >
                <span
                  class="absolute w-2 h-2 rounded-full -left-1 mt-1.5"
                  :class="['chargeback', 'payment_failed', 'delivery_failed'].includes(entry.event)
                    ? 'bg-destructive'
                    : (entry.event === 'delivery_partial'
                      ? 'bg-amber-500'
                      : (['paid', 'completed', 'delivery_delivered'].includes(entry.event) ? 'bg-success' : 'bg-muted-foreground'))"
                />
                <div class="text-sm font-medium">
                  {{ entry.description }}
                </div>
                <div
                  v-if="entry.detail"
                  class="text-xs text-muted-foreground"
                >
                  {{ entry.detail }}
                </div>
                <div class="text-xs text-muted-foreground">
                  <span>{{ formatToDayDateString(entry.at) }}</span>
                  <!-- No causer means nobody did it: a gateway webhook, or a scheduled sweep. -->
                  <span v-if="entry.causer"> · {{ entry.causer.username ?? entry.causer.name }}</span>
                  <span v-else> · {{ __("system") }}</span>
                </div>
              </li>
            </ol>
          </div>

          <div
            v-if="order.notes"
            class="bg-card rounded-lg shadow p-6"
          >
            <h3 class="text-sm font-medium mb-2">
              {{ __("Notes") }}
            </h3>
            <p class="text-sm whitespace-pre-line text-muted-foreground">
              {{ order.notes }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
