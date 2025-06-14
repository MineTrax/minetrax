<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { CloudArrowDownIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';
import millify from 'millify';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    downloads: Object,
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
        key: 'name',
        sortable: true,
        label: __('Name'),
        filterable: {
            type: 'text',
        },
    },
    {
        key: 'is_active',
        label: __('Active'),
        sortable: true,
    },
    {
        key: 'is_external',
        label: __('External'),
        sortable: true,
    },
    {
        key: 'is_only_auth',
        label: __('Auth Only'),
        sortable: true,
    },
    {
        key: 'file_name',
        sortable: true,
        label: __('File Name'),
    },
    {
        key: 'file_size',
        sortable: false,
        label: __('File Size'),
    },
    {
        key: 'download_count',
        sortable: true,
        class: 'text-center',
        label: __('Downloads'),
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
    <app-head :title="__('Downloads Administration')" />

    <div class="px-10 py-8 mx-auto text-secondary-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-secondary-500 dark:text-secondary-300">
          {{ __("Downloads") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('create downloads')"
            :href="route('admin.download.create')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-surface-800 border border-transparent rounded-md hover:bg-surface-700 active:bg-surface-900 focus:outline-none focus:border-secondary-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Create") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Download") }}</span>
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-surface-800"
        :header="headerRow"
        :data="downloads"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-secondary-800 whitespace-nowrap dark:text-secondary-200"
          >
            {{ item.id }}
          </td>

          <DtRowItem>
            {{ item.name }}
          </DtRowItem>

          <td class="px-4">
            <Icon
              v-if="item.is_active"
              class="text-success-500"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
          </td>

          <td class="px-4">
            <Icon
              v-if="item.is_external"
              class="text-success-500 inline"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
            <Icon
              v-if="item.is_external_url_hidden"
              v-tippy
              :title="__('External URL is protected from end users.')"
              class="text-warning-400 inline"
              name="shield-check"
            />
          </td>

          <td class="px-4">
            <Icon
              v-if="item.is_only_auth"
              class="text-success-500 inline"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
            <span
              v-if="item.min_role_weight_required"
              v-tippy
              :title="__('Minimum Role Weight Required to Download')"
              class="text-xs"
            >({{ item.min_role_weight_required }})</span>
          </td>

          <DtRowItem>
            {{ item.is_external ? item.file_name : item.file.file_name }}
          </DtRowItem>

          <DtRowItem>
            <span
              v-if="item.is_external"
              class="italic text-secondary-400"
            >{{ __("Unknown") }}</span>
            <span v-else>
              {{ millify(item.file.size, {
                units: ["B", "KB", "MB", "GB", "TB"],
                space: true })
              }}
            </span>
          </DtRowItem>

          <DtRowItem class="text-center">
            {{ item.download_count }}
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
            <a
              v-tippy
              :href="route('download.download', item.slug)"
              target="_blank"
              class="inline-flex items-center justify-center text-primary-500 hover:text-primary-800"
              :title="__('Download')"
            >
              <CloudArrowDownIcon class="inline-block w-5 h-5" />
            </a>
            <InertiaLink
              v-if="can('update downloads')"
              v-tippy
              as="a"
              :href="route('admin.download.edit', item.id)"
              class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
              :title="__('Edit Download')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete downloads')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this Download permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.download.delete', item.id)"
              class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
              :title="__('Delete Download')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
