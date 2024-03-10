<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import { FormKitSchema } from '@formkit/vue';
import {useFormKit} from '@/Composables/useFormKit';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AlertCard from '@/Components/AlertCard.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

const props = defineProps({
    customForm: {
        type: Object,
    },
    currentUserCanSubmit: {
        type: Boolean,
    },
});

const formSchema = useFormKit().generateSchemaFromFieldsArray(props.customForm.fields);

const submitForm = async (values) => {
    await promisifyForm(values);
};

const promisifyForm = (values) => {
    return new Promise((resolve, reject) => {
        const form = useForm({
            ...values
        });
        form.post(route('custom-form.submit', props.customForm.slug), {
            onSuccess: visit => {
                resolve(visit);
            },
            onError: err => {
                reject(err);
            }
        });
    });
};

const formDisabled = computed(() => {
    return props.customForm.status.value !== 'active' || !props.currentUserCanSubmit;
});

const disabledErrorMessage = computed(() => {
    if (!formDisabled.value) {
        return {
            title: null,
            body: null,
        };
    }

    if (props.customForm.status.value === 'disabled') {
        return {
            title: __('Oh Jeez! This form is currently disabled.'),
            body: __(
                'It seems the form is no longer accepting submissions. Please check back later.'
            ),
        };
    }

    if (props.customForm.status.value !== 'active') {
        return {
            title: __('Oh Jeez! This form is not active.'),
            body: __(
                'It seems the form is not active yet. Please check back later.'
            ),
        };
    }

    if (['auth', 'staff'].includes(props.customForm.can_create_submission) && !usePage().props.auth.user) {
        return {
            title: __('Oh Jeez! You need to be logged in to submit this form.'),
            body: __(
                'Please login and try again.'
            ),
        };
    }

    return {
        title: __('Oh Jeez! You have already submitted this form.'),
        body: __(
            'You have already submitted this form maximum number of times allowed.'
        ),
    };
});

</script>

<!-- eslint-disable vue/no-v-html -->
<template>
  <app-layout>
    <app-head :title="customForm.title" />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex justify-between md:mb-4">
        <h1 class="text-center font-bold text-2xl text-gray-900 dark:text-gray-200 mb-5">
          {{ customForm.title }}
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
        <div
          class="overflow-x-auto md:w-9/12"
        >
          <AlertCard
            v-if="formDisabled"
            text-color="text-orange-800 dark:text-orange-500"
            border-color="border-orange-500"
          >
            {{ disabledErrorMessage.title }}
            <template #body>
              {{
                disabledErrorMessage.body
              }}
            </template>
          </AlertCard>
          <div class="min-w-full">
            <div class="shadow max-w-none bg-white dark:bg-cool-gray-800 px-3 py-2 md:px-10 md:py-5 overflow-hidden rounded">
              <div
                v-if="customForm.description"
                class="prose dark:prose-dark max-w-none mb-6 pb-6 border-b dark:border-gray-700"
                v-html="customForm.description_html"
              />

              <FormKit
                :disabled="formDisabled"
                type="form"
                @submit="submitForm"
              >
                <FormKitSchema :schema="formSchema" />
              </FormKit>

              <p
                v-if="!formDisabled && customForm.max_submission_per_user"
                class="text-xs text-gray-500 dark:text-gray-400 text-right"
              >
                {{ __("You can submit this form only :count times.", {
                  count: customForm.max_submission_per_user
                }) }}
              </p>
            </div>
          </div>
        </div>

        <div
          class="md:w-3/12 flex-1 space-y-4 mt-4 md:mt-0"
        >
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>
