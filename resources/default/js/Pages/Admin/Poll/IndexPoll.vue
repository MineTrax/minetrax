<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Link } from '@inertiajs/vue3';
import { LockClosedIcon, LockOpenIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    polls: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Polls'),
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
        key: 'question',
        sortable: true,
        label: __('Question'),
    },
    {
        key: 'options',
        sortable: false,
        label: __('Options'),
    },
    {
        key: 'started_at',
        sortable: true,
        label: __('Started At'),
    },
    {
        key: 'closed_at',
        label: __('End At'),
        sortable: true,
    },
    {
        key: 'created_at',
        label: __('Created At'),
        sortable: true,
    },
    {
        key: 'created_by',
        label: __('Created By'),
        sortable: true,
    },
    {
        key: 'is_closed',
        sortable: true,
        label: __('Locked'),
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
    <app-head :title="__('Manage Polls')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />
        <div class="flex">
          <Button
            v-if="can('create polls')"
            as-child
          >
            <Link :href="route('admin.poll.create')">
              {{ __("Create Poll") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="polls"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
          >
            {{ item.id }}
          </td>

          <DtRowItem>
            <div class="text-sm font-medium text-foreground dark:text-foreground">
              {{ item.question }}
            </div>
          </DtRowItem>

          <td class="px-4 py-3 space-x-1 space-y-1 text-sm">
            <template v-if="item.options.length > 0">
              <Badge
                v-for="option in item.options"
                :key="option.id"
                variant="outline"
              >
                {{ option.name }}
              </Badge>
            </template>
            <span
              v-else
              class="italic text-foreground"
            >{{ __("No options.") }}</span>
          </td>

          <DtRowItem>
            <span
              v-tippy
              class="focus:outline-none"
              :content="formatToDayDateString(item.started_at)"
            >
              {{ formatTimeAgoToNow(item.started_at) }}
            </span>
          </DtRowItem>


          <DtRowItem>
            <span
              v-if="item.closed_at"
              v-tippy
              class="focus:outline-none"
              :content="formatToDayDateString(item.closed_at)"
            >
              {{ formatTimeAgoToNow(item.closed_at) }}
            </span>
            <span
              v-else
              class="italic"
            >{{ __("None") }}</span>
          </DtRowItem>


          <DtRowItem>
            <span
              v-tippy
              class="focus:outline-none"
              :content="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>

          <DtRowItem>
            <span v-if="item.creator">{{ item.creator.username }}</span>
            <span
              v-else
              class="italic text-error-600"
            >{{ __("None") }}</span>
          </DtRowItem>

          <DtRowItem>
            <Icon
              v-if="item.is_closed"
              class="text-success-500"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
          </DtRowItem>

          <td
            class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
          >
            <Link
              v-if="can('update polls') && !item.is_closed"
              v-tippy
              as="button"
              method="put"
              :href="route('admin.poll.lock', item.id)"
              class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
              :title="__('Lock Poll')"
            >
              <LockClosedIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-if="can('update polls') && item.is_closed"
              v-tippy
              as="button"
              method="put"
              :href="route('admin.poll.unlock', item.id)"
              class="inline-flex items-center justify-center text-success-600 dark:text-success-500 hover:text-success-800 dark:hover:text-success-800"
              :title="__('Unlock Poll')"
            >
              <LockOpenIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-if="can('delete polls')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this Poll permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.poll.delete', item.id)"
              class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
              :title="__('Delete Poll')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </Link>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
