<template>
  <app-layout>
    <app-head title="Create Custom Page" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Create Custom Page") }}
        </h1>
        <inertia-link
          :href="route('admin.custom-page.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-4 md:gap-6">
          <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
              <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-400">
                {{ __("Overview") }}
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                {{ __("Using custom pages you can create a page based on markdown to show information like privacy, rules etc.") }} <br> {{ __("Using custom pages you can also redirect to some external links.") }}
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-3">
            <form @submit.prevent="createCustomPage">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-4">
                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="title"
                        v-model="form.title"
                        :label="__('Title of Page')"
                        :help="__('Eg: Privacy & Policy')"
                        :error="form.errors.title"
                        type="text"
                        name="title"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="path"
                        v-model="form.path"
                        :label="__('URL Path')"
                        :help="__('Eg: privacy-policy')"
                        :error="form.errors.path"
                        type="text"
                        name="path"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-4">
                      <x-checkbox
                        id="is_redirect"
                        v-model="form.is_redirect"
                        :label="__('External Redirect')"
                        :help="__('Tick if visiting this page should redirect to an external url.')"
                        name="is_redirect"
                        :error="form.errors.is_redirect"
                      />
                    </div>


                    <div
                      v-show="form.is_redirect"
                      class="col-span-6 sm:col-span-6"
                    >
                      <x-input
                        id="redirect_url"
                        v-model="form.redirect_url"
                        :label="__('Redirect URL')"
                        :help="__('Eg: https://my-custom-shop.com')"
                        :error="form.errors.redirect_url"
                        type="text"
                        name="redirect_url"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div
                      v-show="!form.is_redirect"
                      class="col-span-6 sm:col-span-6"
                    >
                      <textarea
                        id="body"
                        v-model="form.body"
                        aria-label="body"
                        name="body"
                        class="mt-1 focus:ring-light-blue-500 focus:border-light-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      />
                      <jet-input-error
                        :message="form.errors.body"
                        class="mt-2"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-3">
                      <fieldset>
                        <legend class="text-base font-medium text-gray-900 dark:text-gray-300">
                          {{ __("Options") }}
                        </legend>
                        <div class="mt-4 flex space-x-4">
                          <div class="flex items-start">
                            <div class="flex items-center h-5">
                              <input
                                id="is_visible"
                                v-model="form.is_visible"
                                name="is_visible"
                                type="checkbox"
                                class="focus:ring-light-blue-400 h-4 w-4 text-light-blue-500 border-gray-300 dark:border-gray-900 rounded dark:bg-cool-gray-900"
                              >
                            </div>
                            <div class="ml-3 text-sm">
                              <label
                                for="is_visible"
                                class="font-medium text-gray-700 dark:text-gray-400"
                              >{{ __("Visible") }}</label>
                              <!--                                                            <p class="text-gray-500">Get notified when someones posts a comment on a posting.</p>-->
                            </div>
                          </div>
                          <div class="flex items-start">
                            <div class="flex items-center h-5">
                              <input
                                id="is_in_navbar"
                                v-model="form.is_in_navbar"
                                name="is_in_navbar"
                                type="checkbox"
                                class="focus:ring-light-blue-400 h-4 w-4 text-light-blue-500 border-gray-300 dark:border-gray-900 rounded dark:bg-cool-gray-900"
                              >
                            </div>
                            <div class="ml-3 text-sm">
                              <label
                                for="is_in_navbar"
                                class="font-medium text-gray-700 dark:text-gray-400"
                              >{{ __("Add to Navbar") }}</label>
                              <!--                                                            <p class="text-gray-500">Get notified when a candidate applies for a job.</p>-->
                            </div>
                          </div>
                        </div>
                        <jet-input-error
                          :message="form.errors.is_in_navbar"
                          class="mt-2"
                        />
                        <jet-input-error
                          :message="form.errors.is_visible"
                          class="mt-2"
                        />
                      </fieldset>
                    </div>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Create Page") }}
                  </loading-button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import EasyMDE from 'easymde';
import XCheckbox from '@/Components/Form/XCheckbox.vue';

export default {
    components: {
        XCheckbox,
        AppLayout,
        JetInputError,
        LoadingButton,
        XInput
    },
    data() {
        return {
            form: this.$inertia.form({
                title: '',
                body: '',
                path: '',
                is_visible: true,
                is_in_navbar: false,
                is_redirect: false,
                redirect_url: null
            }),
            easyMDE: null,
        };
    },

    mounted() {
        this.easyMDE = new EasyMDE({
            previewClass: 'editor-preview prose max-w-none',
        });
    },

    methods: {
        createCustomPage() {
            this.form.body = this.easyMDE.value();
            this.form.post(route('admin.custom-page.store'), {});
        },
    },
};
</script>
