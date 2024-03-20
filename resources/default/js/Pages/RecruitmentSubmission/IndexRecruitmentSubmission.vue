<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import {Link} from '@inertiajs/vue3';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { EyeIcon } from '@heroicons/vue/24/outline';
import RecruitmentStatusBadge from '@/Shared/RecruitmentStatusBadge.vue';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    filters: {
        type: Object,
    },
    submissions: {
        type: Object,
    },
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'text-left w-[5%]',
    },
    {
        key: 'recruitment_id',
        label: __('Recruitment'),
        sortable: true,
    },
    {
        key: 'status',
        label: __('Status'),
        sortable: true,
    },
    {
        key: 'created_at',
        label: __('Created At'),
        class: 'text-right w-1/12 whitespace-nowrap',
        sortable: true,
    },
    {
        key: 'updated_at',
        label: __('Last Updated At'),
        class: 'text-right w-1/12 whitespace-nowrap',
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
  <AppLayout>
    <AppHead :title="__('My Recruitment Applications')" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-lg md:text-3xl text-gray-500 dark:text-gray-300">
          {{ __("My Recruitment Applications") }}
        </h1>
        <div class="flex space-x-2">
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
          :data="submissions"
          :filters="filters"
        >
          <template #default="{ item }">
            <td
              class="text-sm px-4 font-medium text-left text-gray-800 whitespace-nowrap dark:text-gray-200"
            >
              <InertiaLink
                as="a"
                class="hover:underline"
                :href="route('recruitment-submission.show', {
                  submission: item.id,
                  recruitment: item.recruitment.slug
                })"
              >
                {{ item.id }}
              </InertiaLink>
            </td>

            <DtRowItem>
              {{ item.recruitment.title }}
            </DtRowItem>

            <DtRowItem>
              <RecruitmentStatusBadge :status="item.status.value" />
            </DtRowItem>

            <DtRowItem
              v-tippy
              class="text-right whitespace-nowrap"
              :content="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </DtRowItem>

            <DtRowItem
              v-tippy
              class="text-right whitespace-nowrap"
              :content="formatToDayDateString(item.updated_at)"
            >
              {{ formatTimeAgoToNow(item.updated_at) }}
            </DtRowItem>

            <td
              class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
            >
              <InertiaLink
                as="a"
                :href="
                  route(
                    'recruitment-submission.show',
                    {
                      recruitment: item.recruitment.slug,
                      submission: item.id,
                    }
                  )
                "
                class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
              >
                <EyeIcon class="inline-block w-5 h-5" />
              </InertiaLink>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
