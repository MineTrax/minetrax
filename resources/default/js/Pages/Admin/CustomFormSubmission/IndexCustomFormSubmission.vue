<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import {
    EyeIcon,
    TrashIcon,
    ArchiveBoxArrowDownIcon,
    ArrowUturnUpIcon,
} from '@heroicons/vue/24/outline';
import XSelect from '@/Components/Form/XSelect.vue';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { pickBy } from 'lodash';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } =
    useHelpers();

const props = defineProps({
    forms: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    submissions: {
        type: Object,
    },
    archived: {
        type: Boolean,
        default: false,
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
        key: 'country_id',
        label: __('Country'),
        sortable: true,
        class: 'text-left w-1/12',
    },
    {
        key: 'user_id',
        sortable: true,
        label: __('User'),
        class: 'w-3/12',
    },
    {
        key: 'custom_form_id',
        label: __('Custom Form'),
        sortable: true,
    },
    {
        key: 'created_at',
        label: __('Created At'),
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

if (props.archived) {
    headerRow.splice(5, 0, {
        key: 'deleted_at',
        label: __('Archived At'),
        class: 'text-right w-1/12 whitespace-nowrap',
        sortable: true,
    });
}

// Form Selector
let selectedForms = ref(
    props.filters?.forms?.length ? props.filters?.forms[0] : null
);

const showing = computed(() => {
    if (props.filters.forms && props.filters.forms.length > 0) {
        return props.filters.forms
            .map((id) => {
                return props.forms[id];
            })
            .join(', ');
    }
    return null;
});

watch(selectedForms, (newSelectedForms) => {
    const query = {
        forms: newSelectedForms ? [newSelectedForms] : null,
    };

    router.get(route(route().current()), pickBy(query));
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="archived ? __('Archived Custom Form Submissions') : __('Custom Form Submissions')" />

    <div class="p-4 mx-auto space-y-4 px-10">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-extrabold text-gray-800 dark:text-gray-200">
          {{ archived ? __("Archived Form Submissions:") : __("Form Submissions:") }}
          {{ showing ?? __("All Forms") }}
        </h3>

        <x-select
          id="selectForms"
          v-model="selectedForms"
          name="selectForms"
          :select-list="forms"
          :placeholder="__('All Forms')"
          class="w-48 max-w-48 dark:border dark:rounded dark:border-gray-700"
        />
      </div>

      <div>
        <DataTable
          class="bg-white rounded shadow dark:bg-gray-800"
          :header="headerRow"
          :data="submissions"
          :filters="filters"
        >
          <template #default="{ item }">
            <td
              class="text-sm px-4 font-medium text-left text-gray-800 whitespace-nowrap dark:text-gray-200"
            >
              {{ item.id }}
            </td>
            <td
              class="px-4 py-4 text-sm font-medium text-gray-800 whitespace-nowrap dark:text-gray-200"
            >
              <div class="flex items-center">
                <div
                  v-if="item.country"
                  v-tippy
                  class="flex-shrink-0 h-10 w-10 focus:outline-none"
                  :content="item.country.name"
                >
                  <img
                    class="h-10 w-10"
                    :src="item.country.photo_path"
                    alt=""
                  >
                </div>
              </div>
            </td>

            <td class="px-4">
              <InertiaLink
                v-if="item.user"
                :href="route('user.public.get', item.user.username)"
                class="flex items-center"
              >
                <div class="flex-shrink-0 h-10 w-10 mr-2">
                  <img
                    class="h-10 w-10 rounded-full"
                    :src="item.user.profile_photo_url"
                    alt="Avatar"
                  >
                </div>
                <div class="flex-col">
                  <div
                    class="text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap truncate"
                    :style="[item.user.roles[0].color ? {color: item.user.roles[0].color} : null]"
                  >
                    {{ item.user.name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    @{{ item.user.username }}
                  </div>
                </div>
              </InertiaLink>
              <div
                v-else
                class="flex items-center italic text-sm text-gray-500 dark:text-gray-400"
              >
                {{ __("Anonymous") }}
              </div>
            </td>

            <DtRowItem>
              {{ item.custom_form.title }}
            </DtRowItem>

            <DtRowItem
              v-tippy
              class="text-right whitespace-nowrap"
              :content="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </DtRowItem>

            <DtRowItem
              v-if="archived"
              v-tippy
              class="text-right whitespace-nowrap"
              :content="formatToDayDateString(item.deleted_at)"
            >
              {{ formatTimeAgoToNow(item.deleted_at) }}
            </DtRowItem>

            <td
              class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
            >
              <InertiaLink
                as="a"
                :href="route('admin.custom-form-submission.show', item.id)"
                class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
              >
                <EyeIcon class="inline-block w-5 h-5" />
              </InertiaLink>
              <InertiaLink
                v-if="can('archive custom_form_submissions') && !archived"
                v-confirm="{
                  message:
                    'Archive this Custom Form Submission? It will move to archive section.',
                }"
                v-tippy
                as="button"
                method="POST"
                :href="route('admin.custom-form-submission.archive', item.id)"
                class="inline-flex items-center justify-center text-orange-500 hover:text-orange-900 focus:outline-none"
                :title="__('Archive Submission')"
              >
                <ArchiveBoxArrowDownIcon class="inline-block w-5 h-5" />
              </InertiaLink>
              <InertiaLink
                v-if="can('delete custom_form_submissions') && archived"
                v-confirm="{
                  message:
                    'Restore this Custom Form Submission? It will move back to submissions list.',
                }"
                v-tippy
                as="button"
                method="POST"
                :href="route('admin.custom-form-submission.restore', item.id)"
                class="inline-flex items-center justify-center text-green-500 hover:text-green-900 focus:outline-none"
                :title="__('Restore Submission')"
              >
                <ArrowUturnUpIcon class="inline-block w-5 h-5" />
              </InertiaLink>
              <InertiaLink
                v-if="can('delete custom_form_submissions')"
                v-confirm="{
                  message:
                    'Delete this Custom Form Submission? This action cannot be undone.',
                }"
                v-tippy
                as="button"
                method="DELETE"
                :href="route('admin.custom-form-submission.delete', item.id)"
                class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
                :title="__('Delete Submission')"
              >
                <TrashIcon class="inline-block w-5 h-5" />
              </InertiaLink>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AdminLayout>
</template>
