<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    chatHistory: {
        type: Object,
    }
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'text-left',
    },
    {
        key: 'data',
        label: __('Data'),
        sortable: false,
    },
    {
        key: 'type',
        label: __('Type'),
        sortable: true,
    },
    {
        key: 'server_id',
        label: __('Server'),
        sortable: true,
    },
    {
        key: 'created_at',
        label: __('Created'),
        sortable: true,
    },
];
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Chatlog - ServerIntel')" />

    <div class="p-4 mx-auto space-y-4 px-10">
      <ServerIntelServerSelector
        :title="__('Chatlog')"
        :server-list="serverList"
        :filters="filters"
      />

      <div>
        <DataTable
          class="bg-white rounded shadow dark:bg-gray-800"
          :header="headerRow"
          :data="chatHistory"
          :filters="filters"
        >
          <template #default="{ item }">
            <td
              class="px-4 py-4 text-sm font-medium text-gray-800 whitespace-nowrap dark:text-gray-200"
            >
              {{ item.id }}
            </td>

            <td class="px-4 invert dark:invert-0">
              <div class="text-sm text-gray-200">
                <p
                  v-html="item.data"
                />
                <p
                  v-if="item.causer_uuid"
                  class="text-xs text-gray-500 italic"
                >
                  {{ __("Causer") }}: {{ item.causer_uuid }} ({{ item.causer_username }})
                </p>
              </div>
            </td>

            <DtRowItem>
              {{ item.type }}
            </DtRowItem>

            <DtRowItem>
              <span
                v-tippy
                :title="`Server ID: ${item.server.id}`"
              >
                {{ item.server.name }}
              </span>
            </DtRowItem>

            <DtRowItem>
              <span
                v-tippy
                :title="formatToDayDateString(item.created_at)"
              >
                {{ formatTimeAgoToNow(item.created_at) }}
              </span>
            </DtRowItem>
          </template>
        </DataTable>
      </div>
    </div>
  </AdminLayout>
</template>

