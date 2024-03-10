<template>
  <AdminLayout>
    <app-head :title="`Edit Download #${download.id}`" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Edit Download") }}
        </h1>
        <inertia-link
          :href="route('admin.download.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
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
                {{ __("Using downloads you can safely provide your users way to download anything like resource packs etc.") }}
              </p>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                {{ __("You can restrict the download to a paricular role and even hide actual external download URL from end user by stream the file directly from within minetrax.") }}
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="updateDownload">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="name"
                        v-model="form.name"
                        :label="__('Download Name/Title')"
                        :error="form.errors.name"
                        type="text"
                        name="name"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <textarea
                        id="description"
                        v-model="form.description"
                        aria-label="description"
                        name="description"
                        class="mt-1 focus:ring-light-blue-500 focus:border-light-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      />
                      <jet-input-error
                        :message="form.errors.description"
                        class="mt-2"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-3">
                      <x-checkbox
                        id="is_external"
                        :model-value="download.is_external"
                        :disabled="true"
                        :label="__('External URL')"
                        :help="__('If you want to link to an external file, check this.')"
                        :error="form.errors.is_external"
                      />
                    </div>

                    <div
                      v-if="download.is_external"
                      class="flex items-center col-span-6 sm:col-span-3"
                    >
                      <x-checkbox
                        id="is_external_url_hidden"
                        v-model="form.is_external_url_hidden"
                        :label="__('Hide External URL')"
                        :help="__('Hide the actual external URL from end users.')"
                        name="is_external_url_hidden"
                        :error="form.errors.is_external_url_hidden"
                      />
                    </div>

                    <div
                      v-if="download.is_external"
                      class="col-span-6 sm:col-span-6"
                    >
                      <x-input
                        id="file_url"
                        :disabled="true"
                        :model-value="download.file_url"
                        :label="__('File Download URL')"
                        :error="form.errors.file_url"
                        :help="__('Eg: http://s3.amazonaws.com/bucket/file.zip')"
                        type="text"
                        name="file_url"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div
                      v-if="download.is_external"
                      class="col-span-6 sm:col-span-6"
                    >
                      <x-input
                        id="file_name"
                        v-model="form.file_name"
                        :label="__('File Name (with extension)')"
                        :error="form.errors.file_name"
                        :help="__('Eg: file.zip')"
                        type="text"
                        name="file_name"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div
                      v-if="!form.is_external"
                      class="col-span-6 sm:col-span-6"
                    >
                      <label
                        for="file"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >{{ __("File") }}</label>
                      <span class="block p-2 w-full text-sm text-gray-900 border border-gray-300 cursor-default rounded-lg bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                        {{ download.file?.file_name }}
                      </span>
                      <jet-input-error
                        :message="form.errors.file"
                        class="mt-2"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-3">
                      <x-checkbox
                        id="is_only_auth"
                        v-model="form.is_only_auth"
                        :label="__('Authenticated Users Only')"
                        :help="__('If only authenticated users should be able to view & download this file.')"
                        name="is_only_auth"
                        :error="form.errors.is_only_auth"
                      />
                    </div>

                    <div
                      v-if="form.is_only_auth"
                      class="col-span-6 sm:col-span-3"
                    >
                      <x-input
                        id="min_role_weight_required"
                        v-model="form.min_role_weight_required"
                        :label="__('Minimum Role Weight')"
                        :help="__('The minimum role weight required to download. Leave empty to allow all authenticated users.')"
                        :error="form.errors.min_role_weight_required"
                        type="number"
                        name="min_role_weight_required"
                        help-error-flex="flex-row"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-6">
                      <x-checkbox
                        id="is_active"
                        v-model="form.is_active"
                        :label="__('Active')"
                        :help="__('Active downloads are visible to end users.')"
                        name="is_active"
                        :error="form.errors.is_active"
                      />
                    </div>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Update Download") }}
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
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import EasyMDE from 'easymde';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        JetInputError,
        LoadingButton,
        XInput,
        XCheckbox,
    },
    props: {
        download: Object
    },
    data() {
        return {
            form: useForm({
                name: this.download.name,
                description: this.download.description,
                file_name: this.download.file_name,
                is_external_url_hidden: this.download.is_external_url_hidden,
                is_only_auth: this.download.is_only_auth,
                min_role_weight_required: this.download.min_role_weight_required,
                is_active: this.download.is_active,
                '_method': 'PUT',
            }),
            easyMDE: null
        };
    },

    mounted() {
        this.easyMDE = new EasyMDE({
            previewClass: 'editor-preview prose max-w-none'
        });
    },

    methods: {
        updateDownload() {
            this.form.description = this.easyMDE.value();

            this.form.post(route('admin.download.update', this.download.id), {});
        },
    }
};
</script>

