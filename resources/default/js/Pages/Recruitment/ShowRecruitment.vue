<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import { FormKitSchema } from '@formkit/vue';
import { useFormKit } from '@/Composables/useFormKit';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';
import AlertCard from '@/Components/AlertCard.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { useHelpers } from '@/Composables/useHelpers';

const { __ } = useTranslations();
const { secondsToHMS, formatToDayDateString } = useHelpers();

const props = defineProps({
    recruitment: {
        type: Object,
    },
    currentUserCanSubmit: {
        type: Boolean,
    },
    userVerified: {
        type: Boolean,
    },
    userHasLinkedPlayer: {
        type: Boolean,
    },
    userSubmissionsCount: {
        type: Number,
    },
    secondsSinceLastSubmission: {
        type: Number,
    },
    userLastActiveSubmission: {
        type: Object,
    },
    userLastApprovedSubmission: {
        type: Object,
    }
});

const formSchema = useFormKit().generateSchemaFromFieldsArray(
    props.recruitment.fields
);

const submitForm = async (values) => {
    await promisifyForm(values);
};

const promisifyForm = (values) => {
    return new Promise((resolve, reject) => {
        const form = useForm({
            ...values,
        });
        form.post(route('recruitment.submit', props.recruitment.slug), {
            onSuccess: (visit) => {
                resolve(visit);
            },
            onError: (err) => {
                reject(err);
            },
        });
    });
};
const disabledErrorMessage = ref({
    title: null,
    body: null,
});
const formDisabled = ref(false);

watchEffect(() => {
    if (props.recruitment.status.value != 'active') {
        disabledErrorMessage.value = {
            title: __('Application Closed!'),
            body: __(
                'This recruitment form is currently closed. Please check back later.'
            ),
        };
        formDisabled.value = true;
    }

    if (!usePage().props?.auth?.user) {
        disabledErrorMessage.value = {
            title: __('Login or Register to Apply!'),
            body: __('You need to be logged in to apply to this recruitment.'),
        };
        formDisabled.value = true;
    }

    if (props.userLastActiveSubmission) {
        formDisabled.value = true;
    }

    else if (
        props.recruitment.max_submission_per_user &&
        props.userSubmissionsCount >= props.recruitment.max_submission_per_user
    ) {
        disabledErrorMessage.value = {
            title: __('Max Submissions Reached!'),
            body: __(
                'You have reached the maximum number of submissions for this recruitment.'
            ),
        };
        formDisabled.value = true;
    }

    else if (
        props.recruitment.submission_cooldown_in_seconds &&
        props.secondsSinceLastSubmission &&
        props.secondsSinceLastSubmission <
            props.recruitment.submission_cooldown_in_seconds
    ) {
        disabledErrorMessage.value = {
            title: __('You are on a Cooldown!'),
            body: __(
                'You need to wait :seconds seconds before submitting this application again.',
                {
                    seconds: secondsToHMS(
                        props.recruitment.submission_cooldown_in_seconds -
                            props.secondsSinceLastSubmission, true
                    ),
                }
            ),
        };
        formDisabled.value = true;
    }

    else if (props.recruitment.is_allow_only_verified_users && !props.userVerified) {
        disabledErrorMessage.value = {
            title: __('Account Verification Required!'),
            body: __(
                'Only verified users can apply to this recruitment. Please verify your account.'
            ),
        };
        formDisabled.value = true;
    }

    else if (
        props.recruitment.is_allow_only_player_linked_users &&
        !props.userHasLinkedPlayer
    ) {
        disabledErrorMessage.value = {
            title: __('Account Linking Required!'),
            body: __(
                'Only users with linked players can apply to this recruitment. Please link a player to your account.'
            ),
        };
        formDisabled.value = true;
    }
});
</script>

