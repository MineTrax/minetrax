<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { Button } from "@/Components/ui/button";

const { __ } = useTranslations();

defineProps({
    order: { type: Object, required: true },
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
            {{ order.created_at }} · {{ __("For") }} {{ order.player_username }}
          </p>
        </div>
        <div class="flex gap-2">
          <CommonStatusBadge :status="order.status.value" />
          <CommonStatusBadge :status="order.delivery_status.value" />
        </div>
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
                v-for="option in item.options"
                :key="option.name"
                class="block text-xs text-muted-foreground"
              >{{ option.name }}: {{ option.label }}</span>
              <span
                v-if="item.grant"
                class="inline-flex items-center gap-1 mt-2 text-xs text-muted-foreground"
              >
                <CommonStatusBadge :status="item.grant.status.value" />
                <template v-if="item.grant.expires_at">{{ __("until") }} {{ item.grant.expires_at }}</template>
                <template v-else-if="item.grant.status.value === 'active'">{{ __("permanent") }}</template>
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
            <dt>{{ __("Coupon") }} <span v-if="order.coupon_code">({{ order.coupon_code }})</span></dt>
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
