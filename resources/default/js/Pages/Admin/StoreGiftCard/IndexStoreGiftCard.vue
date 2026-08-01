<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useTranslations } from "@/Composables/useTranslations";
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import Icon from "@/Components/Icon.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import { Link } from "@inertiajs/vue3";
import { EyeIcon, PencilSquareIcon } from "@heroicons/vue/24/outline";

const { can } = useAuthorizable();
const { __ } = useTranslations();

const props = defineProps({
    cards: Object,
    filters: Object,
    currencies: Array,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Gift Cards"),
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
        key: "code",
        sortable: true,
        label: __("Code"),
        filterable: {
            // One box over the code and the account it was issued to: support has whichever of the
            // two the player quoted at them.
            key: "q",
            type: "text",
        },
    },
    {
        key: "balance",
        sortable: true,
        label: __("Remaining"),
        filterable: {
            key: "spendable",
            type: "select",
            options: ["true", "false"],
            searchable: false,
        },
    },
    {
        key: "currency_code",
        sortable: true,
        label: __("Currency"),
        filterable: {
            type: "select",
            options: props.currencies,
            searchable: true,
        },
    },
    {
        key: "issued_to",
        sortable: false,
        label: __("Issued To"),
    },
    {
        key: "orders_count",
        sortable: false,
        class: "text-center",
        label: __("Orders"),
    },
    {
        key: "is_enabled",
        sortable: true,
        label: __("Enabled"),
        filterable: {
            type: "select",
            options: ["true", "false"],
            searchable: false,
        },
    },
    {
        key: "actions",
        label: __("Actions"),
        sortable: false,
        class: "w-1/12 text-right",
    },
];

function expiryLabel(card) {
    if (! card.expires_at) {
        return __("No expiry");
    }

    const date = new Date(card.expires_at);

    return date > new Date()
        ? __("Expires :date", { date: date.toLocaleDateString() })
        : __("Expired :date", { date: date.toLocaleDateString() });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Gift Cards Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_gift_cards')"
            as-child
          >
            <Link :href="route('admin.store.gift-card.create')">
              {{ __("Issue Gift Card") }}
            </Link>
          </Button>
        </div>
      </div>

      <p class="text-sm text-muted-foreground mb-4">
        {{ __("A gift card is store credit a customer redeems at the cart. Most are bought; issue one here to compensate a player or run a giveaway. Open a card to see every movement on it.") }}
      </p>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="cards"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem class="text-muted-foreground tabular-nums">
            {{ item.id }}
          </DtRowItem>

          <DtRowItem>
            <code class="px-1.5 py-0.5 rounded bg-muted text-xs font-mono select-all">
              {{ item.code }}
            </code>
            <div class="text-xs text-muted-foreground mt-1">
              {{ expiryLabel(item) }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <span class="font-medium">{{ item.balance_formatted }}</span>
            <div
              v-if="item.balance !== item.original_balance"
              class="text-xs text-muted-foreground"
            >
              {{ __("of :amount", { amount: item.original_balance_formatted }) }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <span class="text-xs font-mono">{{ item.currency_code }}</span>
          </DtRowItem>

          <DtRowItem>
            <span
              v-if="item.issued_to_user"
              class="text-xs"
            >{{ item.issued_to_user.username }}</span>
            <span
              v-else
              class="text-xs text-muted-foreground"
            >{{ __("Anyone holding the code") }}</span>
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.orders_count }}
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

          <td
            class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
          >
            <ButtonGroup>
              <Button
                variant="outline"
                size="icon"
                as-child
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.gift-card.show', item.id)"
                  :title="__('View Card')"
                >
                  <EyeIcon />
                </Link>
              </Button>
              <Button
                v-if="can('update store_gift_cards')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.gift-card.edit', item.id)"
                  :title="__('Edit Card')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
            </ButtonGroup>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
