<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import {Link} from '@inertiajs/vue3';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import {CloudArrowDownIcon, EyeIcon} from '@heroicons/vue/24/outline';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    downloads: Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'name',
        sortable: true,
        label: __('Name'),
    },
    {
        key: 'download_count',
        label: __('Download Count'),
        sortable: true,
        class: 'text-center hidden md:table-cell'
    },
    {
        key: 'created_at',
        label: __('Added'),
        sortable: true,
        class: 'w-1/12 hidden md:table-cell',
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
  <AppLayout>
    <AppHead :title="__('Downloads')" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Downloads") }}
        </h1>
        <div class="flex">
          <Link
            :href="route('home')"
            class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-cool-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Homepage") }}</span>
          </Link>
        </div>
      </div>
      <div class="flex flex-col md:flex-row md:space-x-4">
        <DataTable
          class="bg-white rounded shadow dark:bg-gray-800 w-full"
          :header="headerRow"
          :data="downloads"
          :filters="filters"
        >
          <template #default="{ item }">
            <DtRowItem>
              <Link
                :href="route('download.show', item.slug)"
                class="hover:text-light-blue-400 hover:underline"
              >
                {{ item.name }}
              </Link>
            </DtRowItem>

            <DtRowItem class="text-center hidden md:table-cell">
              {{ item.download_count }}
            </DtRowItem>

            <DtRowItem class="whitespace-nowrap hidden md:table-cell">
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
              <a
                v-tippy
                :href="route('download.download', item.slug)"
                target="_blank"
                class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
                :title="__('Download')"
              >
                <CloudArrowDownIcon class="inline-block w-5 h-5" />
              </a>
              <Link
                v-tippy
                as="a"
                :href="route('download.show', item.slug)"
                class="inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800"
                :title="__('Show Details')"
              >
                <EyeIcon class="inline-block w-5 h-5" />
              </Link>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
