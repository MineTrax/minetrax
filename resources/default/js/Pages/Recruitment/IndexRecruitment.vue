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
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import { startCase } from 'lodash';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    recruitments: Object,
    filters: Object,
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
        current: true
    }
];

const headerRow = [
    {
        key: 'title',
        sortable: true,
        label: __('Title'),
        filterable: {
            type: 'text',
        }
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
        class: 'w-1/12 hidden text-right md:table-cell whitespace-nowrap',
        sortable: true,
        filterable: {
            type: 'multiselect',
            options: ['active', 'disabled'],
        }
    },
    {
        key: 'updated_at',
        label: __('Last Updated'),
        sortable: true,
        class: 'w-1/12 hidden text-right md:table-cell whitespace-nowrap',
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
    <AppHead :title="__('Application Forms')" />

    <AppBreadcrumb class="max-w-screen-2xl mx-auto" :items="breadcrumbItems" />

    <div class="py-4 px-2 md:py-4 md:px-10 max-w-screen-2xl mx-auto">
      <div
        v-if="$page.props.auth.user"
        class="flex justify-end mb-6"
      >
        <div class="flex space-x-2">
          <Button
            v-if="$page.props.auth.user"
            as-child
          >
            <Link :href="route('recruitment-submission.index')">
              {{ __("View My Applications") }}
            </Link>
          </Button>
        </div>
      </div>
      <div class="flex flex-col md:flex-row md:space-x-4">
        <DataTable
          class="rounded-lg border bg-card text-card-foreground shadow w-full"
          :header="headerRow"
          :data="recruitments"
          :filters="filters"
        >
          <template #default="{ item }">
            <DtRowItem>
              <Link
                :href="route('recruitment.show', item.slug)"
                class="hover:text-primary hover:underline"
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
                class="inline mb-1 text-primary h-4 fill-current focus:outline-none"
              />
              <Icon
                v-if="item.is_allow_only_player_linked_users"
                v-tippy
                name="users"
                :title="__('Only for Linked Account Users (Player linked)')"
                class="inline mb-1 text-success-400 h-4 fill-current focus:outline-none"
              />
            </DtRowItem>

            <DtRowItem class="text-right hidden md:table-cell">
              <CommonStatusBadge :status="item.status.value == 'active' ? 'green' : 'red'" :value="startCase(item.status.value)" />
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
              <Button
                v-tippy
                as-child
                variant="outline"
                size="sm"
                :title="__('Apply')"
              >
                <Link
                  :href="route('recruitment.show', item.slug)"
                >
                  {{ __("Apply") }}
                </Link>
              </Button>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
