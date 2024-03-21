<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CustomFormIntelPieChart from '@/Shared/CustomFormIntelPieChart.vue';
import CustomFormIntelBarChart from '@/Shared/CustomFormIntelBarChart.vue';
import CustomFormIntelListChart from '@/Shared/CustomFormIntelListChart.vue';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    form: {
        type: Object,
    },
    intel: {
        type: Object,
    }
});

</script>


<template>
  <AdminLayout>
    <AppHead
      :title="__(':formtitle Intel - Custom Forms', {
        formtitle: form.title,
      })"
    />

    <div class="p-4 px-10 mx-auto space-y-4">
      <div class="py-3 flex justify-between">
        <h3 class="text-xl font-extrabold text-gray-800 dark:text-gray-200">
          {{ __(":formtitle - Intel", { formtitle: form.title}) }}
        </h3>
        <div class="flex gap-4">
          <InertiaLink
            :href="route('admin.custom-form.index')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-400 border border-transparent rounded-md hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-400 focus:shadow-outline-gray dark:bg-gray-800 dark:hover:bg-gray-700 dark:active:bg-gray-900 dark:focus:border-gray-700"
          >
            <span>{{ __("Back") }}</span>
          </InertiaLink>
        </div>
      </div>

      <div class="p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-cool-gray-800">
        <h3 class="px-3 pt-2 font-bold text-gray-700 dark:text-white">
          {{ __("Details") }}
        </h3>
        <ul class="mt-3 grid grid-cols-2 gap-2">
          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Title") }}</span>
              <div>
                {{ form.title }}
              </div>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Url Slug") }}</span>
              <div>
                {{ form.slug }}
              </div>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Status") }}</span>
              <span>{{ form.status.value }}</span>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Visible in Listing") }}</span>
              <span>{{ form.is_visible_in_listing }}</span>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Who can submit?") }}</span>
              <span>{{ form.can_create_submission }}</span>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Max submission per user") }}</span>
              <span>{{ form.max_submission_per_user ? form.max_submission_per_user : __("not applicable") }}</span>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Minimum staff role weight to view submissions") }}</span>
              <span>{{ form.min_role_weight_to_view_submission ? form.min_role_weight_to_view_submission : __("none") }}</span>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Notify staff on new submission") }}</span>
              <span>{{ form.is_notify_staff_on_submission }}</span>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Created") }}</span>
              <span
                v-tippy
                :title="formatTimeAgoToNow(form.created_at)"
              >{{ formatToDayDateString(form.created_at) }}
                ({{ __("by :username" , {
                  username: form.creator.username
                }) }})
              </span>
            </div>
          </li>

          <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
            <div class="flex items-center justify-between w-full">
              <span>{{ __("Updated") }}</span>
              <span
                v-tippy
                :title="formatTimeAgoToNow(form.updated_at)"
              >{{ formatToDayDateString(form.updated_at) }}
                <span v-if="form.updater">
                  ({{ __("by :username" , {
                    username: form.updater.username
                  }) }})
                </span>
              </span>
            </div>
          </li>
        </ul>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-2 gap-4">
        <template
          v-for="item of intel"
          :key="item.label"
        >
          <CustomFormIntelPieChart
            v-if="['select', 'radio', 'multiselect', 'checkbox'].includes(item.type)"
            :title="item.label"
            :data="item.data"
          />

          <CustomFormIntelBarChart
            v-if="['datetime-local', 'date', 'time', 'month', 'week'].includes(item.type)"
            :title="item.label"
            :data="item.data"
          />

          <CustomFormIntelListChart
            v-if="['text', 'textarea', 'email', 'number', 'password', 'tel', 'url'].includes(item.type)"
            :title="item.label"
            :data="item.data"
          />
        </template>
      </div>
    </div>
  </AdminLayout>
</template>
