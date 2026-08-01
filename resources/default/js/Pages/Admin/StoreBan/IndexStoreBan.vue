<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import { Link } from "@inertiajs/vue3";
import { PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatToDayDateString, formatTimeAgoToNow } = useHelpers();

defineProps({
    bans: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Bans"),
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
        key: "identity",
        sortable: false,
        label: __("Identity"),
        filterable: {
            // One box across every identity column: staff have one thing in hand — a username, an
            // IP, an email — and do not know which column it was banned under.
            key: "q",
            type: "text",
        },
    },
    {
        key: "reason",
        sortable: false,
        label: __("Reason"),
    },
    {
        key: "is_automatic",
        sortable: true,
        label: __("Raised By"),
        filterable: {
            key: "is_automatic",
            type: "select",
            options: ["true", "false"],
            searchable: false,
        },
    },
    {
        key: "expires_at",
        sortable: true,
        label: __("In Force"),
        filterable: {
            key: "active",
            type: "select",
            options: ["true", "false"],
            searchable: false,
        },
    },
    {
        key: "created_at",
        sortable: true,
        label: __("Created"),
    },
    {
        key: "actions",
        label: __("Actions"),
        sortable: false,
        class: "w-1/12 text-right",
    },
];

// Any single identity is enough to block a checkout, so a ban with one filled column is complete
// rather than half-entered — worth listing them as the set they are.
function identities(ban) {
    return [
        ban.user ? { label: __("Account"), value: ban.user.username } : null,
        ban.player_uuid ? { label: __("Player"), value: ban.player_uuid, mono: true } : null,
        ban.ip_address ? { label: __("IP"), value: ban.ip_address, mono: true } : null,
        ban.email ? { label: __("Email"), value: ban.email } : null,
    ].filter(Boolean);
}

// A ban with a past expiry is kept as a record but no longer blocks anybody, and that distinction
// is the first thing support needs when a buyer says they still cannot check out.
function isInForce(ban) {
    return ! ban.expires_at || new Date(ban.expires_at) > new Date();
}

function forceLabel(ban) {
    if (! ban.expires_at) {
        return __("Permanent");
    }

    return __("Until :date", { date: formatToDayDateString(ban.expires_at) });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Bans Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_bans')"
            as-child
          >
            <Link :href="route('admin.store.ban.create')">
              {{ __("Create Ban") }}
            </Link>
          </Button>
        </div>
      </div>

      <p class="text-sm text-muted-foreground mb-4">
        {{ __("A store ban blocks checkout. It can target an account, a player UUID, an IP address or an email, and any single match is enough — which is why a chargeback ban lists several. Deleting a row lifts the ban.") }}
      </p>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="bans"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem class="text-muted-foreground tabular-nums">
            {{ item.id }}
          </DtRowItem>

          <DtRowItem>
            <div
              v-for="identity in identities(item)"
              :key="identity.label"
              class="text-xs"
            >
              <span class="text-muted-foreground">{{ identity.label }}:</span>
              <span
                class="ml-1 text-foreground"
                :class="{ 'font-mono': identity.mono }"
              >{{ identity.value }}</span>
            </div>
          </DtRowItem>

          <DtRowItem>
            <span
              v-if="item.reason"
              class="text-xs"
            >{{ item.reason }}</span>
            <span
              v-else
              class="text-xs text-muted-foreground"
            >{{ __("No reason given") }}</span>
          </DtRowItem>

          <DtRowItem>
            <span
              v-if="item.is_automatic"
              class="text-xs"
            >{{ __("Chargeback") }}</span>
            <span
              v-else
              class="text-xs"
            >{{ item.creator?.username ?? __("Staff") }}</span>
          </DtRowItem>

          <DtRowItem>
            <CommonStatusBadge
              :status="isInForce(item) ? 'active' : 'expired'"
              :value="isInForce(item) ? __('Yes') : __('Expired')"
            />
            <div class="text-xs text-muted-foreground mt-1">
              {{ forceLabel(item) }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <span
              v-tippy
              class="text-xs text-muted-foreground"
              :title="formatToDayDateString(item.created_at)"
            >{{ formatTimeAgoToNow(item.created_at) }}</span>
          </DtRowItem>

          <td
            class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
          >
            <ButtonGroup>
              <Button
                v-if="can('update store_bans')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.ban.edit', item.id)"
                  :title="__('Edit Ban')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('delete store_bans')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message: __('Lift this ban? The buyer will be able to check out again immediately.'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.ban.delete', item.id)"
                  :title="__('Lift Ban')"
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
