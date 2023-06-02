<template>
  <AdminLayout>
    <app-head
      :title="__('Users Administration')"
    />

    <div class="px-10 py-12 mx-auto max-w-7xl text-gray-400">
      <div class="flex justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Users") }}
        </h1>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-gray-800"
        :header="headerRow"
        :data="users"
        :filters="filters"
      >
        <template #default="{item}">
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">
            {{ item.id }}
          </td>
          <DtRowItem>
            {{ item.username }}
          </DtRowItem>
          <DtRowItem>
            {{ item.name }}
          </DtRowItem>
          <DtRowItem>
            {{ item.email }}
          </DtRowItem>
          <DtRowItem>
            <span
              v-tippy
              :title="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>
          <DtRowItem>
            {{ item.roles[0].display_name }}
          </DtRowItem>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <a
              class="text-blue-500 hover:text-blue-700"
              href="#"
            >Delete</a>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    users: Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'w-1/12',
    },
    {
        key: 'username',
        label: __('Username'),
        sortable: true,
        class: 'w-2/12',
    },
    {
        key: 'name',
        label: __('Name'),
        sortable: true,
        class: 'w-3/12',
    },
    {
        key: 'email',
        label: __('Email'),
        sortable: true,
        class: 'w-2/12',
    },
    {
        key: 'created_at',
        label: __('Joined'),
        sortable: true,
        class: 'w-2/12',
    },
    {
        key: 'role_id',
        label: __('Role'),
        sortable: false,
        class: 'w-2/12',
    },
    {
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'w-1/12',
    }
];

</script>
