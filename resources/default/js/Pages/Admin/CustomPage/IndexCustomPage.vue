<script setup>
import DataTable from "@/Components/DataTable/DataTable.vue";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import Icon from "@/Components/Icon.vue";
import { Button } from "@/Components/ui/button";
import { ButtonGroup } from "@/Components/ui/button-group";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useHelpers } from "@/Composables/useHelpers";
import { useTranslations } from "@/Composables/useTranslations";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import {
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";
import { Link } from "@inertiajs/vue3";

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    customPages: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __("Admin"),
        current: false,
    },
    {
        text: __("Custom Pages"),
        current: true,
    }
];

const headerRow = [
    {
        key:"id",
        label: __("ID"),
        sortable: true,
        class:"text-center",
    },
    {
        key:"title",
        sortable: true,
        label: __("Title"),
    },
    {
        key:"path",
        sortable: true,
        label: __("Path"),
    },
    {
        key:"page_type",
        label: __("Page Type"),
        sortable: false,
    },
    {
        key:"is_visible",
        label: __("Visible"),
        sortable: true,
    },
    {
        key:"is_in_navbar",
        label: __("In Navbar"),
        sortable: true,
    },
    {
        key:"is_sidebar_visible",
        sortable: true,
        label: __("Sidebar"),
    },
    {
        key:"is_open_in_new_tab",
        sortable: true,
        label: __("New Tab"),
    },
    {
        key:"created_at",
        sortable: true,
        label: __("Created"),
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
    <app-head :title="__('Manage Custom Pages')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <div class="flex">
          <Button
            v-if="can('create custom_pages')"
            as-child
          >
            <Link :href="route('admin.custom-page.create')">
              {{ __("Create Custom Page") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="customPages"
        :filters="filters"
        :row-href="(item) => route('custom-page.show', item.path)"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
          >
            {{ item.id }}
          </td>

          <DtRowItem>
            {{ item.title }}
          </DtRowItem>

          <DtRowItem>
            {{ item.path }}
          </DtRowItem>

          <td class="px-4 whitespace-normal">
            <div class="flex items-center">
              <div
                class="text-sm font-medium text-foreground dark:text-foreground"
              >
                {{
                  item.is_redirect
                    ?"redirect"
                    : item.is_html_page
                      ?"html"
                      :"markdown"
                }}
              </div>
            </div>
          </td>

          <td class="py-4 text-sm text-foreground px-9">
            <Icon
              v-if="item.is_visible"
              class="text-success focus:outline-hidden"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-destructive"
              name="cross-circle"
            />
          </td>
          <td
            class="py-4 text-sm text-center text-foreground align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_in_navbar"
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
            class="py-4 text-sm text-center text-foreground align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_sidebar_visible"
              class="text-success focus:outline-hidden"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-destructive"
              name="cross-circle"
            />
          </td>

          <td
            class="py-4 text-sm text-center text-foreground align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_open_in_new_tab"
              class="text-success focus:outline-hidden"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-destructive"
              name="cross-circle"
            />
          </td>

          <DtRowItem>
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
                v-if="!item.is_open_in_new_tab"
                variant="outline"
                size="icon"
                as-child
                class="text-primary hover:text-primary"
              >
                <Link
                  as="a"
                  :href="route('custom-page.show', item.path)"
                >
                  <EyeIcon />
                </Link>
              </Button>
              <Button
                v-else
                variant="outline"
                size="icon"
                as-child
                class="text-primary hover:text-primary"
              >
                <a
                  :href="route('custom-page.show', item.path)"
                  target="_blank"
                >
                  <EyeIcon />
                </a>
              </Button>
              <Button
                v-if="can('update custom_pages')"
                variant="outline"
                size="icon"
                as-child
                class="text-yellow-600 dark:text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-400"
              >
                <Link
                  v-tippy
                  as="a"
                  :href="route('admin.custom-page.edit', item.id)"
                  :title="__('Edit Custom Page')"
                >
                  <PencilSquareIcon />
                </Link>
              </Button>
              <Button
                v-if="can('delete custom_pages')"
                variant="outline"
                size="icon"
                as-child
                class="text-destructive hover:text-destructive"
              >
                <Link
                  v-confirm="{
                    message:
                      'Are you sure you want to delete this Custom Page permanently?',
                  }"
                  v-tippy
                  as="button"
                  method="DELETE"
                  :href="route('admin.custom-page.delete', item.id)"
                  :title="__('Delete Custom Page')"
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
