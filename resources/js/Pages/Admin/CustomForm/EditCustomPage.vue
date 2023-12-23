<template>
  <AdminLayout>
    <app-head :title="`Edit Custom Page: ${customPage.title}`" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Edit Custom Page") }}
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
            <form @submit.prevent="updateCustomPage">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
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
                        class="mt-2"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-6">
                      <fieldset>
                        <legend class="text-base font-medium text-gray-900 dark:text-gray-300">
                          {{ __("Options") }}
                        </legend>

                        <div class="mt-4 flex space-x-4">
                          <XCheckbox
                            id="is_visible"
                            v-model="form.is_visible"
                            :label="__('Visible')"
                            :help="__('General public can access this URL via link')"
                            name="is_visible"
                          />

                          <XCheckbox
                            id="is_in_navbar"
                            v-model="form.is_in_navbar"
                            :label="__('Add to Navbar')"
                            :help="__('Add this page link to the top Navigation bar')"
                            name="is_in_navbar"
                          />

                          <XCheckbox
                            id="is_open_in_new_tab"
                            v-model="form.is_open_in_new_tab"
                            :label="__('Open in New Tab')"
                            :help="__('Should this page open in new tab')"
                            name="is_open_in_new_tab"
                          />

                          <XCheckbox
                            v-show="pageType !== 'redirect'"
                            id="is_sidebar_visible"
                            v-model="form.is_sidebar_visible"
                            :label="__('Sidebar Visible')"
                            :help="__('Should right sidebar be visible when user open this page')"
                            name="is_sidebar_visible"
                          />
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
                    {{ __("Update Page") }}
                  </loading-button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import JetInputError from '@/Jetstream/InputError.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import EasyMDE from 'easymde';
import XSelect from '@/Components/Form/XSelect.vue';
import {defineProps, onMounted, ref, shallowRef} from 'vue';
import {useForm} from '@inertiajs/vue3';
import {Codemirror} from 'vue-codemirror';
import {basicSetup} from 'codemirror';
import {html} from '@codemirror/lang-html';
import {oneDark} from '@/Data/CodeMirror/darkTheme.js';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    customPage: Object
});

const pageType = ref(props.customPage.is_redirect ? 'redirect' : props.customPage.is_html_page ? 'html' : 'markdown');
const bodyMarkdown = ref(props.customPage.body);
const pageTypeList = {
    markdown: 'Markdown - Add your content in markdown format',
    html: 'HTML - Add your content in code using HTML/CSS',
    redirect: 'Redirect - This page will redirect to another URL',
};
const form = useForm({
    title: props.customPage.title,
    body: props.customPage.body,
    path: props.customPage.path,
    is_visible: props.customPage.is_visible,
    is_in_navbar: props.customPage.is_in_navbar,
    is_redirect: props.customPage.is_redirect,
    redirect_url: props.customPage.redirect_url,
    is_html_page: props.customPage.is_html_page,
    is_sidebar_visible: props.customPage.is_sidebar_visible,
    is_open_in_new_tab: props.customPage.is_open_in_new_tab,
    '_method': 'PUT'
});

// Codemirror EditorView instance ref
const view = shallowRef();
const extensions = [basicSetup, html()];
if (window.colorMode === 'dark') {
    extensions.push(oneDark);
}
const code = ref(props.customPage.body);
const handleReady = (payload) => {
    view.value = payload.view;
};

let easyMDE = null;

const updateCustomPage = () => {
    if (pageType.value === 'markdown') {
        form.body = easyMDE.value();
    } else if (pageType.value === 'html') {
        form.body = view.value.state.doc.toString();
    }
    form.is_redirect = pageType.value === 'redirect';
    form.is_html_page = pageType.value === 'html';
    form.post(route('admin.custom-page.update', props.customPage.id), {});
};

onMounted(() => {
    easyMDE = new EasyMDE({
        previewClass: 'editor-preview prose max-w-none',
    });
});
</script>
