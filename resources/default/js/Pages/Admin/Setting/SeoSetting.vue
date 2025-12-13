<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import XInput from '@/Components/Form/XInput.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import ImageUpload from '@/Components/Form/ImageUpload.vue';
import Icon from '@/Components/Icon.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

const props = defineProps({
    settings: Object,
    servers: Object
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Settings'),
        current: false,
    },
    {
        text: __('SEO Settings'),
        current: true,
    }
];

const form = useForm({
    'title_home': props.settings.title_home,
    'title_suffix': props.settings.title_suffix,
    'meta': props.settings.meta,
    'inject_at_head': props.settings.inject_at_head,
    'inject_at_body_start': props.settings.inject_at_body_start,
    'inject_at_body_end': props.settings.inject_at_body_end,
    'favicon': null,
});

const saveSetting = () => {
    form.post(route('admin.setting.seo.update'), {
        preserveScroll: true,
        onSuccess: () => {
            location.reload();
        }
    });
};

const addMetaField = () => {
    form.meta.push({
        name: '',
        content: '',
    });
};

const removeMetaField = (index) => {
    form.meta.splice(index, 1);
};
</script>

<template>
  <AdminLayout>
    <app-head :title="__('SEO Settings')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form
          autocomplete="off"
          @submit.prevent="saveSetting"
        >
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <!-- Site Identity -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="space-y-6">
                      <!-- Favicon -->
                      <ImageUpload
                        id="favicon"
                        name="favicon"
                        :label="__('Favicon Image')"
                        :current-url="settings.favicon_path"
                        v-model="form.favicon"
                        :error="form.errors.favicon"
                        :removable="false"
                        shape="rect"
                        :preview-class="'h-20 w-20'"
                        :upload-label="__('Upload')"
                        :change-label="__('Change')"
                        object-fit="contain"
                        accept="image/png, image/vnd.microsoft.icon, image/svg+xml"
                        :hint="__('.png, .ico, .svg only')"
                      />

                      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                          <XInput
                            id="title_home"
                            v-model="form.title_home"
                            :label="__('Title Main')"
                            :error="form.errors.title_home"
                            :placeholder="__('Eg: MineTrax World - Play, Build, Survive, Explore')"
                            type="text"
                            name="title_home"
                          />
                        </div>
                        <div>
                          <XInput
                            id="title_suffix"
                            v-model="form.title_suffix"
                            :label="__('Title Suffix')"
                            :error="form.errors.title_suffix"
                            :placeholder="__('Eg: ◾ MineTrax.live')"
                            type="text"
                            name="title_suffix"
                          />
                        </div>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Meta Fields -->
                <div class="col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Meta Fields") }}
                    </legend>

                    <div class="space-y-4">
                      <div class="flex space-x-4">
                        <div class="w-5" />
                        <label class="flex-1 block text-sm font-medium text-foreground">
                          {{ __("Name") }}
                          <span class="text-xs text-muted-foreground"> ({{ __("Eg: description, keywords, title") }})</span>
                        </label>
                        <label class="flex-1 block text-sm font-medium text-foreground">
                          {{ __("Content") }}
                        </label>
                      </div>

                      <div
                        v-for="(field, index) in form.meta"
                        :key="index"
                        class="flex space-x-4 items-start"
                      >
                        <button
                          type="button"
                          class="mt-2 focus:outline-none group"
                          @click="removeMetaField(index)"
                        >
                          <Icon
                            class="w-5 h-5 text-muted-foreground group-hover:text-destructive"
                            name="trash"
                          />
                        </button>
                        <div class="flex-1">
                          <XInput
                            v-model="field.name"
                            :label="null"
                            :placeholder="__('Meta Name :index', { index: index + 1 })"
                            :error="form.errors[`meta.${index}.name`]"
                            type="text"
                            help-error-flex="flex-col"
                            :required="true"
                          />
                        </div>
                        <div class="flex-1">
                          <XInput
                            v-model="field.content"
                            :label="null"
                            :placeholder="__('Meta Content :index', { index: index + 1 })"
                            :error="form.errors[`meta.${index}.content`]"
                            type="text"
                            help-error-flex="flex-col"
                            :required="true"
                          />
                        </div>
                      </div>

                      <div class="flex justify-end">
                        <Button
                          type="button"
                          variant="outline"
                          size="sm"
                          @click="addMetaField"
                        >
                          {{ __("Add Meta Tag") }}
                        </Button>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Code Injections -->
                <div class="col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Code Injections") }}
                    </legend>

                    <div class="space-y-6">
                      <p class="text-sm text-orange-500">
                        ⚠️ {{ __("Warning! Code Injection allows you to inject custom HTML/JS/CSS code into all pages of your website. This can be very powerful tool for things like adding Widgets etc. Be sure to validate your code before adding here to prevent breaking the site. Invalid code can lead to site malfunction and you may have to reset SEO settings by running `php artisan settings:seo:reset` command.") }}
                      </p>

                      <XTextarea
                        id="inject_at_head"
                        v-model="form.inject_at_head"
                        :auto-resize="true"
                        :label="__('Inject at <head>')"
                        :placeholder="__('Any content to be injected at <head> tag. Eg: Google Analytics, Custom CSS, Meta tags, etc.')"
                        :error="form.errors.inject_at_head"
                        name="inject_at_head"
                        :rows="4"
                        textarea-class="min-h-[6rem]"
                      />

                      <XTextarea
                        id="inject_at_body_start"
                        v-model="form.inject_at_body_start"
                        :auto-resize="true"
                        :label="__('Inject at <body> start')"
                        :placeholder="__('Any content to be injected after start of <body> tag. Eg: Google Analytics script, etc.')"
                        :error="form.errors.inject_at_body_start"
                        name="inject_at_body_start"
                        :rows="4"
                        textarea-class="min-h-[6rem]"
                      />

                      <XTextarea
                        id="inject_at_body_end"
                        v-model="form.inject_at_body_end"
                        :auto-resize="true"
                        :label="__('Inject at <body> end')"
                        :placeholder="__('Any content to be injected at the end of <body> tag. Eg: Chat widget, Custom JavaScript, etc.')"
                        :error="form.errors.inject_at_body_end"
                        name="inject_at_body_end"
                        :rows="4"
                        textarea-class="min-h-[6rem]"
                      />
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
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
                {{ __("Save SEO Settings") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
