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
import {
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
    ChartBarSquareIcon,
} from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import { startCase } from 'lodash';

function getStatusColor(status) {
    switch (status) {
        case 'active':
            return 'green';
        case 'draft':
            return 'pending';
        case 'disabled':
            return 'red';
        case 'archived':
            return 'deferred';
        default:
            return status;
    }
}

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    recruitments: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Application Forms'),
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
        key: 'title',
        sortable: true,
        label: __('Title'),
        filterable: {
            type: 'text',
        },
    },
    {
        key: 'status',
        label: __('Status'),
        sortable: true,
        filterable: {
            type: 'multiselect',
            options: ['draft', 'active', 'disabled', 'archived'],
        }
    },
    {
        key: 'is_notify_staff_on_submission',
        sortable: true,
        label: __('Notify Staff'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'is_allow_messages_from_users',
        sortable: true,
        label: __('Messaging'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'open_submissions_count',
        sortable: true,
        label: __('Open Requests'),
        class: 'text-right whitespace-nowrap',
    },
    {
        key: 'closed_submissions_count',
        sortable: true,
        label: __('Closed Requests'),
        class: 'text-right whitespace-nowrap',
    },
    {
        key: 'created_at',
        sortable: true,
        label: __('Created'),
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
    <app-head :title="__('Manage Applications')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />
        <div class="flex">
          <Button
            v-if="can('create recruitments')"
            as-child
          >
            <Link :href="route('admin.recruitment.create')">
              {{ __("Create Application Form") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="recruitments"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
          >
            {{ item.id }}
          </td>

          <DtRowItem class="">
            <Link
              as="a"
              :href="route('admin.recruitment.show', item.id)"
              class="dark:hover:text-foreground hover:text-primary"
            >
              {{ item.title }}
            </Link>
          </DtRowItem>

          <DtRowItem>
            <CommonStatusBadge :status="getStatusColor(item.status.value)" :value="startCase(item.status.value)" />
          </DtRowItem>

          <td
            class="py-4 text-sm text-center text-foreground align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_notify_staff_on_submission"
              class="text-success-500 focus:outline-none"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
          </td>

          <td
            class="py-4 text-sm text-center text-foreground align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_allow_messages_from_users"
              class="text-success-500 focus:outline-none"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
          </td>

          <DtRowItem class="text-right">
            {{ item.open_submissions_count }}
          </DtRowItem>

          <DtRowItem class="text-right">
            {{ item.closed_submissions_count }}
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
              v-if="['active', 'disabled'].includes(item.status.value)"
              v-tippy
              as="a"
              :href="route('recruitment.show', item.slug)"
              class="inline-flex items-center justify-center text-primary hover:text-primary"
              :title="__('Show Public View')"
            >
              <EyeIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-tippy
              as="a"
              :href="route('admin.recruitment.show', item.id)"
              class="inline-flex items-center justify-center text-success-500 hover:text-success-800"
              :title="__('Show Intel')"
            >
              <ChartBarSquareIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-if="can('update recruitments')"
              v-tippy
              as="a"
              :href="route('admin.recruitment.edit', item.id)"
              class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
              :title="__('Edit Application Form')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-if="can('delete recruitments')"
              v-confirm="{
                message:
                  'Deleting this Application Form will also delete all its requests. Are you sure you want to delete this application form & its requests permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.recruitment.delete', item.id)"
              class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
              :title="__('Delete Application Form')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </Link>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
