<script setup>
import { computed, onUnmounted, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, router } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { Button } from "@/Components/ui/button";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";

const { __ } = useTranslations();

const props = defineProps({
    order: { type: Object, required: true },
});

const status = ref(props.order.status.value);
const deliveryStatus = ref(props.order.delivery_status.value);

const isPending = computed(() => status.value === "pending");
const isPaid = computed(() => ["paid", "completed", "partially_refunded"].includes(status.value));
const isCancelled = computed(() => ["cancelled", "refunded", "chargeback"].includes(status.value));

// Fulfilment runs on a queue, so the page watches for it rather than the response blocking on it.
// Polling stops the moment the order reaches a state that will not change on its own.
let timer = null;

const stopPolling = () => {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
};

const poll = async () => {
    try {
        const response = await fetch(route("store.order.status", props.order.uuid), {
            headers: { Accept: "application/json" },
        });

        if (!response.ok) {
            stopPolling();

            return;
        }

        const data = await response.json();
        const settled = status.value !== data.status || deliveryStatus.value !== data.delivery_status;

        status.value = data.status;
        deliveryStatus.value = data.delivery_status;

        if (data.status !== "pending" && data.delivery_status !== "pending") {
            stopPolling();
        }

        if (settled && data.status !== "pending") {
            router.reload({ only: ["order"] });
        }
    } catch {
        stopPolling();
    }
};

if (props.order.status.value === "pending" || props.order.delivery_status.value === "pending") {
    timer = setInterval(poll, 4000);
}

onUnmounted(stopPolling);
</script>

<template>
  <AppLayout>
    <AppHead :title="__('Order :number', { number: order.uuid.substring(0, 8).toUpperCase() })" />

    <div class="px-4 py-12 mx-auto max-w-2xl text-foreground">
      <div class="bg-card rounded-lg shadow p-8 text-center">
        <!-- Paid -->
        <template v-if="isPaid">
          <div class="text-5xl mb-4">
            🎉
          </div>
          <h1 class="text-2xl font-semibold mb-2">
            {{ __("Thank you!") }}
          </h1>
          <p class="text-muted-foreground">
            {{ __("Your payment went through and your items are on their way to :player.", { player: order.player_username }) }}
          </p>

          <p
            v-if="deliveryStatus === 'pending'"
            class="text-sm text-muted-foreground mt-4"
          >
            {{ __("Delivering now…") }}
          </p>
          <p
            v-else-if="deliveryStatus === 'delivered'"
            class="text-sm text-success mt-4"
          >
            {{ __("Everything has been sent to the server. If you were offline, it will arrive the moment you next join.") }}
          </p>
          <p
            v-else-if="deliveryStatus === 'partial'"
            class="text-sm text-orange-500 mt-4"
          >
            {{ __("Some items are still on their way. Nothing is lost — staff can see this order and will make sure it lands.") }}
          </p>
          <p
            v-else
            class="text-sm text-destructive mt-4"
          >
            {{ __("We could not reach the server to deliver this. Staff have been notified and will sort it out.") }}
          </p>
        </template>

        <!-- Awaiting payment -->
        <template v-else-if="isPending">
          <div class="text-5xl mb-4">
            ⏳
          </div>
          <h1 class="text-2xl font-semibold mb-2">
            {{ __("Waiting for payment") }}
          </h1>
          <p class="text-muted-foreground">
            {{ __("We have not received your payment yet. If you have just paid, this page will update by itself in a moment.") }}
          </p>
        </template>

        <!-- Cancelled or refunded -->
        <template v-else>
          <div class="text-5xl mb-4">
            ⚠️
          </div>
          <h1 class="text-2xl font-semibold mb-2">
            {{ __("This order is no longer active") }}
          </h1>
          <p class="text-muted-foreground">
            {{ __("Nothing has been charged, or the charge has been returned to you.") }}
          </p>
        </template>

        <div class="flex items-center justify-center gap-2 mt-6">
          <CommonStatusBadge :status="status" />
          <CommonStatusBadge
            v-if="isPaid"
            :status="deliveryStatus"
          />
        </div>
      </div>

      <!-- Receipt -->
      <div class="bg-card rounded-lg shadow mt-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex justify-between items-center">
          <h2 class="text-sm font-medium">
            {{ __("Order") }} <span class="font-mono">{{ order.uuid.substring(0, 8).toUpperCase() }}</span>
          </h2>
          <span class="text-xs text-muted-foreground">{{ order.created_at }}</span>
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

        <div class="px-6 py-4 border-t border-border flex justify-between text-sm font-medium">
          <span>{{ __("Total") }}</span>
          <span>{{ order.total_formatted }}</span>
        </div>
      </div>

      <div class="flex justify-center gap-3 mt-6">
        <Button
          variant="outline"
          as-child
        >
          <Link :href="route('store.index')">
            {{ __("Back to Store") }}
          </Link>
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
