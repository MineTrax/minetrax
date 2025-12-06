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
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import { startCase, truncate } from 'lodash';
import AlertCard from '@/Components/AlertCard.vue';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';

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

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Applications'),
        url: route('recruitment.index'),
        current: false
    },
    {
        text: __('My Requests'),
        url: route('recruitment-submission.index'),
        current: false
    },
    {
        text: __('Submission #') + props.submission.id,
        current: true
    }
];
</script>

<template>
  <AppLayout>
    <AppHead
      :title="__('Your application request for :recruitmenttitle #:index - Applications', {
        index: submission.id,
        recruitmenttitle: submission.recruitment.title,
      })"
    />

    <AppBreadcrumb :items="breadcrumbItems" />

    <div class="py-4 px-2 md:px-10 max-w-screen-2xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h1 class="font-bold text-xl text-foreground dark:text-foreground flex items-center">
          <span class="mr-3">
            {{
              __('Application for :recruitmenttitle #:index', {
                index: submission.id,
                recruitmenttitle: submission.recruitment.title,
              })
            }}
          </span>
          <CommonStatusBadge :status="submission.status.value" />
        </h1>
      </div>

      <!-- Alert -->
      <AlertCard
        v-if="submission.status.value === 'pending'"
        title-class="flex items-center"
        text-color="text-success-600 dark:text-success-400"
        border-color="border-success-500"
      >
        {{ __("Success! A staff will soon review your application. Please be patience.") }}
        <template #icon>
          <CheckCircleIcon class="h-6 w-6 text-success-500 mr-2" />
        </template>
      </AlertCard>

      <!-- Main Content -->
      <div class="flex flex-col md:flex-row gap-4">
        <!-- Left Column (Details & Messages) -->
        <div class="md:w-1/2 space-y-4">
          <div class="bg-card text-card-foreground border rounded-lg shadow p-4">
            <ul class="flex flex-col mt-3">
              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Applicant") }}</span>
                  <div>
                    {{ __("You") }}
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
                          class="text-sm font-semibold text-foreground dark:text-foreground whitespace-nowrap truncate"
                          :style="[submission.last_actor.roles[0].color ? {color: submission.last_actor.roles[0].color} : null]"
                        >
                          {{ submission.last_actor.name }}
                        </div>
                        <div class="text-sm text-foreground">
                          @{{ submission.last_actor.username }}
                        </div>
                      </div>
                    </InertiaLink>

                  </span>
                </div>
              </li>
            </ul>

            <div
              v-if="submission.i_can_withdraw"
              class="flex justify-end mt-4 pt-4 border-t border-border"
            >
              <button
                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-error-500 border border-transparent rounded-md hover:bg-error-700 active:bg-error-900 focus:outline-none focus:border-error-900 focus:shadow-outline-gray"
                @click="showingWithdrawForm = true"
              >
                <span>{{ __("Withdraw") }}</span>
              </button>
            </div>
          </div>

          <div class="bg-card text-card-foreground border rounded-lg shadow overflow-hidden">
            <RecruitmentMessagesBox
              :submission="submission"
              :for-admin="false"
            />
          </div>
        </div>

        <!-- Right Column (Form Data) -->
        <div class="md:w-1/2">
          <div class="bg-card text-card-foreground border rounded-lg shadow p-4 md:p-6 no-disabled-effect">
            <h3 class="font-extrabold text-lg mb-4">{{ __("Submission Details") }}</h3>
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

    <Dialog :open="showingWithdrawForm" @update:open="showingWithdrawForm = $event">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>{{ __("Withdraw & Cancel Application") }}</DialogTitle>
          <div
            v-if="submission.recruitment.submission_cooldown_in_seconds"
            class="text-xs text-muted-foreground mt-1"
          >
            {{ __("Please note that you won't be apply again for a given cooldown period of :hms.", {
              hms: secondsToHMS(submission.recruitment.submission_cooldown_in_seconds, true)
            }) }}
          </div>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <XTextarea
            v-model="withdrawForm.reason"
            :placeholder="__('Please provide a reason for withdrawing.')"
            :error="withdrawForm.errors.reason"
          />
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            class="mr-2"
            @click="showingWithdrawForm = false"
          >
            {{ __("Cancel") }}
          </Button>
          <LoadingButton
            variant="destructive"
            :loading="withdrawForm.processing"
            class="px-4 py-2"
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
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
