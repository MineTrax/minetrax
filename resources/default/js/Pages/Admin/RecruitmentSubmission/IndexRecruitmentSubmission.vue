<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { EyeIcon, TrashIcon } from '@heroicons/vue/24/outline';
import XSelect from '@/Components/Form/XSelect.vue';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { pickBy } from 'lodash';
import RecruitmentStatusBadge from '@/Shared/RecruitmentStatusBadge.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

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
    closed: {
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
        key: 'user_id',
        sortable: true,
        label: __('Applicant'),
        class: 'w-3/12',
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
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'w-1/12 text-right',
    },
];

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
    <AppHead
      :title="
        closed
          ? __('Closed Request - Recruitments')
          : __('Open Requests - Recruitments')
      "
    />

    <div class="p-4 mx-auto space-y-4 px-10">
      <div class="flex items-center justify-between">
        <h3
          class="text-xl font-extrabold text-gray-800 dark:text-gray-200"
        >
          {{
            closed
              ? __("Closed Recruitment Submissions:")
              : __("Open Recruitment Submissions:")
          }}
          {{ showing ?? __("All Recruitments") }}
        </h3>

        <x-select
          id="selectForms"
          v-model="selectedForms"
          name="selectForms"
          :select-list="forms"
          :placeholder="__('All Recruitments')"
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

            <td class="px-4">
              <InertiaLink
                :href="
                  route('user.public.get', item.user.username)
                "
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
                    :style="[
                      item.user.roles[0].color
                        ? {
                          color: item.user.roles[0]
                            .color,
                        }
                        : null,
                    ]"
                  >
                    {{ item.user.name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    @{{ item.user.username }}
                  </div>
                </div>
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

            <td
              class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
            >
              <InertiaLink
                as="a"
                :href="
                  route(
                    'admin.recruitment-submission.show',
                    item.id
                  )
                "
                class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
              >
                <EyeIcon class="inline-block w-5 h-5" />
              </InertiaLink>
              <InertiaLink
                v-if="
                  can('delete recruitment_submissions') &&
                    closed
                "
                v-confirm="{
                  message:
                    'Delete this Recruitment Submission? This action cannot be undone.',
                }"
                v-tippy
                as="button"
                method="DELETE"
                :href="
                  route(
                    'admin.recruitment-submission.delete',
                    item.id
                  )
                "
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
