<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { Button } from "@/Components/ui/button";
import Pagination from "@/Components/Pagination.vue";

const { __ } = useTranslations();

defineProps({
    orders: { type: Object, required: true },
});
</script>

<template>
  <AppLayout>
    <AppHead :title="__('My Purchases')" />

    <div class="px-4 py-8 mx-auto max-w-4xl text-foreground">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">
          {{ __("My Purchases") }}
        </h1>
        <Button
          variant="outline"
          as-child
        >
          <Link :href="route('store.index')">
            {{ __("Browse Store") }}
          </Link>
        </Button>
      </div>

      <div
        v-if="!orders.data.length"
        class="bg-card rounded-lg shadow p-12 text-center"
      >
        <p class="text-muted-foreground mb-4">
          {{ __("You have not bought anything yet.") }}
        </p>
        <Button as-child>
          <Link :href="route('store.index')">
            {{ __("Visit the Store") }}
          </Link>
        </Button>
      </div>

      <div
        v-else
        class="space-y-3"
      >
        <Link
          v-for="order in orders.data"
          :key="order.uuid"
          class="block bg-card rounded-lg shadow p-4 hover:bg-muted/40 transition-colors"
          :href="route('store.my-order.show', order.uuid)"
        >
          <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-sm font-medium">{{ order.number }}</span>
                <CommonStatusBadge :status="order.status.value" />
                <CommonStatusBadge :status="order.delivery_status.value" />
              </div>
              <p class="text-sm text-muted-foreground">
                {{ order.items.map((item) => `${item.quantity} x ${item.package_name}`).join(", ") }}
              </p>
              <p class="text-xs text-muted-foreground mt-1">
                {{ __("For") }} {{ order.player_username }} · {{ order.created_at }}
              </p>
            </div>
            <span class="font-medium whitespace-nowrap">{{ order.total_formatted }}</span>
          </div>
        </Link>

        <Pagination
          v-if="orders.last_page > 1"
          :links="orders.links"
        />
      </div>
    </div>
  </AppLayout>
</template>
