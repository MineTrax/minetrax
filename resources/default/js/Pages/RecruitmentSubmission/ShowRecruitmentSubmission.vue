<script setup>
import {Link} from '@inertiajs/vue3';
import AppHead from '@/Components/AppHead.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
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
import AlertCard from '@/Components/AlertCard.vue';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } = useHelpers();

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

const showingWithdrawForm = ref(false);
const withdrawForm = useForm({
    reason: '',
});
</script>

<template>
  <AppLayout>
    <AppHead
      :title="__('Your application for :recruitmenttitle #:index - Recruitments', {
        index: submission.id,
        recruitmenttitle: submission.recruitment.title,
      })"
    />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-xl text-gray-500 dark:text-gray-300">
          <span class="mr-3">
            {{
              __('Application for :recruitmenttitle #:index', {
                index: submission.id,
                recruitmenttitle: submission.recruitment.title,
              })
            }}
          </span>
          <RecruitmentStatusBadge :status="submission.status.value" />
        </h1>
        <div class="flex space-x-2">
          <Link
            :href="route('recruitment-submission.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-cool-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Back") }}</span>
          </Link>
        </div>
      </div>

      <AlertCard
        v-if="submission.status.value === 'pending'"
        title-class="flex items-center"
        text-color="text-green-600 dark:text-green-400"
        border-color="border-green-500"
      >
        {{ __("Success! A staff will soon review your application. Please be patience.") }}
        <template #icon>
          <CheckCircleIcon class="h-6 w-6 text-green-500 mr-2" />
        </template>
      </AlertCard>

      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="flex w-full gap-4 flex-col md:flex-row">
          <div class="md:w-1/2 space-y-4">
            <div class="p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-cool-gray-800">
              <ul class="flex flex-col mt-3">
                <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-gray-800 gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-gray-400">
                  <div class="flex items-center justify-between w-full">
                    <span>{{ __("Applicant") }}</span>
                    <div>
                      You
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
                    <span>
                      <InertiaLink
                        :href="route('user.public.get', submission.last_actor.username)"
                        class="flex items-center"
                      >
                        <div class="flex-shrink-0 h-10 w-10 mr-2">
                          <img
                            class="h-10 w-10 rounded-full"
                            :src="submission.last_actor.profile_photo_url"
                            alt="Avatar"
                          >
                        </div>
                        <div class="flex-col">
                          <div
                            class="text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap truncate"
                            :style="[submission.last_actor.roles[0].color ? {color: submission.last_actor.roles[0].color} : null]"
                          >
                            {{ submission.last_actor.name }}
                          </div>
                          <div class="text-sm text-gray-500">
                            @{{ submission.last_actor.username }}
                          </div>
                        </div>
                      </InertiaLink>

                    </span>
                  </div>
                </li>
              </ul>

              <div class="button-group flex space-x-4 justify-end p-4">
                <button
                  v-if="submission.i_can_withdraw"
                  class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-500 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-gray"
                  @click="showingWithdrawForm = true"
                >
                  <span>{{ __("Withdraw") }}</span>
                </button>
              </div>
            </div>


            <div class="p-2 overflow-hidden bg-white rounded shadow max-w-none dark:bg-cool-gray-800">
              <RecruitmentMessagesBox
                :submission="submission"
                :for-admin="false"
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
    </div>

    <JetDialogModal
      :show="showingWithdrawForm"
      @close="showingWithdrawForm = false"
    >
      <template #title>
        <h3 class="text-lg font-bold">
          {{ __("Withdraw & Cancel Application") }}
        </h3>
        <p
          v-if="submission.recruitment.submission_cooldown_in_seconds"
          class="text-xs text-gray-500"
        >
          {{ __("Please note that you won't be apply again for a given cooldown period of :hms.", {
            hms: secondsToHMS(submission.recruitment.submission_cooldown_in_seconds, true)
          }) }}
        </p>
      </template>

      <template #content>
        <XTextarea
          v-model="withdrawForm.reason"
          :label="__('Please provide a reason for withdrawing.')"
          :error="withdrawForm.errors.reason"
        />
      </template>

      <template #footer>
        <JetSecondaryButton
          class="mr-2"
          @click="showingWithdrawForm = false"
        >
          {{ __("Cancel") }}
        </JetSecondaryButton>
        <LoadingButton
          :loading="withdrawForm.processing"
          class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-500 border border-transparent rounded-md hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-gray"
          @click="() => {
            withdrawForm.post(route('recruitment-submission.withdraw', {
              submission: submission.id,
              recruitment: submission.recruitment.slug
            }), {
              onSuccess: () => {
                showingWithdrawForm = false;
              },
            })
          }"
        >
          {{ __("Withdraw") }}
        </LoadingButton>
      </template>
    </JetDialogModal>
  </AppLayout>
</template>
