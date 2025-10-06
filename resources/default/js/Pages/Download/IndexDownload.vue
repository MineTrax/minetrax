<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import {Link} from '@inertiajs/vue3';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import {CloudArrowDownIcon, EyeIcon} from '@heroicons/vue/24/outline';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';

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
        filterable: {
            type: 'text',
        },
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

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Downloads'),
        url: route('download.index'),
        current: true
    }
];
</script>

<template>
  <AppLayout>
    <AppHead :title="__('Downloads')" />

    <AppBreadcrumb class="max-w-screen-2xl mx-auto" :items="breadcrumbItems" />

    <div class="px-2 py-4 mx-auto md:py-4 md:px-10  max-w-screen-2xl ">
      <div class="flex flex-col md:flex-row md:space-x-4">
        <DataTable
          class="rounded-lg border bg-card text-card-foreground shadow w-full"
          :header="headerRow"
          :data="downloads"
          :filters="filters"
        >
          <template #default="{ item }">
            <DtRowItem>
              <Link
                :href="route('download.show', item.slug)"
                class="hover:text-primary hover:underline"
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
                class="inline-flex items-center justify-center text-primary hover:text-primary"
                :title="__('Download')"
              >
                <CloudArrowDownIcon class="inline-block w-5 h-5" />
              </a>
              <Link
                v-tippy
                as="a"
                :href="route('download.show', item.slug)"
                class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
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
