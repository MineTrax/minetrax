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
} from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    customForms: Object,
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
        key: 'can_create_submission',
        label: __('Can Submit'),
        sortable: true,
    },
    {
        key: 'min_role_weight_to_view_submission',
        label: __('Role Weight to View Submission'),
        sortable: true,
        class: 'text-right whitespace-nowrap',
    },
    {
        key: 'is_notify_staff_on_submission',
        sortable: true,
        label: __('Notify Staff on Submit'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'is_visible_in_listing',
        sortable: true,
        label: __('Visible in Listing'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'submissions_count',
        sortable: true,
        label: __('Total Submissions'),
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
    <app-head :title="__('Manage Custom Forms')" />

    <div class="px-10 py-8 mx-auto text-gray-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Manage Custom Forms") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('create custom_forms')"
            :href="route('admin.custom-form.create')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Create") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Custom Form") }}</span>
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-gray-800"
        :header="headerRow"
        :data="customForms"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-gray-800 whitespace-nowrap dark:text-gray-200"
          >
            {{ item.id }}
          </td>

          <DtRowItem class="">
            {{ item.title }}
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

          <td class="px-4 whitespace-normal">
            <div class="flex items-center">
              <div
                class="text-sm font-medium text-gray-900 dark:text-gray-400"
              >
                {{ item.can_create_submission }}
              </div>
            </div>
          </td>

          <DtRowItem class="text-right">
            {{ item.min_role_weight_to_view_submission ?? '-' }}
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
              v-if="item.is_visible_in_listing"
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
            {{ item.submissions_count }}
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
              as="a"
              :href="route('custom-form.show', item.slug)"
              class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
            >
              <EyeIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('update custom_forms')"
              v-tippy
              as="a"
              :href="route('admin.custom-form.edit', item.id)"
              class="inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800"
              :title="__('Edit Custom Form')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete custom_forms')"
              v-confirm="{
                message:
                  'Deleting this Custom Form will also delete all its submissions. Are you sure you want to delete this form & its submissions permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.custom-form.delete', item.id)"
              class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
              :title="__('Delete Custom Form')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
