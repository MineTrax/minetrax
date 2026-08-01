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
import { Badge } from "@/Components/ui/badge";
import AlertCard from "@/Components/AlertCard.vue";
import { Link, router } from "@inertiajs/vue3";
import { PencilSquareIcon, TrashIcon, ArrowsRightLeftIcon } from "@heroicons/vue/24/outline";
import Icon from "@/Components/Icon.vue";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatToDateString } = useHelpers();

defineProps({
    currencies: Object,
    filters: Object,
    baseCurrency: String,
    baseIsLocked: Boolean,
});

const breadcrumbItems = [
    { text: __("Admin"), current: false },
    { text: __("Store"), current: false },
    { text: __("Currencies"), current: true },
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
        key: "code",
        sortable: true,
        label: __("Code"),
        filterable: { type: "text" },
    },
    {
        key: "name",
        sortable: true,
        label: __("Name"),
        filterable: { type: "text" },
    },
    {
        key: "symbol",
        sortable: true,
        label: __("Symbol"),
    },
    {
        key: "exponent",
        sortable: true,
        class: "text-center",
        label: __("Decimals"),
    },
    {
        key: "rate_to_base",
        sortable: true,
        label: __("Rate to Base"),
    },
    {
        key: "is_enabled",
        sortable: true,
        class: "text-center",
        label: __("Enabled"),
    },
    {
        key: "actions",
        label: __("Actions"),
        sortable: false,
        class: "w-1/12 text-right",
    },
];

function makeBaseCurrency(currency) {
    router.post(route("admin.store.currency.make-base", currency.id), {}, { preserveScroll: true });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Currencies Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_currencies')"
            as-child
          >
            <Link :href="route('admin.store.currency.create')">
              {{ __("Add Currency") }}
            </Link>
          </Button>
        </div>
      </div>

      <AlertCard
        v-if="baseIsLocked"
        variant="info"
        class="mb-6"
      >
        {{ __("The base currency is locked because orders already exist. Their revenue was recorded against :code.", { code: baseCurrency }) }}
      </AlertCard>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="currencies"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem class="text-muted-foreground tabular-nums">
            {{ item.id }}
          </DtRowItem>

          <DtRowItem>
            <div class="flex items-center gap-2">
              <span class="font-medium text-foreground">{{ item.code }}</span>
              <Badge
                v-if="item.is_base"
                variant="default"
              >
                {{ __("Base") }}
              </Badge>
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ item.name }}
            <div
              v-if="item.country_codes && item.country_codes.length"
              class="text-xs text-muted-foreground"
            >
              {{ item.country_codes.join(", ") }}
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ item.symbol }}
            <span class="text-xs text-muted-foreground">({{ item.symbol_position }})</span>
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.exponent }}
          </DtRowItem>

          <DtRowItem>
            <span
              v-if="item.is_base"
              class="text-muted-foreground"
            >—</span>
            <template v-else>
              <span>{{ item.rate_to_base }}</span>
              <!-- Freshness matters: a currency the automatic feed does not carry keeps whatever
                   rate was set by hand, and only this date says so. -->
              <div class="text-xs text-muted-foreground">
                {{ item.rate_updated_at
                  ? __("Updated :date", { date: formatToDateString(item.rate_updated_at) })
                  : __("Set manually") }}
              </div>
            </template>
          </DtRowItem>

          <td class="px-4 text-center">
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
                v-if="can('update store_currencies')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.currency.edit', item.id)"
                  :title="__('Edit Currency')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="!item.is_base && !baseIsLocked && can('update store_currencies')"
                v-tippy
                variant="outline"
                size="icon"
                :title="__('Make Base Currency')"
                @click="makeBaseCurrency(item)"
              >
                <ArrowsRightLeftIcon />
              </Button>
              <Button
                v-if="!item.is_base && can('delete store_currencies')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message: __('Are you sure you want to delete this currency permanently?'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.currency.delete', item.id)"
                  :title="__('Delete Currency')"
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
