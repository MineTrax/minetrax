<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';
import { FormKitSchema } from '@formkit/vue';
import {useFormKit} from '@/Composables/useFormKit';
import RecruitmentMessagesBox from '@/Shared/RecruitmentMessagesBox.vue';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import { startCase } from 'lodash';
import { ArrowLeftCircleIcon } from '@heroicons/vue/24/outline';
import Icon from '@/Components/Icon.vue';

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
      :title="__(':user application for :recruitmenttitle #:index - Applications', {
        user: submission.user.name,
        index: submission.id,
        recruitmenttitle: submission.recruitment.title,
      })"
    />

    <div class="p-4 px-10 mx-auto space-y-4">
      <div class="py-3 flex justify-between">
        <h3 class="text-xl font-extrabold text-foreground dark:text-foreground">
          {{
            __(':user application for :recruitmenttitle #:index', {
              user: submission.user.name,
              index: submission.id,
              recruitmenttitle: submission.recruitment.title,
            })
          }}
          <CommonStatusBadge :status="submission.status.value" />
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
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-error-600 border border-transparent rounded-md hover:bg-error-700 active:bg-error-900 focus:outline-none focus:border-error-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Delete") }}</span>
          </InertiaLink>
          <InertiaLink
            :href="route('admin.recruitment-submission.index-open')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-surface-400 border border-transparent rounded-md hover:bg-surface-500 active:bg-surface-600 focus:outline-none focus:border-foreground focus:shadow-outline-gray dark:bg-surface-800 dark:hover:bg-surface-700 dark:active:bg-surface-900 dark:focus:border-foreground"
          >
            <span class="flex items-center justify-center">
              <ArrowLeftCircleIcon class="h-4 mr-2" />
              {{ __("Open") }}
            </span>
          </InertiaLink>

          <InertiaLink
            :href="route('admin.recruitment-submission.index-closed')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-surface-400 border border-transparent rounded-md hover:bg-surface-500 active:bg-surface-600 focus:outline-none focus:border-foreground focus:shadow-outline-gray dark:bg-surface-800 dark:hover:bg-surface-700 dark:active:bg-surface-900 dark:focus:border-foreground"
          >
            <span class="flex items-center justify-center">
              <ArrowLeftCircleIcon class="h-4 mr-2" />
              {{ __("Closed") }}
            </span>
          </InertiaLink>
        </div>
      </div>

      <div class="flex w-full gap-4">
        <div class="w-1/2 space-y-4">
          <div class="p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-surface-800">
            <ul class="flex flex-col mt-3">
              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
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
                          class="text-sm font-semibold text-foreground dark:text-foreground whitespace-nowrap truncate"
                          :style="[submission.user.roles[0].color ? {color: submission.user.roles[0].color} : null]"
                        >
                          {{ submission.user.name }}

                          <Icon
      v-if="submission.user.verified_at"
      v-tippy
      name="verified-check-fill"
      :title="__('Verified Account')"
      class="inline mb-1 fill-current focus:outline-none text-primary w-5 h-5"
    />
    <Icon
      v-if="submission.user.is_staff"
      v-tippy
      name="shield-check-fill"
      :title="__('Staff Member')"
      class="inline mb-1 text-amber-400 fill-current focus:outline-none w-5 h-5"
    />
    <Icon
      v-if="submission.user.muted_at"
      v-tippy
      name="volume-off-fill"
      :title="__('Muted User')"
      class="inline mb-1 text-error-500 fill-current focus:outline-none w-5 h-5"
    />
                        </div>
                        <div class="text-sm text-foreground">
                          @{{ submission.user.username }}
                        </div>
                      </div>
                    </InertiaLink>
                  </div>
                </div>
              </li>

              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Application") }}</span>
                  <span>{{ submission.recruitment.title }}</span>
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
                class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground"
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

              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Request Status") }}</span>
                  <CommonStatusBadge :status="submission.status.value" />
                </div>
              </li>

              <li
                v-if="submission.last_act_reason"
                class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground"
              >
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Reason") }}</span>
                  <span class="w-1/2 whitespace-pre-line text-right">{{ submission.last_act_reason }}</span>
                </div>
              </li>

              <li
                v-if="submission.last_act_by"
                class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground"
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
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-primary border border-transparent rounded-md hover:bg-primary active:bg-primary focus:outline-none focus:border-primary focus:shadow-outline-gray"
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
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-surface-500 border border-transparent rounded-md hover:bg-surface-700 active:bg-surface-900 focus:outline-none focus:border-foreground focus:shadow-outline-gray"
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
                    'Approve this request? This action cannot be undone.',
                }"
                as="button"
                method="POST"
                :href="route('admin.recruitment-submission.act', submission.id)"
                :data="{
                  action: 'approved',
                }"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-success-500 border border-transparent rounded-md hover:bg-success-700 active:bg-success-900 focus:outline-none focus:border-success-900 focus:shadow-outline-gray"
              >
                <span>{{ __("Approve") }}</span>
              </InertiaLink>
              <button
                v-if="submission.i_can_act"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-error-500 border border-transparent rounded-md hover:bg-error-700 active:bg-error-900 focus:outline-none focus:border-error-900 focus:shadow-outline-gray"
                @click="showingRejectForm = true"
              >
                <span>{{ __("Reject") }}</span>
              </button>
            </div>
          </div>


          <div class="p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-surface-800">
            <RecruitmentMessagesBox
              :submission="submission"
              :for-admin="true"
            />
          </div>
        </div>


        <div class="md:w-1/2 px-3 py-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-surface-800 md:px-10 md:py-5 no-disabled-effect">
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
          class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-error-500 border border-transparent rounded-md hover:bg-error-700 active:bg-error-900 focus:outline-none focus:border-error-900 focus:shadow-outline-gray"
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
