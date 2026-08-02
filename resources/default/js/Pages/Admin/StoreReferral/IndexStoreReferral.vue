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
import { ChartBarIcon, PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";
import Icon from "@/Components/Icon.vue";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatToDateString } = useHelpers();

defineProps({
    referrals: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Referrals"),
        current: true,
    },
];

const headerRow = [
    {
        key: "id",
        sortable: true,
        // Not translated: "#" is a symbol, not a word.
        label: "#",
        class: "w-px",
    },
    {
        key: "code",
        sortable: true,
        label: __("Code"),
        filterable: {
            type: "text",
        },
    },
    {
        key: "referrer_name",
        sortable: true,
        label: __("Referrer"),
        filterable: {
            type: "text",
        },
    },
    {
        key: "share_bp",
        sortable: true,
        class: "text-right",
        label: __("Share"),
    },
    {
        key: "visit_count",
        sortable: true,
        class: "text-right",
        label: __("Visits"),
    },
    {
        key: "orders_count",
        sortable: false,
        class: "text-right",
        label: __("Orders"),
    },
    {
        key: "earned_base",
        sortable: true,
        class: "text-right",
        label: __("Earned"),
    },
    {
        key: "paid_out",
        sortable: true,
        class: "text-right",
        label: __("Paid Out"),
    },
    {
        // Not sortable: it is earned minus paid out, computed per row rather than in SQL. Sorting it
        // would need a derived column for a number that is only ever scanned.
        key: "owed",
        sortable: false,
        class: "text-right",
        label: __("Owed"),
    },
    {
        key: "is_enabled",
        label: __("Enabled"),
        sortable: true,
    },
    {
        key: "actions",
        label: __("Actions"),
        sortable: false,
        class: "w-1/12 text-right",
    },
];

// Basis points are safe to divide — they are a rate, not money.
function shareLabel(referral) {
    return `${Number(referral.share_bp) / 100}%`;
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Referrals Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_referrals')"
            as-child
          >
            <Link :href="route('admin.store.referral.create')">
              {{ __("Create Referral Code") }}
            </Link>
          </Button>
        </div>
      </div>

      <p class="text-sm text-muted-foreground mb-4">
        {{ __("A referral code lets a creator send buyers to your store and earn a share of what they spend. Owed is what they have earned less what you have already paid them.") }}
      </p>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="referrals"
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
          </DtRowItem>

          <DtRowItem>
            {{ item.referrer_name }}
            <div
              v-if="item.user"
              class="text-xs text-muted-foreground mt-1"
            >
              @{{ item.user.username }}
            </div>
          </DtRowItem>

          <DtRowItem class="text-right tabular-nums">
            {{ shareLabel(item) }}
          </DtRowItem>

          <DtRowItem class="text-right tabular-nums">
            {{ item.visit_count }}
            <div
              v-if="item.last_visited_at"
              class="text-xs text-muted-foreground"
            >
              {{ formatToDateString(item.last_visited_at) }}
            </div>
          </DtRowItem>

          <DtRowItem class="text-right tabular-nums">
            {{ item.orders_count }}
          </DtRowItem>

          <DtRowItem class="text-right tabular-nums">
            {{ item.earned_formatted }}
          </DtRowItem>

          <DtRowItem class="text-right tabular-nums text-muted-foreground">
            {{ item.paid_out_formatted }}
          </DtRowItem>

          <!-- Negative when a refund landed after a payout: the referrer was paid for a sale that
               later unwound, and it is carried against what they earn next. -->
          <DtRowItem
            class="text-right tabular-nums font-medium"
            :class="item.owed < 0 ? 'text-destructive' : ''"
          >
            {{ item.owed_formatted }}
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
                variant="outline"
                size="icon"
                as-child
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.referral.show', item.id)"
                  :title="__('Earnings and Payouts')"
                >
                  <ChartBarIcon />
                </Link>
              </Button>
              <Button
                v-if="item.can_update"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.referral.edit', item.id)"
                  :title="__('Edit Referral Code')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="item.can_delete"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message: __('Are you sure you want to delete this referral code? Orders it earned on keep their record, and it stops crediting anyone immediately.'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.referral.delete', item.id)"
                  :title="__('Delete Referral Code')"
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
