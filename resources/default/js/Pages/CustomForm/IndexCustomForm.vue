<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import {Link} from '@inertiajs/vue3';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import {EyeIcon} from '@heroicons/vue/24/outline';
import {UserIcon} from '@heroicons/vue/24/solid';
import Icon from '@/Components/Icon.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import { startCase } from 'lodash';
import { Button } from '@/Components/ui/button';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    forms: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Forms'),
        url: route('custom-form.index'),
        current: true
    }
];

const headerRow = [
    {
        key: 'title',
        label: __('Title'),
        sortable: true,
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
        sortable: true,
        class: 'w-1/12 hidden text-right md:table-cell',
        filterable: {
            type: 'multiselect',
            options: ['active', 'disabled']
        }
    },
    {
        key: 'created_at',
        label: __('Added'),
        sortable: true,
        class: 'w-1/12 hidden text-right md:table-cell',
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
    <AppHead :title="__('Custom Forms')" />

    <AppBreadcrumb class="max-w-screen-2xl mx-auto" :items="breadcrumbItems" />

    <div class="py-4 px-2 md:py-4 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4">
        <DataTable
          class="rounded-lg border bg-card text-card-foreground shadow w-full"
          :header="headerRow"
          :data="forms"
          :filters="filters"
        >
          <template #default="{ item }">
            <DtRowItem>
              <Link
                :href="route('custom-form.show', item.slug)"
                class="hover:text-primary hover:underline"
              >
                {{ item.title }}
              </Link>
            </DtRowItem>

            <DtRowItem class="text-right">
              <span
                v-tippy
                :title="__('Registered Users Only')"
              >
                <UserIcon
                  v-if="item.can_create_submission === 'auth'"
                  class="inline-block w-4 h-4 text-primary"
                />
              </span>
              <Icon
                v-if="item.can_create_submission === 'staff'"
                v-tippy
                name="shield-check-fill"
                :title="__('Staff Only')"
                class="inline mb-1 text-amber-400 h-4 fill-current focus:outline-none"
              />
            </DtRowItem>

            <DtRowItem class="text-right hidden md:table-cell">
              <CommonStatusBadge :status="item.status.value == 'active' ? 'green' : 'red'" :value="startCase(item.status.value)" />
            </DtRowItem>

            <DtRowItem class="whitespace-nowrap hidden md:table-cell text-right">
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
            <Button
                v-tippy
                as-child
                variant="outline"
                size="sm"
                :title="__('Show Details')"
              >
                <Link
                  as="a"
                  :href="
                    route('custom-form.show', item.slug)
                  "
                >
                  <EyeIcon class="inline-block w-5 h-5" />
                </Link>
              </Button>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
