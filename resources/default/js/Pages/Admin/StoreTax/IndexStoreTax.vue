<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useTranslations } from "@/Composables/useTranslations";
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

defineProps({
    taxes: Object,
    filters: Object,
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Store Taxes"), current: true },
];

const headerRow = [
    {
        key: "id",
        sortable: true,
        // Not translated: "#" is a symbol, not a word.
        label: "#",
        class: "w-px",
    },
    { key: "name", sortable: true, label: __("Name"), filterable: { key: "q", type: "text" } },
    { key: "country_id", sortable: true, label: __("Applies To") },
    { key: "rate_bp", sortable: true, label: __("Rate") },
    { key: "is_inclusive", sortable: true, label: __("Price Includes Tax") },
    { key: "is_enabled", sortable: true, label: __("Enabled") },
    { key: "actions", label: __("Actions"), sortable: false, class: "w-1/12 text-right" },
];

// Basis points are safe to divide here — a percentage is not money.
const ratePercent = (tax) => `${Number(tax.rate_bp) / 100}%`;
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Taxes Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_taxes')"
            as-child
          >
            <Link :href="route('admin.store.tax.create')">
              {{ __("Create Tax") }}
            </Link>
          </Button>
        </div>
      </div>

      <p class="text-sm text-muted-foreground mb-4">
        {{ __("One rule per country, plus an optional global rule for everyone else. A buyer is matched to their own country's rule first; only one rule ever applies to an order.") }}
      </p>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="taxes"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem class="text-muted-foreground tabular-nums">
            {{ item.id }}
          </DtRowItem>

          <DtRowItem>
            <span class="font-medium">{{ item.name }}</span>
          </DtRowItem>

          <DtRowItem>
            <span v-if="item.country">{{ item.country.name }}</span>
            <span
              v-else
              class="px-2 py-0.5 text-xs font-medium bg-primary/10 text-primary rounded"
            >{{ __("Everyone else") }}</span>
          </DtRowItem>

          <DtRowItem class="tabular-nums">
            {{ ratePercent(item) }}
          </DtRowItem>

          <DtRowItem>
            <span class="text-xs text-muted-foreground">
              {{ item.is_inclusive ? __("Included in the price") : __("Added at checkout") }}
            </span>
          </DtRowItem>

          <td class="px-4">
            <Icon
              v-if="item.is_enabled"
              class="text-success"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-destructive"
              name="cross-circle"
            />
          </td>

          <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
            <ButtonGroup>
              <Button
                v-if="can('update store_taxes')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.tax.edit', item.id)"
                  :title="__('Edit Tax')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('delete store_taxes')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message: __('Delete this tax rule? Orders already placed keep the rate they were charged.'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.tax.delete', item.id)"
                  :title="__('Delete Tax')"
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
