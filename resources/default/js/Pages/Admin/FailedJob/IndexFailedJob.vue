<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { TrashIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    jobs: Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
    },
    {
        key: 'uuid',
        label: __('UUID'),
        sortable: true,
    },
    {
        key: 'job',
        sortable: false,
        label: __('Job'),
    },
    {
        key: 'connection',
        sortable: true,
        label: __('Connection/Queue'),
    },
    {
        key: 'exception',
        label: __('Exception'),
        sortable: true,
    },
    {
        key: 'failed_at',
        sortable: true,
        label: __('Failed At'),
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
    <app-head :title="__('Failed Jobs')" />

    <div class="px-10 py-8 mx-auto text-secondary-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-secondary-500 dark:text-secondary-300">
          {{ __("Failed Jobs") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('retry failed_jobs')"
            :href="route('admin.failed-job.retry')"
            method="post"
            class="mr-2 inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-success-500 border border-transparent rounded-md hover:bg-success-700 active:bg-success-900 focus:outline-none focus:border-success-900 focus:shadow-outline-gray"
          >
            {{ __("Retry All Jobs") }}
          </InertiaLink>
          <InertiaLink
            v-if="can('delete failed_jobs')"
            v-confirm="{message: 'Are you sure you want to delete all failed jobs?'}"
            method="delete"
            as="button"
            :href="route('admin.failed-job.clear')"
            class="inline-flex items-center px-4 py-2 bg-error-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-error-700 active:bg-error-900 focus:outline-none focus:border-error-900 focus:shadow-outline-red transition ease-in-out duration-150"
          >
            {{ __("Clear All Jobs") }}
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-surface-800"
        :header="headerRow"
        :data="jobs"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-secondary-800 whitespace-nowrap dark:text-secondary-200"
          >
            {{ item.id }}
          </td>

          <DtRowItem class="text-left">
            {{ item.uuid }}
          </DtRowItem>

          <td class="px-4 whitespace-nowrap">
            <div>
              <div class="text-sm font-medium text-secondary-900 dark:text-secondary-300">
                {{ item.payload.displayName }}
              </div>
              <div class="text-sm text-secondary-500 dark:text-secondary-400">
                {{ __("Attempts: :attempts", {
                  attempts: item.payload.attempts,
                }) }}
              </div>
            </div>
          </td>

          <DtRowItem class="text-left">
            {{ item.connection + '/' + item.queue }}
          </DtRowItem>

          <DtRowItem class="text-left">
            <span
              class="truncate"
              :title="item.exception"
            >
              {{ item.exception.substring(0, 30) + '...' }}
            </span>
          </DtRowItem>

          <DtRowItem>
            <span
              v-tippy
              :title="formatToDayDateString(item.failed_at)"
            >
              {{ formatTimeAgoToNow(item.failed_at) }}
            </span>
          </DtRowItem>

          <td
            class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
          >
            <InertiaLink
              v-if="can('retry failed_jobs')"
              v-tippy
              as="button"
              method="post"
              :data="{ uuid: item.uuid }"
              :href="route('admin.failed-job.retry')"
              class="inline-flex items-center justify-center text-success-600 dark:text-success-500 hover:text-success-800 dark:hover:text-success-800"
              :title="__('Retry Job')"
            >
              <ArrowPathIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete failed_jobs')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this Job permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :data="{ uuid: item.uuid }"
              :href="route('admin.failed-job.clear')"
              class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
              :title="__('Delete Job')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
