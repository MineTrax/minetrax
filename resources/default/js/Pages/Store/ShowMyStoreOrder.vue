<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { Button } from "@/Components/ui/button";

const { __ } = useTranslations();
const { formatToDayDateString, formatToDateString, purifyText } = useHelpers();

defineProps({
    order: { type: Object, required: true },
    // Admin-authored rich text for an offline gateway, and null once there is nothing left to pay.
    // Sanitised before rendering: it reaches the page as markup.
    paymentInstructions: { type: String, default: null },
});
</script>

<template>
  <AppLayout>
    <AppHead :title="__('Order :number', { number: order.number })" />

    <div class="px-4 py-8 mx-auto max-w-3xl text-foreground">
      <Link
        class="text-sm text-muted-foreground hover:text-foreground"
        :href="route('store.my-order.index')"
      >
        &larr; {{ __("My Purchases") }}
      </Link>

      <div class="flex flex-wrap justify-between items-start gap-3 mt-4 mb-6">
        <div>
          <h1 class="text-2xl font-semibold font-mono mb-1">
            {{ order.number }}
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ formatToDayDateString(order.created_at) }} · {{ __("For") }} {{ order.player_username }}
          </p>
        </div>
        <div class="flex flex-col items-end gap-2">
          <div class="flex gap-2">
            <CommonStatusBadge :status="order.status.value" />
            <CommonStatusBadge :status="order.delivery_status.value" />
          </div>
          <!-- A plain anchor, not <Link>: the response is a PDF download, and Inertia would try to
               parse it as a page visit. -->
          <Button
            v-if="order.can_download_invoice"
            variant="outline"
            size="sm"
            as="a"
            :href="route('store.order.invoice', order.uuid)"
          >
            {{ __("Download Invoice") }}
          </Button>
        </div>
      </div>

      <!-- How to pay an offline order. Above the line items rather than below them: a buyer who
           opened this page from their purchase history came back to settle it, not to re-read
           what they bought. -->
      <div
        v-if="paymentInstructions"
        class="bg-card rounded-lg shadow p-6 mb-6"
      >
        <h2 class="text-sm font-medium mb-3">
          {{ __("How to pay") }}
        </h2>
        <div
          class="prose prose-sm dark:prose-invert max-w-none text-foreground/90 prose-headings:text-foreground prose-p:text-foreground/90 prose-strong:text-foreground prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-blockquote:border-primary/30 prose-blockquote:text-foreground/70 prose-code:text-primary prose-code:bg-muted prose-code:rounded prose-code:px-1 prose-pre:bg-muted prose-img:rounded-lg"
          v-html="purifyText(paymentInstructions)"
        />
      </div>

      <div class="bg-card rounded-lg shadow overflow-hidden">
        <ul class="divide-y divide-border">
          <li
            v-for="(item, index) in order.items"
            :key="index"
            class="px-6 py-4 flex justify-between gap-4"
          >
            <div>
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
                v-if="item.grant"
                class="inline-flex items-center gap-1 mt-2 text-xs text-muted-foreground"
              >
                <!-- An active grant is the good outcome here, not the red "active" a ban gets. -->
                <CommonStatusBadge
                  :status="item.grant.status.value"
                  :tone="item.grant.status.value === 'active' ? 'success' : null"
                />
                <template v-if="item.grant.expires_at">{{ __("until") }} {{ formatToDateString(item.grant.expires_at) }}</template>
                <template v-else-if="item.grant.status.value === 'active'">{{ __("permanent") }}</template>
              </span>
              <span
                v-if="item.gift_card"
                class="block mt-2 text-xs text-muted-foreground"
              >
                {{ __("Gift card code") }}:
                <code class="px-1 py-0.5 rounded bg-muted text-foreground font-mono select-all">{{ item.gift_card.code }}</code>
                <span class="ml-1">({{ item.gift_card.balance_formatted }})</span>
              </span>
            </div>
            <span class="whitespace-nowrap">{{ item.total_formatted }}</span>
          </li>
        </ul>

        <dl class="px-6 py-4 border-t border-border space-y-1 text-sm">
          <div class="flex justify-between">
            <dt class="text-muted-foreground">
              {{ __("Subtotal") }}
            </dt>
            <dd>{{ order.money.subtotal }}</dd>
          </div>
          <div
            v-if="order.raw.coupon_discount > 0"
            class="flex justify-between text-success"
          >
            <!-- Each code with what it was worth, not one combined figure: an order can carry
                 several, and the sum alone cannot say which of them did what. -->
            <dt>
              {{ __("Coupon") }}
              <span v-if="order.coupons.length">
                ({{ order.coupons.map((coupon) => coupon.code).join(", ") }})
              </span>
            </dt>
            <dd>-{{ order.money.coupon_discount }}</dd>
          </div>
          <div
            v-if="order.raw.tax_amount > 0"
            class="flex justify-between"
          >
            <dt class="text-muted-foreground">
              {{ __("Tax") }}
            </dt>
            <dd>{{ order.money.tax_amount }}</dd>
          </div>
          <div class="flex justify-between font-medium">
            <dt>{{ __("Total") }}</dt>
            <dd>{{ order.money.total }}</dd>
          </div>
          <div
            v-if="order.raw.gift_card_amount > 0"
            class="flex justify-between text-success"
          >
            <dt>{{ __("Gift Card") }}</dt>
            <dd>-{{ order.money.gift_card_amount }}</dd>
          </div>
          <div
            v-if="order.raw.gift_card_amount > 0"
            class="flex justify-between font-medium border-t border-border pt-2 mt-2"
          >
            <dt>{{ __("Paid") }}</dt>
            <dd>{{ order.money.amount_due }}</dd>
          </div>
        </dl>
      </div>

      <!-- Ahead of the summary buttons, and styled as the primary action: an unpaid order has one
           thing worth doing to it, and "View Delivery Status" does not read as it. -->
      <div
        v-if="order.is_resumable"
        class="flex flex-wrap items-center justify-between gap-3 mt-6 p-4 rounded-lg border border-border bg-orange-500/5"
      >
        <p class="text-sm text-muted-foreground">
          {{ __("This order is waiting for payment.") }}
        </p>
        <Button as-child>
          <Link :href="route('store.order.result', order.uuid)">
            {{ __("Pay :amount", { amount: order.money.amount_due }) }}
          </Link>
        </Button>
      </div>

      <div class="mt-6">
        <Button
          variant="outline"
          as-child
        >
          <Link :href="route('store.order.result', order.uuid)">
            {{ __("View Delivery Status") }}
          </Link>
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
