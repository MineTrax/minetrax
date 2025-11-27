<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import ImageUpload from '@/Components/Form/ImageUpload.vue';

const { __ } = useTranslations();

const props = defineProps({
    badge: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Badges'),
        url: route('admin.badge.index'),
        current: false,
    },
    {
        text: __('Edit Badge'),
        current: true,
    },
    {
        text: '#' + props.badge.id,
        current: true,
    }
];

const form = useForm({
    name: props.badge.name,
    shortname: props.badge.shortname,
    sort_order: props.badge.sort_order,
    is_sticky: props.badge.is_sticky,
    photo: null,
    '_method': 'PUT'
});

function updateBadge() {
    form.post(route('admin.badge.update', props.badge.id), {
        preserveScroll: true
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Badge - :name', { name: badge.name })" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateBadge">
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6">
                  <ImageUpload
                    id="photo"
                    name="photo"
                    :label="__('Badge Icon Image')"
                    :hint="__('A small square (Eg: 50x50) image is recommended')"
                    :current-url="badge.photo_url"
                    v-model="form.photo"
                    :error="form.errors.photo"
                    :removable="false"
                    shape="rect"
                    :preview-class="'h-32 w-32'"
                    :upload-label="__('Upload')"
                    :change-label="__('Change')"
                    object-fit="contain"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Badge Name')"
                    :help="__('Eg: Special')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="shortname"
                    v-model="form.shortname"
                    :label="__('Short Name')"
                    :help="__('Eg: special')"
                    :error="form.errors.shortname"
                    type="text"
                    name="shortname"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="sort_order"
                    v-model="form.sort_order"
                    :label="__('Sort Order')"
                    :error="form.errors.sort_order"
                    type="number"
                    name="sort_order"
                  />
                </div>

                <div class="col-span-6">
                  <XSwitch
                    id="is_sticky"
                    v-model="form.is_sticky"
                    :label="__('Is Sticky')"
                    :help="__('Tick if you want this badge to always appear with username')"
                    name="is_sticky"
                    :error="form.errors.is_sticky"
                  />
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.badge.index')">
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
                {{ __("Update Badge") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
