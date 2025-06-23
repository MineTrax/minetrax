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

const formSchema = useFormKit().generateSchemaFromFieldsArray(parsedData.value, true);
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
        <h3 class="text-xl font-extrabold text-foreground dark:text-foreground">
          {{ __(":formtitle - Submission #:index", {index: submission.id, formtitle: submission.custom_form.title}) }}
          <span
            v-if="submission.deleted_at"
            class="text-base text-orange-500 italic"
          >
            {{ __("(Archived)") }}
          </span>
        </h3>
        <div class="flex gap-4">
          <InertiaLink
            v-if="can('archive custom_form_submissions') && !submission.deleted_at"
            v-confirm="{
              message:
                'Archive this Custom Form Submission? It will move to archive section.',
            }"
            v-tippy
            as="button"
            method="POST"
            :href="route('admin.custom-form-submission.archive', submission.id)"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-orange-500 border border-transparent rounded-md hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:border-orange-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Archive") }}</span>
          </InertiaLink>
          <InertiaLink
            v-if="can('delete custom_form_submissions') && submission.deleted_at"
            v-confirm="{
              message:
                'Restore this Custom Form Submission? It will move back to submissions list.',
            }"
            v-tippy
            as="button"
            method="POST"
            :href="route('admin.custom-form-submission.restore', submission.id)"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-success-500 border border-transparent rounded-md hover:bg-success-700 active:bg-success-900 focus:outline-none focus:border-success-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Restore") }}</span>
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
            :href="route('admin.custom-form-submission.delete', submission.id)"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-error-600 border border-transparent rounded-md hover:bg-error-700 active:bg-error-900 focus:outline-none focus:border-error-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Delete") }}</span>
          </InertiaLink>
          <InertiaLink
            :href="submission.deleted_at ? route('admin.custom-form-submission.index-archived') : route('admin.custom-form-submission.index')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-surface-400 border border-transparent rounded-md hover:bg-surface-500 active:bg-surface-600 focus:outline-none focus:border-foreground focus:shadow-outline-gray dark:bg-surface-800 dark:hover:bg-surface-700 dark:active:bg-surface-900 dark:focus:border-foreground"
          >
            <span>{{ __("Back") }}</span>
          </InertiaLink>
        </div>
      </div>

      <div class="flex w-full gap-4">
        <div class="flex-grow px-3 py-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-surface-800 md:px-10 md:py-5 no-disabled-effect">
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
        <div class="w-1/3 p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-surface-800">
          <ul class="flex flex-col mt-3">
            <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
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
                        class="text-sm font-semibold text-foreground dark:text-foreground whitespace-nowrap truncate"
                        :style="[submission.user.roles[0].color ? {color: submission.user.roles[0].color} : null]"
                      >
                        {{ submission.user.name }}
                      </div>
                      <div class="text-sm text-foreground">
                        @{{ submission.user.username }}
                      </div>
                    </div>
                  </InertiaLink>
                  <div
                    v-else
                    class="flex items-center italic text-sm text-foreground dark:text-foreground"
                  >
                    {{ __("Anonymous") }}
                  </div>
                </div>
              </div>
            </li>

            <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
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

            <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
              <div class="flex items-center justify-between w-full">
                <span>{{ __("Form") }}</span>
                <span>{{ submission.custom_form.title }}</span>
              </div>
            </li>

            <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
              <div class="flex items-center justify-between w-full">
                <span>{{ __("Created At") }}</span>
                <span
                  v-tippy
                  :title="formatTimeAgoToNow(submission.created_at)"
                >{{ formatToDayDateString(submission.created_at) }}</span>
              </div>
            </li>

            <li
              v-if="submission.deleted_at"
              class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground"
            >
              <div class="flex items-center justify-between w-full">
                <span>{{ __("Archived At") }}</span>
                <span
                  v-tippy
                  :title="formatTimeAgoToNow(submission.deleted_at)"
                >{{ formatToDayDateString(submission.deleted_at) }}</span>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
