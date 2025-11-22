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
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { truncate } from 'lodash';

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
const page = usePage();
const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;

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
        text: truncate(props.recruitment.title, { length: 50 }),
        current: true
    }
];

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
                'This application form is currently closed. Please check back later.'
            ),
        };
        formDisabled.value = true;
    }

    if (!usePage().props?.auth?.user) {
        disabledErrorMessage.value = {
            title: __('Login or Register to Apply!'),
            body: __('You need to be logged in to apply to this application.'),
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
                'You have reached the maximum number of submissions for this application.'
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
                'You need to wait :seconds before submitting this application again.',
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
                'Only verified users can apply to this application. Please verify your account.'
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
                'Only users with linked players can apply to this application. Please link a player to your account.'
            ),
        };
        formDisabled.value = true;
    }
});
</script>

<!-- eslint-disable vue/no-v-html -->
<template>
  <AppLayout>
    <AppHead :title="recruitment.title" />

    <AppBreadcrumb :items="breadcrumbItems" />

    <div class="py-4 px-2 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="md:w-9/12 flex-1">
          <AlertCard
            v-if="formDisabled && disabledErrorMessage.title"
            text-color="text-error-800 dark:text-error-500"
            border-color="border-error-500"
          >
            {{ disabledErrorMessage.title }}
            <template #body>
              {{ disabledErrorMessage.body }}
            </template>
          </AlertCard>

          <AlertCard
            v-if="formDisabled && userLastActiveSubmission"
            text-color="text-primary dark:text-primary"
            border-color="border-primary"
          >
            {{ __("You have already applied!") }}
            <template #body>
              <p>
                {{
                  __(
                    "You have an active request for this application. Applied on :date.",
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
                  class="p-2 border border-primary dark:border-primary rounded text-primary dark:text-primary hover:bg-primary dark:hover:bg-primary"
                  :href="
                    route('recruitment-submission.show', {
                      recruitment: recruitment.slug,
                      submission:
                        userLastActiveSubmission.id,
                    })
                  "
                >
                  {{ __("View Request") }}
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
                  {{ __("View Approved Request") }}
                </InertiaLink>
              </div>
            </template>
          </AlertCard>

          <div class="min-w-full">
            <div
              class="bg-card text-card-foreground border rounded-lg shadow p-4 md:p-6"
            >
              <h1 class="font-bold text-4xl text-foreground mb-5">
                {{ recruitment.title }}
              </h1>

              <div
                v-if="recruitment.description"
                class="prose dark:prose-invert max-w-none mb-6 pb-6 border-b border-border"
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
                class="text-xs text-muted-foreground text-right"
              >
                {{
                  __(
                    "You can apply to this application :count more time(s).",
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

        <div
          class="hidden md:flex flex-col md:w-3/12 flex-none space-y-4 sticky"
          :class="{ 'top-16': isStickyNav, 'top-5': !isStickyNav }"
        >
          <ServerStatusBox />
          <ShoutBox />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
