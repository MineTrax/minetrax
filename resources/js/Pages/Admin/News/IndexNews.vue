<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    newslist: Object,
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
        key: 'type',
        sortable: true,
        label: __('Type'),
    },
    {
        key: 'title',
        sortable: true,
        label: __('Title'),
    },
    {
        key: 'published_at',
        label: __('Published'),
        sortable: true,
    },
    {
        key: 'is_pinned',
        label: __('Pinned'),
        sortable: true,
    },
    {
        key: 'created_at',
        label: __('Created'),
        sortable: true,
        class: 'w-1/12',
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
    <app-head :title="__('Manage News')" />

    <div class="px-10 py-8 mx-auto text-gray-400">
      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Manage News") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('create news')"
            :href="route('admin.news.create')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Create News") }}</span>
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-gray-800"
        :header="headerRow"
        :data="newslist"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-gray-800 whitespace-nowrap dark:text-gray-200"
          >
            {{ item.id }}
          </td>
          <td class="px-4">
            <div class="text-sm text-gray-900">
              <span
                v-if="item.type.value === 0"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-light-blue-100 text-light-blue-800 dark:bg-light-blue-700 dark:bg-opacity-25 dark:text-light-blue-400"
              >{{ item.type.key }}</span>
              <span
                v-else-if="item.type.value === 1"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
              >{{ item.type.key }}</span>
              <span
                v-else-if="item.type.value === 2"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:bg-opacity-25 dark:text-green-400"
              >{{ item.type.key }}</span>
              <span
                v-else
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
              >{{ item.type.key }}</span>
            </div>
          </td>

          <td class="px-4 whitespace-nowrap">
            <div class="flex items-center">
              <div
                v-if="item.photo_url"
                class="flex-shrink-0 h-10 w-14"
              >
                <img
                  class="h-10 w-14"
                  :src="item.photo_url"
                  alt=""
                >
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                  {{ item.title }}
                </div>
              </div>
            </div>
          </td>

          <DtRowItem>
            <Icon
              v-if="item.published_at"
              v-tippy
              :content="formatTimeAgoToNow(item.published_at)"
              class="text-green-500 focus:outline-none"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-red-500"
              name="cross-circle"
            />
          </DtRowItem>

          <DtRowItem>
            <Icon
              v-if="item.is_pinned"
              class="text-green-500"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-red-500"
              name="cross-circle"
            />
          </DtRowItem>

          <DtRowItem>
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
              v-tippy
              as="a"
              :href="route('news.show', item.slug)"
              class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
              :title="__('View News')"
            >
              <EyeIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('update news')"
              v-tippy
              as="a"
              :href="route('admin.news.edit', item.id)"
              class="inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800"
              :title="__('Edit News')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete news')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this News permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.news.delete', item.id)"
              class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
              :title="__('Delete News')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
