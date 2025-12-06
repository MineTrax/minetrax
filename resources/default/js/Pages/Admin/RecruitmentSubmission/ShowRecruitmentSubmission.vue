<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
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
import { startCase } from 'lodash';
import Icon from '@/Components/Icon.vue';
import { ArrowLeftCircleIcon } from '@heroicons/vue/24/outline';

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

const isClosed = ['approved', 'rejected', 'withdrawn'].includes(props.submission.status.value);

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Applications'),
        url: route('admin.recruitment.index'),
        current: false,
    },
    {
        text: isClosed ? __('Closed Requests') : __('Open Requests'),
        url: isClosed ? route('admin.recruitment-submission.index-closed') : route('admin.recruitment-submission.index-open'),
        current: false,
    },
    {
        text: __('Submission #') + props.submission.id,
        current: true,
    },
];
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

    <div class="px-10 py-8 mx-auto max-w-screen-2xl space-y-4">
      <!-- Breadcrumb -->
      <div class="flex items-center justify-between">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <!-- Header -->
      <div class="flex justify-between items-center">
        <h1 class="font-bold text-xl text-foreground dark:text-foreground flex items-center">
          <span class="mr-3">
            {{
              __(':user application for :recruitmenttitle #:index', {
                user: submission.user.name,
                index: submission.id,
                recruitmenttitle: submission.recruitment.title,
              })
            }}
          </span>
          <CommonStatusBadge :status="submission.status.value" />
        </h1>
        <div class="flex gap-2">
          <Button
            variant="secondary"
            size="sm"
            as-child
          >
            <InertiaLink
              :href="route('admin.recruitment-submission.index-open')"
            >
              <ArrowLeftCircleIcon class="h-4" />
              {{ __("Open Requests") }}
            </InertiaLink>
          </Button>

          <Button
            variant="secondary"
            size="sm"
            as-child
          >
            <InertiaLink
              :href="route('admin.recruitment-submission.index-closed')"
            >
              <ArrowLeftCircleIcon class="h-4" />
              {{ __("Closed Requests") }}
            </InertiaLink>
          </Button>
          <Button
            v-if="submission.i_can_delete"
            variant="destructive"
            size="sm"
            as-child
          >
            <InertiaLink
              v-confirm="{
                message:
                  'Delete this Submission? This action cannot be undone.',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.recruitment-submission.delete', submission.id)"
            >
              {{ __("Delete") }}
            </InertiaLink>
          </Button>
        </div>
      </div>

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
                            class="inline mb-1 text-destructive fill-current focus:outline-none w-5 h-5"
                          />
                        </div>
                        <div class="text-sm text-muted-foreground">
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
                          :style="[submission.last_actor.roles[0]?.color ? {color: submission.last_actor.roles[0].color} : null]"
                        >
                          {{ submission.last_actor.name }}
                        </div>
                        <div class="text-sm text-muted-foreground">
                          @{{ submission.last_actor.username }}
                        </div>
                      </div>
                    </InertiaLink>
                  </span>
                </div>
              </li>
            </ul>

            <div
              v-if="submission.i_can_act"
              class="flex justify-end mt-4 pt-4 border-t border-border space-x-2"
            >
              <Button
                v-if="['pending', 'onhold'].includes(submission.status.value)"
                size="sm"
                as-child
              >
                <InertiaLink
                  as="button"
                  method="POST"
                  :href="route('admin.recruitment-submission.act', submission.id)"
                  :data="{
                    action: 'inprogress',
                  }"
                >
                  {{ __("Mark In-Progress") }}
                </InertiaLink>
              </Button>
              <Button
                v-if="submission.status.value != 'onhold'"
                variant="outline"
                size="sm"
                as-child
              >
                <InertiaLink
                  as="button"
                  method="POST"
                  :href="route('admin.recruitment-submission.act', submission.id)"
                  :data="{
                    action: 'onhold',
                  }"
                >
                  {{ __("Mark On-Hold") }}
                </InertiaLink>
              </Button>
              <Button
                size="sm"
                class="bg-success-500 text-white hover:bg-success-600"
                as-child
              >
                <InertiaLink
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
                >
                  {{ __("Approve") }}
                </InertiaLink>
              </Button>
              <Button
                variant="destructive"
                size="sm"
                @click="showingRejectForm = true"
              >
                {{ __("Reject") }}
              </Button>
            </div>
          </div>

          <div class="bg-card text-card-foreground border rounded-lg shadow overflow-hidden">
            <RecruitmentMessagesBox
              :submission="submission"
              :for-admin="true"
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

    <Dialog :open="showingRejectForm" @update:open="showingRejectForm = $event">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>{{ __("Reject Application") }}</DialogTitle>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <XTextarea
            v-model="rejectForm.reason"
            :label="__('Reason for Rejection')"
            :placeholder="__('Please provide a reason for rejecting this application.')"
            :error="rejectForm.errors.reason"
          />
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            class="mr-2"
            @click="showingRejectForm = false"
          >
            {{ __("Cancel") }}
          </Button>
          <LoadingButton
            variant="destructive"
            :loading="rejectForm.processing"
            class="px-4 py-2"
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
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>
