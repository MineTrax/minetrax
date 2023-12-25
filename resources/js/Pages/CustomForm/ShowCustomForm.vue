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
          <div class="min-w-full">
            <div class="shadow max-w-none bg-white dark:bg-cool-gray-800 px-3 py-2 md:px-10 md:py-5 overflow-hidden rounded">
              <div
                v-if="customForm.description"
                class="prose dark:prose-dark max-w-none mb-4"
                v-html="customForm.description_html"
              />

              <FormKit
                type="form"
                @submit="submitForm"
              >
                <FormKitSchema :schema="formSchema" />
              </FormKit>
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

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import { FormKitSchema } from '@formkit/vue';
import {useFormKit} from '@/Composables/useFormKit';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    customForm: {
        type: Object,
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
</script>
