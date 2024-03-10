<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { LockClosedIcon, LockOpenIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    polls: Object,
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

    <div class="px-10 py-8 mx-auto text-gray-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Manage Polls") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('create polls')"
            :href="route('admin.poll.create')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Create New") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Poll") }}</span>
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-gray-800"
        :header="headerRow"
        :data="polls"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-gray-800 whitespace-nowrap dark:text-gray-200"
          >
            {{ item.id }}
          </td>

          <DtRowItem>
            <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
              {{ item.question }}
            </div>
          </DtRowItem>

          <td class="px-4 text-sm">
            <template v-if="item.options.length > 0">
              <span
                v-for="option in item.options"
                :key="option.id"
                class="px-2 mr-1 mb-1 inline-flex text-xs leading-5 font-semibold rounded bg-gray-100 text-gray-800 dark:bg-cool-gray-700 dark:text-gray-300"
              >{{ option.name }}</span>
            </template>
            <span
              v-else
              class="italic text-gray-500"
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
              class="italic text-red-600"
            >{{ __("None") }}</span>
          </DtRowItem>

          <DtRowItem>
            <Icon
              v-if="item.is_closed"
              class="text-green-500"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-red-500"
              name="cross-circle"
            />
          </DtRowItem>

          <td
            class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
          >
            <InertiaLink
              v-if="can('update polls') && !item.is_closed"
              v-tippy
              as="button"
              method="put"
              :href="route('admin.poll.lock', item.id)"
              class="inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800"
              :title="__('Lock Poll')"
            >
              <LockClosedIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('update polls') && item.is_closed"
              v-tippy
              as="button"
              method="put"
              :href="route('admin.poll.unlock', item.id)"
              class="inline-flex items-center justify-center text-green-600 dark:text-green-500 hover:text-green-800 dark:hover:text-green-800"
              :title="__('Unlock Poll')"
            >
              <LockOpenIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete polls')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this Poll permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.poll.delete', item.id)"
              class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
              :title="__('Delete Poll')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
