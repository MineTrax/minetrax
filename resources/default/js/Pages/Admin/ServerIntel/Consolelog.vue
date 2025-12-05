<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const props = defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    consoleHistory: {
        type: Object,
    }
});

let selectedServers = props.filters?.servers?.length ? props.filters?.servers[0] : null;
const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Server Intel'),
        current: false,
    },
    {
        text: __('Consolelog'),
        current: true,
    },
    {
        text: props.serverList[selectedServers] ?? __('All Servers'),
        current: true,
    }
];

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
    <AppHead :title="__('ConsoleLog - ServerIntel')" />

    <div class="px-10 py-8 mx-auto space-y-4">
      <div class="flex justify-between items-center">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <ServerIntelServerSelector
          :server-list="serverList"
          :filters="filters"
        />
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="consoleHistory"
        :filters="filters"
      >
        <template #default="{ item }">
          <td class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap">
            {{ item.id }}
          </td>

          <td class="px-4">
            <div class="text-sm text-foreground">
              <pre class="whitespace-pre-wrap font-mono text-xs bg-muted p-2 rounded">{{ item.data }}</pre>
            </div>
          </td>

          <DtRowItem>
            <span
              v-tippy
              class="whitespace-nowrap"
              :title="`Server ID: ${item.server.id}`"
            >
              {{ item.server.name }}
            </span>
          </DtRowItem>

          <DtRowItem>
            <span
              v-tippy
              class="whitespace-nowrap"
              :title="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
