<template>
  <AdminLayout>
    <app-head title="Create News" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Create News") }}
        </h1>
        <inertia-link
          :href="route('admin.news.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
              <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-400">
                {{ __("Overview") }}
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                {{ __("In news you can do announcements or other generic news about your server.") }}
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="addNews">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
                      <x-select
                        id="type"
                        v-model="form.type"
                        name="type"
                        :error="form.errors.type"
                        :label="__('News Category')"
                        :select-list="{0: __('General'), 1: __('Announcement'), 2: __('Event')}"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="title"
                        v-model="form.title"
                        :label="__('Title')"
                        :error="form.errors.title"
                        type="text"
                        name="title"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
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

                    <div class="col-span-6 sm:col-span-3">
                      <!-- Profile Photo File Input -->
                      <input
                        id="photo"
                        ref="photo"
                        type="file"
                        class="hidden"
                        @change="updatePhotoPreview"
                      >

                      <label
                        for="photo"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >{{ __("Image") }}</label>

                      <!-- New Profile Photo Preview -->
                      <div
                        v-show="photoPreview"
                        class="mt-2"
                      >
                        <span
                          class="block rounded w-48 h-32"
                          :style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'"
                        />
                      </div>

                      <jet-secondary-button
                        class="mt-2 mr-2"
                        type="button"
                        @click.prevent="selectNewPhoto"
                      >
                        {{ __("Select A New Image") }}
                      </jet-secondary-button>

                      <jet-input-error
                        :message="form.errors.photo"
                        class="mt-2"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-3">
                      <fieldset>
                        <legend class="text-base font-medium text-gray-900 dark:text-gray-400">
                          {{ __("Options") }}
                        </legend>
                        <div class="mt-4 flex space-x-4">
                          <div class="flex items-start">
                            <div class="flex items-center h-5">
                              <input
                                id="is_published"
                                v-model="form.is_published"
                                name="is_published"
                                type="checkbox"
                                class="focus:ring-light-blue-400 h-4 w-4 text-light-blue-500 border-gray-300 rounded dark:border-gray-900 rounded dark:bg-cool-gray-900"
                              >
                            </div>
                            <div class="ml-3 text-sm">
                              <label
                                for="is_published"
                                class="font-medium text-gray-700 dark:text-gray-300"
                              >{{ __("Published") }}</label>
                              <!--                                                            <p class="text-gray-500">Get notified when someones posts a comment on a posting.</p>-->
                            </div>
                          </div>
                          <div class="flex items-start">
                            <div class="flex items-center h-5">
                              <input
                                id="is_pinned"
                                v-model="form.is_pinned"
                                name="is_pinned"
                                type="checkbox"
                                class="focus:ring-light-blue-400 h-4 w-4 text-light-blue-500 border-gray-300 rounded dark:border-gray-900 rounded dark:bg-cool-gray-900"
                              >
                            </div>
                            <div class="ml-3 text-sm">
                              <label
                                for="is_pinned"
                                class="font-medium text-gray-700 dark:text-gray-300"
                              >{{ __("Pinned") }}</label>
                              <!--                                                            <p class="text-gray-500">Get notified when a candidate applies for a job.</p>-->
                            </div>
                          </div>
                        </div>
                        <jet-input-error
                          :message="form.errors.is_pinned"
                          class="mt-2"
                        />
                        <jet-input-error
                          :message="form.errors.is_published"
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
                    {{ __("Add News") }}
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

<script>
import JetInputError from '@/Jetstream/InputError.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import EasyMDE from 'easymde';
import XSelect from '@/Components/Form/XSelect.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XSelect,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        XInput
    },
    data() {
        return {
            form: useForm({
                title: '',
                body: '',
                type: 0,
                is_published: true,
                is_pinned: false,
                photo: null,
            }),
            photoPreview: null,
            easyMDE: null
        };
    },

    mounted() {
        this.easyMDE = new EasyMDE({
            previewClass: 'editor-preview prose max-w-none'
        });
    },

    methods: {
        addNews() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0];
            }

            this.form.body = this.easyMDE.value();

            this.form.post(route('admin.news.store'), {});
        },
        updatePhotoPreview() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.photoPreview = e.target.result;
            };

            reader.readAsDataURL(this.$refs.photo.files[0]);
        },
        selectNewPhoto() {
            this.$refs.photo.click();
        },
    }
};
</script>
