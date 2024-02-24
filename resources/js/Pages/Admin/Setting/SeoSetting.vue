<template>
  <AdminLayout>
    <app-head
      :title="__('SEO Settings')"
    />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full rounded">
            <div class="px-6 py-4 border-b dark:border-gray-700 dark:text-gray-300 font-bold">
              {{ __("SEO Settings") }}
            </div>

            <div class="mt-10 sm:mt-0">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="mt-5 md:mt-0 md:col-span-3">
                  <form
                    autocomplete="off"
                    @submit.prevent="saveSetting"
                  >
                    <div class="shadow overflow-hidden sm:rounded-md">
                      <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                          <!-- Favicon -->
                          <div
                            class="col-span-6 sm:col-span-6"
                          >
                            <input
                              id="favicon"
                              ref="favicon"
                              type="file"
                              class="hidden"
                              accept="image/png, image/vnd.microsoft.icon, image/svg+xml"
                              @change="updateFaviconPreview"
                            >

                            <label
                              for="favicon"
                              class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >{{ __("Favicon Image") }}</label>

                            <div
                              v-show="!faviconPreview"
                              class="mt-2 flex items-center p-6"
                            >
                              <img
                                v-if="settings.favicon_path"
                                :src="settings.favicon_path"
                                alt="favicon"
                                class="h-14 w-14"
                              >
                              <span
                                v-else
                                class="text-gray-400 italic text-xs"
                              >
                                {{ __("No favicon.") }}
                              </span>
                            </div>

                            <div
                              v-show="faviconPreview"
                              class="mt-2 p-4"
                            >
                              <span
                                class="block rounded h-14 w-14"
                                :style="`background-size: contain; background-repeat: no-repeat; background-position: center center; background-image: url(${faviconPreview});`"
                              />
                            </div>

                            <JetSecondaryButton
                              class="mt-2 mr-2"
                              type="button"
                              @click.prevent="selectFaviconImage"
                            >
                              {{ __("Select A New Image") }}
                            </JetSecondaryButton>

                            <div class="mt-2 text-xs text-gray-400">
                              {{ __("Allowed") }}: .png, .ico, .svg
                            </div>

                            <jet-input-error
                              :message="form.errors.favicon"
                              class="mt-2"
                            />
                          </div>

                          <div
                            class="col-span-3 sm:col-span-4"
                          >
                            <XInput
                              id="title_home"
                              v-model="form.title_home"
                              :label="__('Title Main')"
                              :error="form.errors.title_home"
                              help="Eg: MineTrax World - Play, Build, Survive, Explore"
                              type="text"
                              name="title_home"
                            />
                          </div>
                          <div
                            class="col-span-3 sm:col-span-2"
                          >
                            <XInput
                              id="title_suffix"
                              v-model="form.title_suffix"
                              :label="__('Title Suffix')"
                              :error="form.errors.title_suffix"
                              help="Eg: ◾ MineTrax.live"
                              type="text"
                              name="title_suffix"
                            />
                          </div>

                          <div class="flex-col col-span-6 space-y-1 sm:col-span-6">
                            <legend class="text-base font-medium text-gray-900 dark:text-gray-300">
                              {{ __("Meta Fields") }}
                            </legend>

                            <div class="w-full space-y-1">
                              <div class="flex space-x-4">
                                <div class="w-5" />
                                <label
                                  class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >{{
                                   __("Name") }}
                                  <span class="text-xs text-gray-500"> (Eg: description, keywords, title)</span>
                                </label>
                                <label
                                  class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >{{
                                  __("Content") }}
                                </label>
                              </div>

                              <div
                                v-for="(
                                  field, index
                                ) in form.meta"
                                :key="index"
                                class="flex space-x-4"
                              >
                                <button
                                  type="button"
                                  class="focus:outline-none group"
                                  @click="
                                    removeMetaField(index)
                                  "
                                >
                                  <Icon
                                    class="w-5 h-5 text-gray-300 group-hover:text-red-500"
                                    name="trash"
                                  />
                                </button>
                                <div class="flex-1">
                                  <x-input
                                    v-model="field.name
                                    "
                                    :label="__(
                                      'Meta Name :index',
                                      {
                                        index:
                                          index +
                                          1,
                                      }
                                    )
                                    "
                                    :error="form.errors[
                                      `meta.${index}.name`
                                    ]"
                                    type="text"
                                    help-error-flex="flex-col"
                                    :required="true"
                                  />
                                </div>
                                <div class="flex-1">
                                  <x-input
                                    v-model="field.content
                                    "
                                    :label="__(
                                      'Meta Content :index',
                                      {
                                        index:
                                          index +
                                          1,
                                      }
                                    )
                                    "
                                    :error="form.errors[
                                      `meta.${index}.content`
                                    ]
                                    "
                                    type="text"
                                    help-error-flex="flex-col"
                                    :required="true"
                                  />
                                </div>
                              </div>

                              <div class="flex justify-end mt-1">
                                <button
                                  type="button"
                                  class="p-1.5 text-xs text-light-blue-500 rounded border border-light-blue-500 focus:outline-none hover:text-light-blue-300 hover:border-light-blue-300 transition ease-in-out duration-150"
                                  @click="addMetaField"
                                >
                                  {{
                                    __("Add Meta Tag")
                                  }}
                                </button>
                              </div>
                            </div>
                          </div>

                          <!-- <div
                            class="col-span-6 sm:col-span-6"
                          >
                            <XTextarea
                              id="robots_txt"
                              v-model="form.robots_txt"
                              :auto-resize="true"
                              :label="__('Robots.txt')"
                              :help="__('Robots.txt file content.')"
                              :error="form.errors.robots_txt"
                              name="robots_txt"
                            />
                          </div> -->

                          <div class="col-span-6 sm:col-span-6">
                            <legend class="text-base font-medium text-gray-900 dark:text-gray-300">
                              {{ __("Code Injections") }}
                            </legend>
                            <p class="text-sm text-orange-500">
                              ⚠️ {{ __("Warning! Code Injection allows you to inject custom HTML/JS/CSS code into all pages of your website. This can be very powerful tool for things like adding Widgets etc. Be sure to validate your code before adding here to prevent breaking the site. Invalid code can lead to site malfunction and you may have to reset SEO settings by running `php artisan settings:seo:reset` command.") }}
                            </p>
                          </div>

                          <div
                            class="col-span-6 sm:col-span-6"
                          >
                            <XTextarea
                              id="inject_at_head"
                              v-model="form.inject_at_head"
                              :auto-resize="true"
                              :label="__('Inject at <head>')"
                              :help="__('Any content to be injected at <head> tag. Eg: Google Analytics, Custom CSS, Meta tags, etc.')"
                              :error="form.errors.inject_at_head"
                              name="inject_at_head"
                            />
                          </div>


                          <div
                            class="col-span-6 sm:col-span-6"
                          >
                            <XTextarea
                              id="inject_at_body_start"
                              v-model="form.inject_at_body_start"
                              :auto-resize="true"
                              :label="__('Inject at <body> start')"
                              :help="__('Any content to be injected after start of <body> tag. Eg: Google Analytics script, etc.')"
                              :error="form.errors.inject_at_body_start"
                              name="inject_at_body_start"
                            />
                          </div>

                          <div
                            class="col-span-6 sm:col-span-6"
                          >
                            <XTextarea
                              id="inject_at_body_end"
                              v-model="form.inject_at_body_end"
                              :auto-resize="true"
                              :label="__('Inject at <body> end')"
                              :help="__('Any content to be injected at the end of <body> tag. Eg: Chat widget, Custom JavaScript, etc.')"
                              :error="form.errors.inject_at_body_end"
                              name="inject_at_body_end"
                            />
                          </div>
                        </div>
                      </div>
                      <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                        <LoadingButton
                          :loading="form.processing"
                          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600"
                          type="submit"
                        >
                          {{ __("Save SEO Settings") }}
                        </LoadingButton>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import Icon from '@/Components/Icon.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
    servers: Object
});

const form = useForm({
    'title_home': props.settings.title_home,
    'title_suffix': props.settings.title_suffix,
    'meta' : props.settings.meta,
    // 'robots_txt' : props.settings.robots_txt,
    'inject_at_head' : props.settings.inject_at_head,
    'inject_at_body_start' : props.settings.inject_at_body_start,
    'inject_at_body_end' : props.settings.inject_at_body_end,
    'favicon' : null,
});

let faviconPreview = ref(null);
let favicon = ref(null);

function selectFaviconImage() {
    favicon.value.click();
}

function updateFaviconPreview() {
    const reader = new FileReader();

    reader.onload = (e) => {
        faviconPreview.value = e.target.result;
    };

    reader.readAsDataURL(favicon.value.files[0]);
}

const saveSetting = () => {
    if (favicon.value) {
        form.favicon = favicon.value.files[0];
    }
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
