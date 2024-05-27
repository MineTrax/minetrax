<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { TrashIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    commandQueues: Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
    },
    {
        key: 'parsed_command',
        label: __('Command'),
        sortable: true,
        filterable: {
            type: 'text',
        },
    },
    {
        key: 'server_id',
        sortable: true,
        label: __('Server'),
        filterable: {
            key: 'server.name',
            type: 'text',
        }
    },
    {
        key: 'status',
        label: __('Status'),
        sortable: true,
        filterable: {
            type: 'multiselect',
            options: ['pending', 'running', 'failed', 'cancelled', 'deferred', 'completed'],
        },
    },
    {
        key: 'last_attempt_at',
        sortable: true,
        label: __('Last Attempted'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'execute_at',
        sortable: true,
        label: __('Execute At'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'tag',
        sortable: true,
        label: __('Tags'),
        filterable: {
            type: 'text',
        }
    },
    {
        key: 'player_id',
        sortable: true,
        label: __('For Player'),
        class: 'whitespace-nowrap',
        filterable: {
            key: 'player.username',
            type: 'text',
        }
    },
    {
        key: 'output',
        label: __('Output'),
        sortable: true,
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
    <app-head :title="__('Command History')" />

    <div class="px-10 py-8 mx-auto text-gray-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Command History") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('create command_queues')"
            v-confirm="{message: 'Are you sure you wanna retry all failed command queues? Please note that failed commands automatically get retried till they reach the max attempts.'}"
            :href="route('admin.command-queue.retry')"
            method="post"
            as="button"
            class="mr-2 inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-500 border border-transparent rounded-md hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:shadow-outline-gray"
          >
            {{ __("Retry All Failed") }}
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-gray-800"
        :header="headerRow"
        :data="commandQueues"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-gray-800 whitespace-nowrap dark:text-gray-200"
          >
            {{ item.id }}
          </td>

          <DtRowItem class="text-left font-mono">
            {{ item.parsed_command }}
          </DtRowItem>

          <DtRowItem class="text-left whitespace-nowrap">
            {{ item.server.name }}
          </DtRowItem>

          <DtRowItem class="text-left">
            <CommonStatusBadge :status="item.status.value" />
          </DtRowItem>

          <DtRowItem>
            <div
              v-if="item.last_attempt_at"
              v-tippy
              class="whitespace-nowrap"
              :title="formatToDayDateString(item.last_attempt_at)"
            >
              {{ formatTimeAgoToNow(item.last_attempt_at) }}
            </div>
            <div
              v-else
              class="text-gray-500 italic"
            >
              {{ "not yet" }}
            </div>
            <div class="text-xs dark:text-gray-500">
              {{ __("Attempts: :attempts/:max_attempts", {
                attempts: item.attempts,
                max_attempts: item.max_attempts ?? 1,
              }) }}
            </div>
          </DtRowItem>

          <DtRowItem>
            <div
              v-if="item.execute_at"
              v-tippy
              class="text-sm font-medium text-gray-900 dark:text-gray-300 whitespace-nowrap"
              :title="formatToDayDateString(item.execute_at)"
            >
              {{ formatTimeAgoToNow(item.execute_at) }}
            </div>
            <div
              v-else
              class="text-gray-500 italic"
            >
              {{ "no delay" }}
            </div>
          </DtRowItem>

          <DtRowItem class="text-left">
            {{ item.tag ?? __("none") }}
          </DtRowItem>

          <DtRowItem class="text-center">
            <inertia-link
              v-if="item.player_id"
              v-tippy
              as="a"
              :href="route('player.show', item.player.uuid)"
              class="text-sm font-medium text-gray-900 dark:text-gray-400 focus:outline-none cursor-pointer hover:underline"
              :content="item.player.uuid"
            >
              <span
                v-if="item.player.username"
                class="text-gray-600 dark:text-gray-400"
              >{{ item.player.username }}</span>
              <span
                v-else
                class="text-red-500 italic"
              >{{ __("Unknown") }}</span>
            </inertia-link>
            <div
              v-else
              class="italic text-gray-500"
            >
              {{ __("none") }}
            </div>
          </DtRowItem>

          <DtRowItem class="text-left">
            <span
              v-if="item.output"
              class="truncate"
              :title="item.output"
            >
              {{ item.output.length > 10 ? item.output.substring(0, 10) + '...' : item.output }}
            </span>
            <span
              v-else
              class="italic"
            >
              {{ __("none") }}
            </span>
          </DtRowItem>

          <td
            class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
          >
            <InertiaLink
              v-if="can('create command_queues') && ['failed', 'cancelled'].includes(item.status.value)"
              v-tippy
              as="button"
              method="post"
              :data="{ id: item.id }"
              :href="route('admin.command-queue.retry')"
              class="inline-flex items-center justify-center text-green-600 dark:text-green-500 hover:text-green-800 dark:hover:text-green-800"
              :title="__('Retry Command')"
            >
              <ArrowPathIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete command_queues') && ['pending', 'failed', 'cancelled', 'completed', 'deferred'].includes(item.status.value)"
              v-confirm="{
                message:
                  'Are you sure you want to delete this command history?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :data="{ id: item.id }"
              :href="route('admin.command-queue.delete')"
              class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
              :title="__('Delete Command')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
