<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import { Link } from "@inertiajs/vue3";
import { PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";
import Icon from "@/Components/Icon.vue";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatToDateString } = useHelpers();

defineProps({
    sales: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Sales"),
        current: true,
    }
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
        key: "name",
        sortable: true,
        label: __("Sale"),
        filterable: {
            type: "text",
        },
    },
    {
        key: "discount_value",
        sortable: true,
        label: __("Discount"),
    },
    {
        key: "saleables_count",
        sortable: false,
        label: __("Applies To"),
    },
    {
        key: "starts_at",
        sortable: true,
        label: __("Window"),
    },
    {
        key: "is_enabled",
        label: __("Running"),
        sortable: true,
    },
    {
        key: "actions",
        label: __("Actions"),
        sortable: false,
        class: "w-1/12 text-right",
    },
];

// A percentage is basis points, which is safe to divide here — it is not money. A fixed amount is
// minor units, so the server sends it already formatted in the base currency.
function discountLabel(sale) {
    const type = sale.discount_type?.value ?? sale.discount_type;

    if (type === "percent") {
        return `${Number(sale.discount_value) / 100}%`;
    }

    return sale.discount_formatted;
}

function windowLabel(sale) {
    if (! sale.starts_at && ! sale.ends_at) {
        return __("Always");
    }
    const from = sale.starts_at ? formatToDateString(sale.starts_at) : __("Now");
    const to = sale.ends_at ? formatToDateString(sale.ends_at) : __("No end");

    return `${from} → ${to}`;
}

// Read from the declared scope, not from whether any rows happen to exist: a packages-scoped sale
// with nothing picked covers nothing, and reporting that as "Everything" would be the opposite of
// the truth.
function scopeLabel(sale) {
    const scope = sale.scope_type?.value ?? sale.scope_type;

    if (scope === "all") {
        return __("Whole store");
    }

    const noun = scope === "categories" ? __("categories") : __("packages");

    return __(":count :noun", { count: sale.saleables_count, noun });
}

// What a shopper has to spend before the sale does anything. The server formats it, because
// min_basket_amount is minor units.
function minimumLabel(sale) {
    return sale.min_basket_formatted ? __("min :amount", { amount: sale.min_basket_formatted }) : null;
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Sales Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_sales')"
            as-child
          >
            <Link :href="route('admin.store.sale.create')">
              {{ __("Create Sale") }}
            </Link>
          </Button>
        </div>
      </div>

      <p class="text-sm text-muted-foreground mb-4">
        {{ __("A sale discounts prices on the storefront automatically for as long as it runs — no code to type in. Sales never stack: where several apply, the largest saving wins.") }}
      </p>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="sales"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem class="text-muted-foreground tabular-nums">
            {{ item.id }}
          </DtRowItem>

          <DtRowItem>
            <div class="font-medium text-foreground">
              {{ item.name }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <span class="font-medium">{{ discountLabel(item) }}</span>
            <span class="text-xs text-muted-foreground ml-1">
              {{ __("off") }}
            </span>
            <div
              v-if="minimumLabel(item)"
              class="text-xs text-muted-foreground"
            >
              {{ minimumLabel(item) }}
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ scopeLabel(item) }}
          </DtRowItem>

          <DtRowItem>
            <span class="text-xs">{{ windowLabel(item) }}</span>
          </DtRowItem>

          <td class="px-4">
            <Icon
              v-if="item.is_running"
              v-tippy
              class="text-success"
              name="check-circle"
              :title="__('Discounting prices right now')"
            />
            <Icon
              v-else
              v-tippy
              class="text-muted-foreground"
              name="cross-circle"
              :title="item.is_enabled ? __('Enabled, but outside its window') : __('Disabled')"
            />
          </td>

          <td
            class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
          >
            <ButtonGroup>
              <Button
                v-if="can('update store_sales')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.sale.edit', item.id)"
                  :title="__('Edit Sale')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('delete store_sales')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message: __('Are you sure you want to delete this sale permanently? Orders placed while it ran keep the price they were charged.'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.sale.delete', item.id)"
                  :title="__('Delete Sale')"
                >
                  <TrashIcon />
                </Link>
              </Button>
            </ButtonGroup>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
