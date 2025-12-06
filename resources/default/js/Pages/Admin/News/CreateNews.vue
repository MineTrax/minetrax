<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import ImageUpload from '@/Components/Form/ImageUpload.vue';
import TipTapEditor from '@/Components/TipTapEditor.vue';
import { ref } from 'vue';

const { __ } = useTranslations();

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('News'),
        url: route('admin.news.index'),
        current: false,
    },
    {
        text: __('Create News'),
        current: true,
    }
];



const form = useForm({
    title: '',
    body: '',
    type: '0',
    is_published: true,
    is_pinned: false,
    is_commentable: true,
    photo: null,
});



function addNews() {
    form.post(route('admin.news.store'), {
        preserveScroll: true
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Create News')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="addNews">
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XSelect
                    id="type"
                    v-model="form.type"
                    name="type"
                    :error="form.errors.type"
                    :label="__('News Category')"
                    :select-list="{'0': __('General'), '1': __('Announcement'), '2': __('Event')}"
                    :disable-null="true"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <ImageUpload
                    id="photo"
                    name="photo"
                    :label="__('Featured Image (Optional)')"
                    v-model="form.photo"
                    :error="form.errors.photo"
                    :removable="false"
                    shape="rect"
                    :preview-class="'h-64 w-full'"
                    :upload-label="__('Upload')"
                    :change-label="__('Change')"
                  />
                </div>

                <div class="col-span-6">
                  <XInput
                    id="title"
                    v-model="form.title"
                    :label="__('Title')"
                    :error="form.errors.title"
                    type="text"
                    name="title"
                  />
                </div>

                <div class="col-span-6">
                  <label
                    for="body"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Content") }}</label>
                  <TipTapEditor
                    id="body"
                    v-model="form.body"
                    class="min-h-[400px]"
                  />
                  <p
                    v-if="form.errors.body"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.body }}
                  </p>
                </div>

                <div class="col-span-6">
                  <div class="flex flex-wrap gap-6">
                    <XSwitch
                      id="is_published"
                      v-model="form.is_published"
                      :label="__('Published')"
                      name="is_published"
                      :error="form.errors.is_published"
                    />
                    <XSwitch
                      id="is_pinned"
                      v-model="form.is_pinned"
                      :label="__('Pinned')"
                      name="is_pinned"
                      :error="form.errors.is_pinned"
                    />
                    <XSwitch
                      id="is_commentable"
                      v-model="form.is_commentable"
                      :label="__('Allow Comments')"
                      name="is_commentable"
                      :error="form.errors.is_commentable"
                    />
                  </div>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.news.index')">
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
                {{ __("Create News") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
