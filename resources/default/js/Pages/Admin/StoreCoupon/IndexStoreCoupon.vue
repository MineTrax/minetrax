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
    coupons: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Coupons"),
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
            type: "text",
        },
    },
    {
        key: "discount_value",
        sortable: true,
        label: __("Discount"),
    },
    {
        key: "couponables_count",
        sortable: false,
        label: __("Applies To"),
    },
    {
        key: "used_count",
        sortable: true,
        class: "text-center",
        label: __("Redeemed"),
    },
    {
        key: "expires_at",
        sortable: true,
        label: __("Window"),
    },
    {
        key: "created_by",
        sortable: true,
        label: __("Created By"),
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

// A coupon with neither a start nor an end is live for as long as it is enabled, which is worth
// saying rather than leaving two dashes for the admin to interpret.
function windowLabel(coupon) {
    if (! coupon.starts_at && ! coupon.expires_at) {
        return __("Always");
    }
    const from = coupon.starts_at ? new Date(coupon.starts_at).toLocaleDateString() : __("Now");
    const to = coupon.expires_at ? new Date(coupon.expires_at).toLocaleDateString() : __("No end");

    return `${from} → ${to}`;
}

// A percentage is basis points, which is safe to divide here — it is not money. A fixed amount is
// minor units, so the server sends it already formatted in its own currency.
function discountLabel(coupon) {
    const type = coupon.discount_type?.value ?? coupon.discount_type;

    if (type === "percent") {
        return `${Number(coupon.discount_value) / 100}%`;
    }

    return coupon.discount_formatted;
}

function scopeLabel(coupon) {
    return coupon.couponables_count === 0
        ? __("Everything")
        : __(":count selected", { count: coupon.couponables_count });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Coupons Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_coupons')"
            as-child
          >
            <Link :href="route('admin.store.coupon.create')">
              {{ __("Create Coupon") }}
            </Link>
          </Button>
        </div>
      </div>

      <p class="text-sm text-muted-foreground mb-4">
        {{ __("A coupon is a code the customer types in at the cart. Leave its scope empty to discount the whole store, or pick packages and categories to narrow it.") }}
      </p>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="coupons"
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
            <div
              v-if="item.description"
              class="text-xs text-muted-foreground mt-1"
            >
              {{ item.description }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <span class="font-medium">{{ discountLabel(item) }}</span>
            <span class="text-xs text-muted-foreground ml-1">
              {{ __("off") }}
            </span>
          </DtRowItem>

          <DtRowItem>
            {{ scopeLabel(item) }}
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.used_count }}<span
              v-if="item.max_uses_total"
              class="text-muted-foreground"
            > / {{ item.max_uses_total }}</span>
          </DtRowItem>

          <DtRowItem>
            <span class="text-xs">{{ windowLabel(item) }}</span>
          </DtRowItem>

          <!-- Null for a coupon seeded, imported, or written before the column existed. Unlike a
               gift card there is no second origin to name, so a dash is the honest answer. -->
          <DtRowItem>
            <span
              v-if="item.creator"
              class="text-xs"
            >{{ item.creator.username }}</span>
            <span
              v-else
              class="text-xs text-muted-foreground"
            >&mdash;</span>
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
                v-if="item.can_update"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.coupon.edit', item.id)"
                  :title="__('Edit Coupon')"
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
                    message: __('Are you sure you want to delete this coupon permanently? Orders that used it keep their discount.'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.coupon.delete', item.id)"
                  :title="__('Delete Coupon')"
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
