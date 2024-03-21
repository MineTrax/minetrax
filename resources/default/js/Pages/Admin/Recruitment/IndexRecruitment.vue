<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import {
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
    ChartBarSquareIcon,
} from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    recruitments: Object,
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
        key: 'title',
        sortable: true,
        label: __('Title'),
    },
    {
        key: 'status',
        label: __('Status'),
        sortable: true,
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
    <app-head :title="__('Manage Recruitment Forms')" />

    <div class="px-10 py-8 mx-auto text-gray-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Manage Recruitment Forms") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('create recruitments')"
            :href="route('admin.recruitment.create')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Create") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Recruitment") }}</span>
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-gray-800"
        :header="headerRow"
        :data="recruitments"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-gray-800 whitespace-nowrap dark:text-gray-200"
          >
            {{ item.id }}
          </td>

          <DtRowItem class="">
            <InertiaLink
              as="a"
              :href="route('admin.recruitment.show', item.id)"
              class="dark:hover:text-gray-200 hover:text-light-blue-400"
            >
              {{ item.title }}
            </InertiaLink>
          </DtRowItem>

          <DtRowItem class="px-4 whitespace-normal">
            <div class="flex items-center">
              <div
                class="text-sm font-medium text-gray-900 dark:text-gray-400"
              >
                {{ item.status.value }}
              </div>
            </div>
          </DtRowItem>

          <td
            class="py-4 text-sm text-center text-gray-500 align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_notify_staff_on_submission"
              class="text-green-500 focus:outline-none"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-red-500"
              name="cross-circle"
            />
          </td>

          <td
            class="py-4 text-sm text-center text-gray-500 align-middle px-9 whitespace-nowrap"
          >
            <Icon
              v-if="item.is_allow_messages_from_users"
              class="text-green-500 focus:outline-none"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-red-500"
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
            <InertiaLink
              v-if="['active', 'disabled'].includes(item.status.value)"
              v-tippy
              as="a"
              :href="route('recruitment.show', item.slug)"
              class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
              :title="__('Show Public View')"
            >
              <EyeIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-tippy
              as="a"
              :href="route('admin.recruitment.show', item.id)"
              class="inline-flex items-center justify-center text-green-500 hover:text-green-800"
              :title="__('Show Intel')"
            >
              <ChartBarSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('update recruitments')"
              v-tippy
              as="a"
              :href="route('admin.recruitment.edit', item.id)"
              class="inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800"
              :title="__('Edit Recruitment Form')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete recruitments')"
              v-confirm="{
                message:
                  'Deleting this Recruitment will also delete all its applications. Are you sure you want to delete this recruitment form & its applications permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.recruitment.delete', item.id)"
              class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
              :title="__('Delete Recruitment')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
