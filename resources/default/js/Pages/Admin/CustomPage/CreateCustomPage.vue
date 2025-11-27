<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import { Codemirror } from 'vue-codemirror';
import { basicSetup } from 'codemirror';
import { html } from '@codemirror/lang-html';
import { oneDark } from '@/Data/CodeMirror/darkTheme.js';
import EasyMDE from 'easymde';
import { onMounted, ref, shallowRef } from 'vue';

const { __ } = useTranslations();

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Custom Pages'),
        url: route('admin.custom-page.index'),
        current: false,
    },
    {
        text: __('Create Custom Page'),
        current: true,
    }
];

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
    is_open_in_new_tab: false,
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

<template>
  <AdminLayout>
    <app-head :title="__('Create Custom Page')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="createCustomPage">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-6">
                  <XInput
                    id="title"
                    v-model="form.title"
                    :label="__('Title of Page')"
                    :help="__('Eg: Privacy & Policy')"
                    :error="form.errors.title"
                    type="text"
                    name="title"
                  />
                </div>

                <div class="col-span-6 sm:col-span-6">
                  <XInput
                    id="path"
                    v-model="form.path"
                    :label="__('URL Path')"
                    :help="__('Eg: privacy-policy')"
                    :error="form.errors.path"
                    type="text"
                    name="path"
                  />
                </div>

                <div class="col-span-6 sm:col-span-6">
                  <XSelect
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
                    class="text-sm mt-4 p-4 border border-warning-600 rounded bg-warning-100 text-warning-700 dark:bg-warning-900/25 dark:text-warning-400"
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
                  <XInput
                    id="redirect_url"
                    v-model="form.redirect_url"
                    :label="__('Redirect URL')"
                    :help="__('Eg: https://my-custom-shop.com')"
                    :error="form.errors.redirect_url"
                    type="text"
                    name="redirect_url"
                  />
                </div>

                <div
                  v-show="pageType === 'html'"
                  class="col-span-6 sm:col-span-6"
                >
                  <Codemirror
                    v-model="code"
                    placeholder="Paste your HTML/CSS code here..."
                    :style="{ height: '400px'}"
                    :indent-with-tab="true"
                    :tab-size="2"
                    :extensions="extensions"
                    @ready="handleReady"
                  />
                  <p
                    v-if="form.errors.body"
                    class="text-xs text-destructive mt-2 text-right"
                  >
                    {{ form.errors.body }}
                  </p>
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
                    class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-input bg-background rounded-md"
                  />
                  <p
                    v-if="form.errors.body"
                    class="text-xs text-destructive mt-2 text-right"
                  >
                    {{ form.errors.body }}
                  </p>
                </div>

                <div class="col-span-6 sm:col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Options") }}
                    </legend>
                    <div class="flex flex-wrap gap-6">
                      <XSwitch
                        id="is_visible"
                        v-model="form.is_visible"
                        :label="__('Visible')"
                        :help="__('General public can access this URL via link')"
                        name="is_visible"
                        :error="form.errors.is_visible"
                      />

                      <XSwitch
                        id="is_in_navbar"
                        v-model="form.is_in_navbar"
                        :label="__('Add to Navbar')"
                        :help="__('Add this page link to the top Navigation bar')"
                        name="is_in_navbar"
                        :error="form.errors.is_in_navbar"
                      />

                      <XSwitch
                        id="is_open_in_new_tab"
                        v-model="form.is_open_in_new_tab"
                        :label="__('Open in New Tab')"
                        :help="__('Should this page open in new tab')"
                        name="is_open_in_new_tab"
                      />

                      <XSwitch
                        v-show="pageType !== 'redirect' && pageType !== 'html'"
                        id="is_sidebar_visible"
                        v-model="form.is_sidebar_visible"
                        :label="__('Sidebar Visible')"
                        :help="__('Should right sidebar be visible when user open this page')"
                        name="is_sidebar_visible"
                        :error="form.errors.is_sidebar_visible"
                      />
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.custom-page.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin -ml-1 mr-2 h-4 w-4"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  />
                </svg>
                {{ __("Create Page") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
