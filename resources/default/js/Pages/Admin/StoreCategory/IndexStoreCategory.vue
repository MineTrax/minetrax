<script setup>
import AdminLayout from"@/Layouts/AdminLayout.vue";
import { useAuthorizable } from"@/Composables/useAuthorizable";
import { useHelpers } from"@/Composables/useHelpers";
import { useTranslations } from"@/Composables/useTranslations";
import DataTable from"@/Components/DataTable/DataTable.vue";
import DtRowItem from"@/Components/DataTable/DtRowItem.vue";
import AppBreadcrumb from"@/Shared/AppBreadcrumb.vue";
import { Button } from"@/Components/ui/button";
import { ButtonGroup } from"@/Components/ui/button-group";
import { Link } from"@inertiajs/vue3";
import { PencilSquareIcon, TrashIcon } from"@heroicons/vue/24/outline";
import Icon from"@/Components/Icon.vue";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    categories: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Store Categories"),
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
        key:"name",
        sortable: true,
        label: __("Name"),
        filterable: {
            type:"text",
        },
    },
    {
        key:"slug",
        label: __("Slug"),
        sortable: true,
    },
    {
        key:"packages_count",
        label: __("Packages"),
        sortable: true,
        class:"text-center",
    },
    {
        key:"sort_order",
        label: __("Sort Order"),
        sortable: true,
        class:"text-center",
    },
    {
        key:"is_visible",
        label: __("Visible"),
        sortable: true,
        class:"text-center",
    },
    {
        key:"is_enabled",
        label: __("Enabled"),
        sortable: true,
        class:"text-center",
    },
    {
        key:"created_at",
        label: __("Created"),
        sortable: true,
        class:"w-1/12",
    },
    {
        key:"actions",
        label: __("Actions"),
        sortable: false,
        class:"w-1/12 text-right",
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Store Categories Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create store_categories')"
            as-child
          >
            <Link :href="route('admin.store.category.create')">
              {{ __("Create Category") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="categories"
        :filters="filters"
      >
        <template #default="{ item }">
          <DtRowItem class="text-muted-foreground tabular-nums">
            {{ item.id }}
          </DtRowItem>

          <DtRowItem>
            <div>
              <div>{{ item.name }}</div>
              <div
                v-if="item.parent"
                class="text-xs text-muted-foreground"
              >
                {{ item.parent.name }}
              </div>
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ item.slug }}
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.packages_count }}
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.sort_order }}
          </DtRowItem>

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

          <DtRowItem class="whitespace-nowrap">
            <span
              v-tippy
              :title="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>

          <td
            class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
          >
            <ButtonGroup>
              <Button
                v-if="can('update store_categories')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.store.category.edit', item.id)"
                  :title="__('Edit Category')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('delete store_categories')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message:
                      __('Are you sure you want to delete this Store Category permanently?'),
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.store.category.delete', item.id)"
                  :title="__('Delete Category')"
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
