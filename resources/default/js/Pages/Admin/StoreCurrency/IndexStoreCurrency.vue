<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useTranslations } from "@/Composables/useTranslations";
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

defineProps({
    currencies: Array,
    baseCurrency: String,
    baseIsLocked: Boolean,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Currencies"),
        current: true,
    }
];

function makeBaseCurrency(currencyCode) {
    router.post(route("admin.store-currency.make-base"), {
        code: currencyCode,
    });
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
            <Link :href="route('admin.store-currency.create')">
              {{ __("Add Currency") }}
            </Link>
          </Button>
        </div>
      </div>

      <div
        v-if="baseIsLocked"
        class="mb-6"
      >
        <AlertCard variant="info">
          {{ __("The base currency is locked because orders already exist. You cannot change the base currency.") }}
        </AlertCard>
      </div>

      <div class="bg-card rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-border">
          <thead class="bg-muted">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                {{ __("Code") }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                {{ __("Name") }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                {{ __("Symbol") }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                {{ __("Exponent") }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                {{ __("Rate to Base") }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                {{ __("Countries") }}
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider">
                {{ __("Enabled") }}
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                {{ __("Actions") }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-card divide-y divide-border">
            <tr
              v-for="row in currencies"
              :key="row.id"
              class="hover:bg-muted transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span class="font-medium">{{ row.code }}</span>
                  <Badge
                    v-if="row.is_base"
                    variant="default"
                  >
                    {{ __("Base") }}
                  </Badge>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ row.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ row.symbol }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ row.exponent }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ row.rate_to_base }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div
                  v-if="row.country_codes && row.country_codes.length > 0"
                  class="flex gap-1 flex-wrap"
                >
                  <span
                    v-for="code in row.country_codes"
                    :key="code"
                    class="inline-flex px-2 py-1 text-xs font-medium text-foreground bg-muted rounded-full"
                  >
                    {{ code }}
                  </span>
                </div>
                <span
                  v-else
                  class="text-muted-foreground"
                >—</span>
              </td>
              <td class="px-6 py-4 text-center">
                <Icon
                  v-if="row.is_enabled"
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
                      :href="route('admin.store-currency.edit', row.id)"
                      :title="__('Edit Currency')"
                    >
                      <PencilSquareIcon />
                    </Link>
                  </Button>
                  <Button
                    v-if="!row.is_base && !baseIsLocked && can('update store_currencies')"
                    v-tippy
                    variant="outline"
                    size="icon"
                    :title="__('Make Base Currency')"
                    @click="makeBaseCurrency(row.code)"
                  >
                    <ArrowsRightLeftIcon />
                  </Button>
                  <Button
                    v-if="!row.is_base && can('delete store_currencies')"
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
                      :href="route('admin.store-currency.delete', row.id)"
                      :title="__('Delete Currency')"
                    >
                      <TrashIcon />
                    </Link>
                  </Button>
                </ButtonGroup>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
