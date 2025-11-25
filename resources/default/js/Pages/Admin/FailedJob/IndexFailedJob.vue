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
import { TrashIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    jobs: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Failed Jobs'),
        current: true,
    }
];

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

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />
        <div class="flex gap-2">
          <Button
            v-if="can('retry failed_jobs')"
            variant="default"
            as-child
          >
            <Link
              :href="route('admin.failed-job.retry')"
              method="post"
            >
              {{ __("Retry All Jobs") }}
            </Link>
          </Button>
          <Button
            v-if="can('delete failed_jobs')"
            variant="destructive"
            as-child
          >
            <Link
              v-confirm="{message: 'Are you sure you want to delete all failed jobs?'}"
              method="delete"
              as="button"
              :href="route('admin.failed-job.clear')"
            >
              {{ __("Clear All Jobs") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="jobs"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap dark:text-foreground"
          >
            {{ item.id }}
          </td>

          <DtRowItem class="text-left">
            {{ item.uuid }}
          </DtRowItem>

          <td class="px-4 whitespace-nowrap">
            <div>
              <div class="text-sm font-medium text-foreground dark:text-foreground">
                {{ item.payload.displayName }}
              </div>
              <div class="text-sm text-foreground dark:text-foreground">
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
            <Link
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
            </Link>
            <Link
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
            </Link>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
