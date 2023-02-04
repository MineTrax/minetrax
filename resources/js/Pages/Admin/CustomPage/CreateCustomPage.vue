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
                {{
                  __("Using custom pages you can create a page based on markdown to show information like privacy, rules etc.")
                }} <br> {{ __("Using custom pages you can also redirect to some external links.") }}
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


                    <div
                      class="col-span-6 sm:col-span-6"
                    >
                      <x-select
                        id="page_type"
                        v-model="pageType"
                        name="page_type"
                        :label="__('Page Type')"
                        :placeholder="__('Select a type of page..')"
                        :disable-null="true"
                        :select-list="pageTypeList"
                      />

                      <div
                        v-if="pageType === 'html'"
                        class="text-sm mt-4 p-4 border border-orange-700 rounded bg-orange-200 text-orange-700 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                      >
                        {{
                          __("Please be careful with this option, adding malicious code can expose your website to security risks. Make sure you know what you are doing.")
                        }}
                      </div>
                    </div>


                    <div
                      v-show="pageType === 'redirect'"
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
                      v-show="pageType === 'html'"
                      class="col-span-6 sm:col-span-6"
                    >
                      <codemirror
                        v-model="code"
                        placeholder="Paste your HTML/CSS code here..."
                        :style="{ height: '400px'}"
                        :indent-with-tab="true"
                        :tab-size="2"
                        :extensions="extensions"
                        @ready="handleReady"
                      />

                      <jet-input-error
                        :message="form.errors.body"
                        class="mt-2 text-right"
                      />
                    </div>

                    <div
                      v-show="pageType === 'markdown'"
                      class="col-span-6 sm:col-span-6"
                    >
                      <textarea
                        id="body"
                        v-model="bodyMarkdown"
                        aria-label="body"
                        name="body"
                        class="mt-1 focus:ring-light-blue-500 focus:border-light-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      />
                      <jet-input-error
                        :message="form.errors.body"
                        class="mt-2 text-right"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-6">
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
                              <p class="text-gray-500 text-xs">
                                {{ __("General public can access this URL via link") }}
                              </p>
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
                              <p class="text-gray-500 text-xs">
                                {{ __("Add this page link to the top Navigation bar") }}
                              </p>
                            </div>
                          </div>
                          <div
                            v-show="pageType !== 'redirect'"
                            class="flex items-start"
                          >
                            <div class="flex items-center h-5">
                              <input
                                id="is_sidebar_visible"
                                v-model="form.is_sidebar_visible"
                                name="is_sidebar_visible"
                                type="checkbox"
                                class="focus:ring-light-blue-400 h-4 w-4 text-light-blue-500 border-gray-300 dark:border-gray-900 rounded dark:bg-cool-gray-900"
                              >
                            </div>
                            <div class="ml-3 text-sm">
                              <label
                                for="is_sidebar_visible"
                                class="font-medium text-gray-700 dark:text-gray-400"
                              >{{ __("Sidebar Visible") }}</label>
                              <p class="text-gray-500 text-xs">
                                {{
                                  __("Should right sidebar be visible when user open this page")
                                }}
                              </p>
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
                        <jet-input-error
                          :message="form.errors.is_sidebar_visible"
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


<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import EasyMDE from 'easymde';
import {onMounted, ref, shallowRef} from 'vue';
import {useForm} from '@inertiajs/inertia-vue3';
import {Codemirror} from 'vue-codemirror';
import {basicSetup} from 'codemirror';
import {html} from '@codemirror/lang-html';
import {oneDark} from '@/Data/CodeMirror/darkTheme.js';

const pageType = ref('markdown');
const bodyMarkdown = ref('');
const pageTypeList = {
    markdown: 'Markdown - Add your content in markdown format',
    html: 'HTML - Add your content in code using HTML/CSS',
    redirect: 'Redirect - This page will redirect to another URL',
};
const form = useForm({
    title: '',
    body: '',
    path: '',
    is_visible: true,
    is_in_navbar: false,
    is_redirect: false,
    redirect_url: null,
    is_html_page: false,
    is_sidebar_visible: true,
});

// Codemirror EditorView instance ref
const view = shallowRef();
const extensions = [basicSetup, html()];
if (window.colorMode === 'dark') {
    extensions.push(oneDark);
}
const code = ref('');
const handleReady = (payload) => {
    view.value = payload.view;
};

let easyMDE = null;

const createCustomPage = () => {
    if (pageType.value === 'markdown') {
        form.body = easyMDE.value();
    } else if (pageType.value === 'html') {
        form.body = view.value.state.doc.toString();
    }
    form.is_redirect = pageType.value === 'redirect';
    form.is_html_page = pageType.value === 'html';
    form.post(route('admin.custom-page.store'), {});
};

onMounted(() => {
    easyMDE = new EasyMDE({
        previewClass: 'editor-preview prose max-w-none',
    });
});
</script>
