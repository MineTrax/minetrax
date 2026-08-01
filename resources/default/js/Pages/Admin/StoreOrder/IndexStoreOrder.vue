<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useTranslations } from "@/Composables/useTranslations";
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/vue3";
import { EyeIcon } from "@heroicons/vue/24/outline";

const { __ } = useTranslations();

defineProps({
    orders: Object,
    filters: Object,
    statuses: Array,
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Store Orders"), current: true },
];

const headerRow = [
    {
        key: "id",
        sortable: true,
        // Not translated: "#" is a symbol, not a word.
        label: "#",
        // Shrinks to the digits so the id never steals width from the real columns.
        class: "w-px",
    },
    {
        key: "uuid",
        sortable: true,
        label: __("Order"),
        filterable: { type: "text" },
    },
    {
        key: "player_username",
        sortable: true,
        label: __("Recipient"),
        filterable: { type: "text" },
    },
    {
        key: "total",
        sortable: true,
        label: __("Total"),
    },
    {
        key: "status",
        sortable: true,
        label: __("Status"),
    },
    {
        key: "delivery_status",
        sortable: true,
        label: __("Delivery"),
    },
    {
        key: "gateway",
        sortable: true,
        label: __("Gateway"),
    },
    {
        key: "created_at",
        sortable: true,
        label: __("Placed"),
    },
    {
        key: "actions",
        label: __("Actions"),
        sortable: false,
        class: "w-1/12 text-right",
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Orders Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="orders"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem class="text-muted-foreground tabular-nums">
            {{ item.id }}
          </DtRowItem>

          <DtRowItem>
            <Link
              class="font-mono text-xs font-medium text-primary hover:underline"
              :href="route('admin.store.order.show', item.uuid)"
            >
              {{ item.uuid.substring(0, 8).toUpperCase() }}
            </Link>
            <div class="text-xs text-muted-foreground">
              {{ item.items_count }} {{ __("item(s)") }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <div class="font-medium text-foreground">
              {{ item.player_username }}
            </div>
            <div class="text-xs text-muted-foreground">
              {{ item.user?.username ? `@${item.user.username}` : (item.email || __("Guest")) }}
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ item.total_formatted }}
          </DtRowItem>

          <DtRowItem>
            <CommonStatusBadge :status="item.status.value" />
          </DtRowItem>

          <DtRowItem>
            <CommonStatusBadge :status="item.delivery_status.value" />
          </DtRowItem>

          <DtRowItem>
            {{ item.gateway?.value ?? "—" }}
          </DtRowItem>

          <DtRowItem>
            <span class="text-xs text-muted-foreground">{{ item.created_at }}</span>
          </DtRowItem>

          <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
            <Button
              variant="outline"
              size="icon"
              as-child
            >
              <Link
                v-tippy
                as="a"
                :href="route('admin.store.order.show', item.uuid)"
                :title="__('View Order')"
              >
                <EyeIcon />
              </Link>
            </Button>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