<!-- eslint-disable vue/no-v-html -->
<template>
  <app-layout>
    <app-head :title="recruitment.title" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between md:mb-4">
        <h1
          class="text-center font-bold text-2xl text-gray-900 dark:text-gray-200 mb-5"
        >
          {{ recruitment.title }}
        </h1>
        <div class="">
          <inertia-link
            :href="route('home')"
            class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Homepage") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="overflow-x-auto md:w-9/12">
          <AlertCard
            v-if="formDisabled && disabledErrorMessage.title"
            text-color="text-red-800 dark:text-red-500"
            border-color="border-red-500"
          >
            {{ disabledErrorMessage.title }}
            <template #body>
              {{ disabledErrorMessage.body }}
            </template>
          </AlertCard>

          <AlertCard
            v-if="formDisabled && userLastActiveSubmission"
            text-color="text-sky-800 dark:text-sky-500"
            border-color="border-sky-500"
          >
            {{ __("You have already applied!") }}
            <template #body>
              <p>
                {{
                  __(
                    "You have an active application for this recruitment. Applied on :date.",
                    {
                      date: formatToDayDateString(
                        userLastActiveSubmission.created_at
                      ),
                    }
                  )
                }}
              </p>

              <div class="flex mt-2">
                <InertiaLink
                  class="p-2 border border-sky-800 dark:border-sky-500 rounded text-sky-800 dark:text-sky-500 hover:bg-sky-200 dark:hover:bg-sky-900"
                  :href="
                    route('recruitment-submission.show', {
                      recruitment: recruitment.slug,
                      submission:
                        userLastActiveSubmission.id,
                    })
                  "
                >
                  {{ __("View Application") }}
                </InertiaLink>
              </div>
            </template>
          </AlertCard>

          <AlertCard
            v-if="userLastApprovedSubmission"
            text-color="text-orange-800 dark:text-orange-500"
            border-color="border-orange-500"
          >
            {{ __("Already approved in past! Wanna apply again?") }}
            <template #body>
              <p>
                {{
                  __(
                    "You have already applied to this application in past. Approved on :date.",
                    {
                      date: formatToDayDateString(
                        userLastApprovedSubmission.updated_at
                      ),
                    }
                  )
                }}
              </p>
              <p>
                {{ __("You may wanna check that before applying again.") }}
              </p>

              <div class="flex mt-2">
                <InertiaLink
                  class="p-2 border border-orange-800 dark:border-orange-500 rounded text-orange-800 dark:text-orange-500 hover:bg-orange-200 dark:hover:bg-orange-900"
                  :href="
                    route('recruitment-submission.show', {
                      recruitment: recruitment.slug,
                      submission:
                        userLastApprovedSubmission.id,
                    })
                  "
                >
                  {{ __("View Approved Application") }}
                </InertiaLink>
              </div>
            </template>
          </AlertCard>

          <div class="min-w-full">
            <div
              class="shadow max-w-none bg-white dark:bg-cool-gray-800 px-3 py-2 md:px-10 md:py-5 overflow-hidden rounded"
            >
              <div
                v-if="recruitment.description"
                class="prose dark:prose-dark max-w-none mb-6 pb-6 border-b dark:border-gray-700"
                v-html="recruitment.description_html"
              />

              <FormKit
                :submit-label="__('Apply')"
                :disabled="formDisabled"
                type="form"
                @submit="submitForm"
              >
                <FormKitSchema :schema="formSchema" />
              </FormKit>

              <p
                v-if="
                  !formDisabled &&
                    recruitment.max_submission_per_user &&
                    userSubmissionsCount > 0
                "
                class="text-xs text-gray-500 dark:text-gray-400 text-right"
              >
                {{
                  __(
                    "You can apply to this recruitment :count more time(s).",
                    {
                      count:
                        recruitment.max_submission_per_user -
                        userSubmissionsCount,
                    }
                  )
                }}
              </p>
            </div>
          </div>
        </div>

        <div class="md:w-3/12 flex-1 space-y-4 mt-4 md:mt-0">
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>
