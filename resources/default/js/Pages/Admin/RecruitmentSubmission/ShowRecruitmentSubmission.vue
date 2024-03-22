<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';
import { FormKitSchema } from '@formkit/vue';
import {useFormKit} from '@/Composables/useFormKit';
import RecruitmentMessagesBox from '@/Shared/RecruitmentMessagesBox.vue';
import RecruitmentStatusBadge from '@/Shared/RecruitmentStatusBadge.vue';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import { startCase } from 'lodash';

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


const showingRejectForm = ref(false);
const rejectForm = useForm({
    action: 'rejected',
    reason: '',
});

</script>

<template>
  <AdminLayout>
    <AppHead
      :title="__(':user application for :recruitmenttitle #:index - Recruitments', {
        user: submission.user.name,
        index: submission.id,
        recruitmenttitle: submission.recruitment.title,
      })"
    />

    <div class="p-4 px-10 mx-auto space-y-4">
      <div class="py-3 flex justify-between">
        <h3 class="text-xl font-extrabold text-gray-800 dark:text-gray-200">
          {{
            __(':user application for :recruitmenttitle #:index', {
              user: submission.user.name,
              index: submission.id,
              recruitmenttitle: submission.recruitment.title,
            })
          }}
          <RecruitmentStatusBadge :status="submission.status.value" />
        </h3>
        <div class="flex gap-4">
          <InertiaLink
            v-if="submission.i_can_delete"
            v-confirm="{
              message:
                'Delete this Submission? This action cannot be undone.',
            }"
            v-tippy
            as="button"
            method="DELETE"
            :href="route('admin.recruitment-submission.delete', submission.id)"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Delete") }}</span>
          </InertiaLink>
          <InertiaLink
            :href="['pending', 'onhold', 'inprogress'].includes(submission.status.value) ? route('admin.recruitment-submission.index-open') : route('admin.recruitment-submission.index-closed')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-400 border border-transparent rounded-md hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-400 focus:shadow-outline-gray dark:bg-gray-800 dark:hover:bg-gray-700 dark:active:bg-gray-900 dark:focus:border-gray-700"
          >
            <span>{{ __("Back") }}</span>
          </InertiaLink>
        </div>
      </div>

      <div class="flex w-full gap-4">
        <div class="w-1/2 space-y-4">
          <div class="p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-cool-gray-800">
            <ul class="flex flex-col mt-3">
              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Applicant") }}</span>
                  <div>
                    <InertiaLink
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
                  </div>
                </div>
              </li>

              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Recruitment") }}</span>
                  <span>{{ submission.recruitment.title }}</span>
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

              <li
                class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400"
              >
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Last Updated At") }}</span>
                  <span
                    v-tippy
                    :title="formatTimeAgoToNow(submission.updated_at)"
                  >{{ formatToDayDateString(submission.updated_at) }}
                  </span>
                </div>
              </li>


              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Submission Status") }}</span>
                  <RecruitmentStatusBadge :status="submission.status.value" />
                </div>
              </li>

              <li
                v-if="submission.last_act_reason"
                class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400"
              >
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Reason") }}</span>
                  <span class="w-1/2 whitespace-pre-line text-right">{{ submission.last_act_reason }}</span>
                </div>
              </li>

              <li
                v-if="submission.last_act_by"
                class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400"
              >
                <div class="flex items-center justify-between w-full">
                  <span>
                    {{ __("Marked :status By", {
                      status: startCase(submission.status.value),
                    }) }}
                  </span>
                  <span>@{{ submission.last_actor.username }}</span>
                </div>
              </li>
            </ul>

            <div class="button-group flex space-x-4 justify-end p-4">
              <InertiaLink
                v-if="submission.i_can_act && ['pending', 'onhold'].includes(submission.status.value)"
                as="button"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-sky-500 border border-transparent rounded-md hover:bg-sky-700 active:bg-sky-900 focus:outline-none focus:border-sky-900 focus:shadow-outline-gray"
                method="POST"
                :href="route('admin.recruitment-submission.act', submission.id)"
                :data="{
                  action: 'inprogress',
                }"
              >
                <span>{{ __("Mark In-Progress") }}</span>
              </InertiaLink>
              <InertiaLink
                v-if="submission.i_can_act && submission.status.value != 'onhold'"
                as="button"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-500 border border-transparent rounded-md hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray"
                method="POST"
                :href="route('admin.recruitment-submission.act', submission.id)"
                :data="{
                  action: 'onhold',
                }"
              >
                <span>{{ __("Mark On-Hold") }}</span>
              </InertiaLink>
              <InertiaLink
                v-if="submission.i_can_act"
                v-confirm="{
                  title: 'Approve',
                  confirmButtonColor: 'green',
                  icon: 'success',
                  message:
                    'Approve this application? This action cannot be undone.',
                }"
                as="button"
                method="POST"
                :href="route('admin.recruitment-submission.act', submission.id)"
                :data="{
                  action: 'approved',
                }"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-500 border border-transparent rounded-md hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:shadow-outline-gray"
              >
                <span>{{ __("Approve") }}</span>
              </InertiaLink>
              <button
                v-if="submission.i_can_act"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-500 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-gray"
                @click="showingRejectForm = true"
              >
                <span>{{ __("Reject") }}</span>
              </button>
            </div>
          </div>


          <div class="p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-cool-gray-800">
            <RecruitmentMessagesBox
              :submission="submission"
              :for-admin="true"
            />
          </div>
        </div>


        <div class="md:w-1/2 px-3 py-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-cool-gray-800 md:px-10 md:py-5 no-disabled-effect">
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
      </div>
    </div>

    <JetDialogModal
      :show="showingRejectForm"
      @close="showingRejectForm = false"
    >
      <template #title>
        <h3 class="text-lg font-bold">
          {{ __("Reject Application") }}
        </h3>
      </template>

      <template #content>
        <XTextarea
          v-model="rejectForm.reason"
          :label="__('Reason for Rejection')"
          :error="rejectForm.errors.reason"
        />
      </template>

      <template #footer>
        <JetSecondaryButton
          class="mr-2"
          @click="showingRejectForm = false"
        >
          {{ __("Cancel") }}
        </JetSecondaryButton>
        <LoadingButton
          :loading="rejectForm.processing"
          class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-500 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-gray"
          @click="() => {
            rejectForm.post(route('admin.recruitment-submission.act', submission.id), {
              onSuccess: () => {
                showingRejectForm = false;
              },
            })
          }"
        >
          {{ __("Reject") }}
        </LoadingButton>
      </template>
    </JetDialogModal>
  </AdminLayout>
</template>
