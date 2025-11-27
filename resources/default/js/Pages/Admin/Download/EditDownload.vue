<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import { onMounted } from 'vue';
import EasyMDE from 'easymde';

const { __ } = useTranslations();

const props = defineProps({
    download: Object
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Downloads'),
        url: route('admin.download.index'),
        current: false,
    },
    {
        text: __('Edit Download'),
        current: true,
    }
];

let easyMDE = null;

const form = useForm({
    name: props.download.name,
    description: props.download.description,
    file_name: props.download.file_name,
    is_external_url_hidden: props.download.is_external_url_hidden,
    is_only_auth: props.download.is_only_auth,
    min_role_weight_required: props.download.min_role_weight_required,
    is_active: props.download.is_active,
    '_method': 'PUT',
});

onMounted(() => {
    easyMDE = new EasyMDE({
        previewClass: 'editor-preview prose max-w-none'
    });
});

function updateDownload() {
    form.description = easyMDE.value();

    form.post(route('admin.download.update', props.download.id), {});
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Download #:id', { id: download.id })" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateDownload">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-6">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Download Name/Title')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                  />
                </div>

                <div class="col-span-6 sm:col-span-6">
                  <XTextarea
                    id="description"
                    v-model="form.description"
                    :label="__('Description')"
                    :error="form.errors.description"
                    name="description"
                  />
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_external"
                    :model-value="download.is_external"
                    :disabled="true"
                    :label="__('External URL')"
                    :help="__('If you want to link to an external file, check this.')"
                  />
                </div>

                <div
                  v-if="download.is_external"
                  class="flex items-center col-span-6 sm:col-span-3"
                >
                  <XSwitch
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
                  <XInput
                    id="file_url"
                    :disabled="true"
                    :model-value="download.file_url"
                    :label="__('File Download URL')"
                    :help="__('Eg: http://s3.amazonaws.com/bucket/file.zip')"
                    type="text"
                    name="file_url"
                  />
                </div>

                <div
                  v-if="download.is_external"
                  class="col-span-6 sm:col-span-6"
                >
                  <XInput
                    id="file_name"
                    v-model="form.file_name"
                    :label="__('File Name (with extension)')"
                    :error="form.errors.file_name"
                    :help="__('Eg: file.zip')"
                    type="text"
                    name="file_name"
                  />
                </div>

                <div
                  v-if="!download.is_external"
                  class="col-span-6 sm:col-span-6"
                >
                  <label
                    for="file"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("File") }}</label>
                  <span class="block p-2 w-full text-sm text-muted-foreground border border-input cursor-default rounded-lg bg-muted">
                    {{ download.file?.file_name }}
                  </span>
                </div>

                <div class="flex items-center col-span-6 sm:col-span-3">
                  <XSwitch
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
                  <XInput
                    id="min_role_weight_required"
                    v-model="form.min_role_weight_required"
                    :label="__('Minimum Role Weight')"
                    :help="__('The minimum role weight required to download. Leave empty to allow all authenticated users.')"
                    :error="form.errors.min_role_weight_required"
                    type="number"
                    name="min_role_weight_required"
                  />
                </div>

                <div class="flex items-center col-span-6 sm:col-span-6">
                  <XSwitch
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
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.download.index')">
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
                {{ __("Update Download") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
