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
import { DocumentDuplicateIcon, PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/outline";
import Icon from "@/Components/Icon.vue";

const { can } = useAuthorizable();
const { __ } = useTranslations();

const props = defineProps({
    packages: Object,
    categoryNames: Array,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Packages"),
        current: true,
    }
];

const typeLabels = {
    minecraft_package: __("Minecraft Package"),
    giftcard: __("Giftcard"),
    both: __("Package & Giftcard"),
};

// The publish window is evaluated on read rather than flipped by a job, so this is where an admin
// sees that a package is withdrawn even though it is still enabled.
function scheduleStatus(item) {
    if (! item.is_enabled) {
        return { label: __("Disabled"), class: "bg-muted text-muted-foreground" };
    }
    if (item.available_from && new Date(item.available_from) > new Date()) {
        return { label: __("Scheduled"), class: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400" };
    }
    if (item.available_until && new Date(item.available_until) < new Date()) {
        return { label: __("Expired"), class: "bg-destructive/10 text-destructive" };
    }
    return { label: __("Live"), class: "bg-success/10 text-success" };
}

const headerRow = [
    {
        key: "name",
        sortable: true,
        label: __("Package"),
        filterable: {
            type: "text",
        },
    },
    {
        key: "slug",
        sortable: true,
        label: __("Slug"),
    },
    {
        key: "store_category_id",
        sortable: true,
        label: __("Category"),
        filterable: {
            key: "category.name",
            // Picking from the real list beats typing a name and guessing at the spelling. Multi,
            // so several categories can be shown at once — the same shape as the command queue's
            // status filter.
            type: "multiselect",
            options: props.categoryNames,
            searchable: true,
        },
    },
    {
        key: "price",
        sortable: true,
        label: __("Price"),
    },
    {
        key: "sold_count",
        sortable: true,
        class: "text-center",
        label: __("Sold"),
    },
    {
        key: "commands_count",
        sortable: true,
        class: "text-center",
        label: __("Commands"),
    },
    {
        key: "status",
        label: __("Status"),
        sortable: false,
    },
    {
        key: "is_enabled",
        label: __("Enabled"),
        sortable: true,
    },
    {
        key: "is_visible",
        label: __("Visible"),
        sortable: true,
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
    <app-head :title="__('Store Packages Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_packages')"
            as-child
          >
            <Link :href="route('admin.store.package.create')">
              {{ __("Create Package") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="packages"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem>
            <div>
              <div class="font-medium text-foreground flex items-center gap-2">
                {{ item.name }}
                <span
                  v-if="item.is_featured"
                  class="px-1.5 py-0.5 text-xs font-medium bg-primary/10 text-primary rounded"
                >
                  {{ __("Featured") }}
                </span>
              </div>
              <div class="text-xs text-muted-foreground">
                {{ item.type?.value ? typeLabels[item.type.value] : "—" }}
              </div>
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ item.slug }}
          </DtRowItem>

          <DtRowItem>
            <Link
              v-if="item.category"
              :href="route('admin.store.category.edit', item.store_category_id)"
              class="text-primary hover:underline"
            >
              {{ item.category.name }}
            </Link>
            <span
              v-else
              class="text-muted-foreground"
            >&mdash;</span>
          </DtRowItem>

          <DtRowItem>
            {{ item.price_formatted }}
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.sold_count }}
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.commands_count }}
          </DtRowItem>

          <DtRowItem>
            <span
              class="px-2 py-1 text-xs font-medium rounded"
              :class="scheduleStatus(item).class"
            >
              {{ scheduleStatus(item).label }}
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

          <td class="px-4">
            <Icon
              v-if="item.is_visible"
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
                v-if="can('update store_packages')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.package.edit', item.id)"
                  :title="__('Edit Package')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('create store_packages')"
                variant="outline"
                size="icon"
                as-child
              >
                <Link
                  v-confirm="{
                    message: __('Copy this package, with its commands, prices, variables and prerequisites? The copy is created disabled so it is not on the storefront until you enable it.'),
                  }"
                  v-tippy
                  as="button"
                  method="POST"
                  :href="route('admin.store.package.duplicate', item.id)"
                  :title="__('Duplicate Package')"
                >
                  <DocumentDuplicateIcon />
                </Link>
              </Button>
              <Button
                v-if="can('delete store_packages')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message: __('Are you sure you want to delete this Store Package permanently?'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.package.delete', item.id)"
                  :title="__('Delete Package')"
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
