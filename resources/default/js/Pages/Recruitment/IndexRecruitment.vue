<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import {Link} from '@inertiajs/vue3';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import {DocumentCheckIcon} from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    recruitments: Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'title',
        sortable: true,
        label: __('Title'),
    },
    {
        key: 'flags',
        sortable: false,
        label: '',
        class: 'w-1/12 text-right',
    },
    {
        key: 'status',
        label: __('Status'),
        sortable: true,
        class: 'w-1/12 hidden text-right md:table-cell whitespace-nowrap',
    },
    {
        key: 'updated_at',
        label: __('Last Updated'),
        sortable: true,
        class: 'w-1/12 hidden text-right md:table-cell whitespace-nowrap',
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
    <AppHead :title="__('Recruitments')" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Recruitments") }}
        </h1>
        <div class="flex space-x-2">
          <Link
            v-if="$page.props.auth.user"
            :href="route('recruitment-submission.index')"
            class="inline-flex items-center px-4 py-2 bg-green-400 dark:bg-green-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-600 focus:outline-none focus:border-green-500 focus:shadow-outline-green transition ease-in-out duration-150"
          >
            <span>{{ __("View My Applications") }}</span>
          </Link>
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
          :data="recruitments"
          :filters="filters"
        >
          <template #default="{ item }">
            <DtRowItem>
              <Link
                :href="route('recruitment.show', item.slug)"
                class="hover:text-light-blue-400 hover:underline"
              >
                {{ item.title }}
              </Link>
            </DtRowItem>

            <DtRowItem class="text-right space-x-1">
              <Icon
                v-if="item.is_allow_only_verified_users"
                v-tippy
                name="verified-check-fill"
                :title="__('Only for Verified Users')"
                class="inline mb-1 text-sky-400 h-4 fill-current focus:outline-none"
              />
              <Icon
                v-if="item.is_allow_only_player_linked_users"
                v-tippy
                name="users"
                :title="__('Only for Linked Account Users (Player linked)')"
                class="inline mb-1 text-green-400 h-4 fill-current focus:outline-none"
              />
            </DtRowItem>

            <DtRowItem class="text-right hidden md:table-cell">
              {{ item.status.value }}
            </DtRowItem>

            <DtRowItem class="whitespace-nowrap hidden md:table-cell text-right">
              <span
                v-tippy
                :title="formatToDayDateString(item.updated_at)"
              >
                {{ formatTimeAgoToNow(item.updated_at) }}
              </span>
            </DtRowItem>

            <td
              class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
            >
              <Link
                v-tippy
                as="a"
                :href="route('recruitment.show', item.slug)"
                class="inline-flex items-center justify-center text-green-600 dark:text-green-500 hover:text-green-800 dark:hover:text-green-800"
                :title="__('Apply')"
              >
                <DocumentCheckIcon class="inline-block w-5 h-5" />
              </Link>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
