<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Link } from '@inertiajs/vue3';
import { EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    newslist: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('News'),
        current: true,
    }
];

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

    <div class="px-10 py-8 mx-auto text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />
        <div class="flex">
          <Button
            v-if="can('create news')"
            as-child
          >
            <Link :href="route('admin.news.create')">
              {{ __("Create News") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="newslist"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-center text-foreground whitespace-nowrap dark:text-foreground"
          >
            {{ item.id }}
          </td>
          <td class="px-4">
            <Badge
              v-if="item.type.value === 0"
              variant="outline"
              class="bg-primary/10 text-primary"
            >
              {{ item.type.key }}
            </Badge>
            <Badge
              v-else-if="item.type.value === 1"
              variant="outline"
              class="bg-orange-500/10 text-orange-500"
            >
              {{ item.type.key }}
            </Badge>
            <Badge
              v-else-if="item.type.value === 2"
              variant="outline"
              class="bg-success-500/10 text-success-500"
            >
              {{ item.type.key }}
            </Badge>
            <Badge
              v-else
              variant="outline"
            >
              {{ item.type.key }}
            </Badge>
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
                <div class="text-sm font-medium text-foreground dark:text-foreground">
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
              class="text-success-500 focus:outline-none"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
              name="cross-circle"
            />
          </DtRowItem>

          <DtRowItem>
            <Icon
              v-if="item.is_pinned"
              class="text-success-500"
              name="check-circle"
            />
            <Icon
              v-else
              class="text-error-500"
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
            <Link
              v-tippy
              as="a"
              :href="route('news.show', item.slug)"
              class="inline-flex items-center justify-center text-primary hover:text-primary"
              :title="__('View News')"
            >
              <EyeIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-if="can('update news')"
              v-tippy
              as="a"
              :href="route('admin.news.edit', item.id)"
              class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
              :title="__('Edit News')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </Link>
            <Link
              v-if="can('delete news')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this News permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.news.delete', item.id)"
              class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
              :title="__('Delete News')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </Link>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
