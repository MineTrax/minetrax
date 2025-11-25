<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link } from '@inertiajs/vue3';
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    badges: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('User Badges'),
        current: true,
    }
];

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'text-center',
    },
    {
        key: 'image',
        sortable: false,
        label: __('Image'),
    },
    {
        key: 'name',
        sortable: true,
        label: __('Name'),
        class: 'w-3/12',
    },
    {
        key: 'users_count',
        label: __('Users'),
    },
    {
        key: 'is_sticky',
        label: __('Sticky'),
        sortable: true,
    },
    {
        key: 'sort_order',
        sortable: true,
        label: __('Sort Order'),
    },
    {
        key: 'created_at',
        label: __('Created'),
        sortable: true,
        class: 'w-1/12',
    },
    {
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'w-1/12 text-right',
    },
];
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Badges Administration')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />
        <div class="flex">
          <Button
            v-if="can('create badges')"
            as-child
          >
            <Link :href="route('admin.badge.create')">
              {{ __("Create Badge") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="badges"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
          >
            {{ item.id }}
          </td>
          <td class="px-4">
            <div class="flex-shrink-0">
              <img
                class="w-10 h-10"
                :src="item.photo_url"
                :alt="__('Badge Image')"
              >
            </div>
          </td>

          <td class="px-4 whitespace-nowrap">
            <div
              class="text-sm font-medium text-foreground dark:text-foreground"
            >
              {{ item.name }}
            </div>
            <div class="text-sm text-foreground dark:text-foreground">
              {{ item.shortname }}
            </div>
          </td>

          <DtRowItem>
            {{ item.users_count }}
          </DtRowItem>

          <td class="px-4">
            <Icon
              v-if="item.is_sticky"
              class="text-success-500"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
          </td>

          <DtRowItem>
            {{ item.sort_order }}
          </DtRowItem>

          <DtRowItem class="whitespace-nowrap">
            <span
              v-tippy
              :title="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>

          <td
            class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
          >
            <Link
              v-if="can('update badges')"
              v-tippy
              as="a"
              :href="route('admin.badge.edit', item.id)"
              class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
              :title="__('Edit Badge')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-if="can('delete badges')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this Badge permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.badge.delete', item.id)"
              class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
              :title="__('Delete Badge')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </Link>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
