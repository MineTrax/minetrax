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
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { truncate } from 'lodash';

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
const page = usePage();
const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Forms'),
        url: route('custom-form.index'),
        current: false
    },
    {
        text: truncate(props.customForm.title, { length: 50 }),
        current: true
    }
];

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
  <AppLayout>
    <AppHead :title="customForm.title" />

    <AppBreadcrumb :items="breadcrumbItems" />

    <div class="py-4 px-2 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4">
        <div class="md:w-9/12 flex-1">
          <AlertCard
            v-if="formDisabled"
            text-color="text-orange-800 dark:text-orange-500"
            border-color="border-orange-500"
          >
            {{ disabledErrorMessage.title }}
            <template #body>
              {{ disabledErrorMessage.body }}
            </template>
          </AlertCard>
          <div class="min-w-full">
            <div class="bg-card text-card-foreground border rounded-lg shadow p-4 md:p-6">
              <h1 class="font-bold text-4xl text-foreground mb-5">
                {{ customForm.title }}
              </h1>

              <div
                v-if="customForm.description"
                class="prose dark:prose-invert max-w-none mb-6 pb-6 border-b border-border"
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
                class="text-xs text-muted-foreground text-right"
              >
                {{ __("You can submit this form only :count times.", {
                  count: customForm.max_submission_per_user
                }) }}
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
