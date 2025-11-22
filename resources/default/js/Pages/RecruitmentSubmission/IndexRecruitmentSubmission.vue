<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import {Link} from '@inertiajs/vue3';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { EyeIcon } from '@heroicons/vue/24/outline';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';

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

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Application Forms'),
        url: route('recruitment.index'),
        current: false
    },
    {
        text: __('My Application Requests'),
        url: route('recruitment-submission.index'),
        current: true
    }
];

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'text-left w-[5%]',
    },
    {
        key: 'recruitment_id',
        label: __('Application'),
        sortable: true,
    },
    {
        key: 'status',
        label: __('Status'),
        sortable: true,
        filterable: {
            type: 'multiselect',
            options: ['pending', 'inprogress', 'approved', 'rejected', 'withdrawn', 'onhold'],
        }
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
        label: '',
        sortable: false,
        class: 'w-1/12 text-right',
    },
];

</script>

<template>
  <AppLayout>
    <AppHead :title="__('My Application Requests')" />

    <AppBreadcrumb class="max-w-screen-2xl mx-auto" :items="breadcrumbItems" />

    <div class="py-4 px-2 md:py-4 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4">
        <DataTable
          class="rounded-lg border bg-card text-card-foreground shadow w-full"
          :header="headerRow"
          :data="submissions"
          :filters="filters"
        >
          <template #default="{ item }">
            <td
              class="text-sm px-4 font-medium text-left text-foreground whitespace-nowrap dark:text-foreground"
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
              <CommonStatusBadge :status="item.status.value" />
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
              <Button
                v-tippy
                as-child
                variant="outline"
                size="sm"
                :title="__('View Application')"
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
                >
                  <EyeIcon class="inline-block w-5 h-5" />
                </InertiaLink>
              </Button>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
