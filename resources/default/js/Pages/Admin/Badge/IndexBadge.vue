<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    badges: Object,
    filters: Object,
});

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

    <div class="px-10 py-8 mx-auto text-secondary-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-secondary-500 dark:text-secondary-300">
          {{ __("User Badges") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('create badges')"
            :href="route('admin.badge.create')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-surface-800 border border-transparent rounded-md hover:bg-surface-700 active:bg-surface-900 focus:outline-none focus:border-secondary-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Create New") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Badge") }}</span>
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-surface-800"
        :header="headerRow"
        :data="badges"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-secondary-800 whitespace-nowrap dark:text-secondary-200"
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
              class="text-sm font-medium text-secondary-900 dark:text-secondary-300"
            >
              {{ item.name }}
            </div>
            <div class="text-sm text-secondary-500 dark:text-secondary-400">
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
            <InertiaLink
              v-if="can('update badges')"
              v-tippy
              as="a"
              :href="route('admin.badge.edit', item.id)"
              class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
              :title="__('Edit Badge')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
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
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
