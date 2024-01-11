<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { computed } from 'vue';
import { FormKitSchema } from '@formkit/vue';
import {useFormKit} from '@/Composables/useFormKit';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const props = defineProps({
    submission: {
        type: Object,
    },
});

const parsedData = computed(() => {
    return props.submission.data.map((item) => {
        return {
            ...item,
            value: item.data
        };
    });
});

const formSchema = useFormKit().generateSchemaFromFieldsArray(parsedData.value);
</script>

<template>
  <AdminLayout>
    <AppHead
      :title="__(':formtitle submission #:index - Custom Form Submissions', {
        index: submission.id,
        formtitle: submission.custom_form.title,
      })"
    />

    <div class="p-4 px-10 mx-auto space-y-4">
      <div class="py-3 flex justify-between">
        <h3 class="text-xl font-extrabold text-gray-800 dark:text-gray-200">
          {{ __(":formtitle - Submission #:index", {index: submission.id, formtitle: submission.custom_form.title}) }}
        </h3>
        <div class="flex gap-4">
          <InertiaLink
            v-if="can('delete custom_form_submissions')"
            v-confirm="{
              message:
                'Delete this Custom Form Submission? This action cannot be undone.',
            }"
            v-tippy
            as="button"
            method="DELETE"
            :href="route('admin.custom-form-submission.delete', submission.id)"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Delete") }}</span>
          </InertiaLink>
          <InertiaLink
            :href="route('admin.custom-form-submission.index')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-400 border border-transparent rounded-md hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-400 focus:shadow-outline-gray dark:bg-gray-800 dark:hover:bg-gray-700 dark:active:bg-gray-900 dark:focus:border-gray-700"
          >
            <span>{{ __("Back") }}</span>
          </InertiaLink>
        </div>
      </div>

      <div class="flex w-full gap-4">
        <div class="flex-grow px-3 py-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-cool-gray-800 md:px-10 md:py-5">
          <FormKit
            :disabled="true"
            type="form"
            :submit-attrs="{
              inputClass: 'hidden',
            }"
          >
            <FormKitSchema :schema="formSchema" />
          </FormKit>
        </div>
        <div class="w-1/3 p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-cool-gray-800">
          <ul class="flex flex-col mt-3">
            <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
              <div class="flex items-center justify-between w-full">
                <span>{{ __("User") }}</span>
                <div>
                  <InertiaLink
                    v-if="submission.user"
                    :href="route('user.public.get', submission.user.username)"
                    class="flex items-center"
                  >
                    <div class="flex-shrink-0 h-10 w-10 mr-2">
                      <img
                        class="h-10 w-10 rounded-full"
                        :src="submission.user.profile_photo_url"
                        alt="Avatar"
                      >
                    </div>
                    <div class="flex-col">
                      <div
                        class="text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap truncate"
                        :style="[submission.user.roles[0].color ? {color: submission.user.roles[0].color} : null]"
                      >
                        {{ submission.user.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        @{{ submission.user.username }}
                      </div>
                    </div>
                  </InertiaLink>
                  <div
                    v-else
                    class="flex items-center italic text-sm text-gray-500 dark:text-gray-400"
                  >
                    {{ __("Anonymous") }}
                  </div>
                </div>
              </div>
            </li>

            <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
              <div class="flex items-center justify-between w-full">
                <span>{{ __("User's Country") }}</span>
                <div class="flex items-center space-x-1">
                  <span>{{ submission.country.name }}</span>
                  <div
                    v-if="submission.country"
                    v-tippy
                    class="flex-shrink-0 h-10 w-10 focus:outline-none"
                    :content="submission.country.name"
                  >
                    <img
                      class="h-10 w-10"
                      :src="submission.country.photo_path"
                      alt=""
                    >
                  </div>
                </div>
              </div>
            </li>

            <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
              <div class="flex items-center justify-between w-full">
                <span>Form</span>
                <span>{{ submission.custom_form.title }}</span>
              </div>
            </li>

            <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
              <div class="flex items-center justify-between w-full">
                <span>{{ __("Created At") }}</span>
                <span
                  v-tippy
                  :title="formatTimeAgoToNow(submission.created_at)"
                >{{ formatToDayDateString(submission.created_at) }}</span>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
