<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import ImageUpload from '@/Components/Form/ImageUpload.vue';
import Multiselect from 'vue-multiselect';

const { __ } = useTranslations();

const props = defineProps({
    permissions: Array,
    role: Object
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Roles'),
        url: route('admin.role.index'),
        current: false,
    },
    {
        text: __('Edit Role'),
        current: true,
    },
    {
        text: props.role.name || props.role.id,
        current: true,
    }
];

const form = useForm({
    name: props.role.name,
    display_name: props.role.display_name,
    color: props.role.color,
    weight: props.role.weight,
    is_staff: props.role.is_staff,
    is_hidden_from_staff_list: props.role.is_hidden_from_staff_list,
    permissions: props.role.permissions,
    web_message_format: props.role.web_message_format,
    photo: null,
    '_method': 'PUT'
});

function updateRole() {
    form.post(route('admin.role.update', props.role.id), {
        preserveScroll: true
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Role - :name', {name: role.name})" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateRole">
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6">
                  <ImageUpload
                    id="photo"
                    name="photo"
                    :label="__('Role Image (Optional)')"
                    :current-url="role.photo_url"
                    v-model="form.photo"
                    :error="form.errors.photo"
                    :removable="false"
                    shape="rect"
                    :preview-class="'h-16 w-full max-w-xs'"
                    :upload-label="__('Upload')"
                    :change-label="__('Change')"
                    object-fit="contain"
                  />
                </div>

                <div
                  v-tippy
                  :content="__('Role name cannot be changed!')"
                  class="col-span-6 sm:col-span-3 focus:outline-none"
                >
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Role Name')"
                    :help="__('Eg: superadmin')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                    :disabled="true"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="display_name"
                    v-model="form.display_name"
                    :label="__('Display Name')"
                    :help="__('Eg: Super Administrator')"
                    :error="form.errors.display_name"
                    type="text"
                    name="display_name"
                  />
                </div>

                <div class="col-span-6 sm:col-span-6">
                  <XInput
                    id="web_message_format"
                    v-model="form.web_message_format"
                    :label="__('Web Message Format')"
                    :help="__('Eg: &4&b{USERNAME}&r&7')"
                    :error="form.errors.web_message_format"
                    type="text"
                    name="web_message_format"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="color"
                    v-model="form.color"
                    :label="__('Color')"
                    :help="__('Eg: #ff00ff')"
                    :error="form.errors.color"
                    type="text"
                    name="color"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="weight"
                    v-model="form.weight"
                    :label="__('Weight')"
                    :help="__('More important the role more the weight')"
                    :error="form.errors.weight"
                    type="number"
                    name="weight"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_staff"
                    v-model="form.is_staff"
                    :label="__('Is Staff')"
                    :help="__('This role is a staff member. This flag is used to let other know staff and show admin panel.')"
                    name="is_staff"
                    :error="form.errors.is_staff"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSwitch
                    id="is_hidden_from_staff_list"
                    v-model="form.is_hidden_from_staff_list"
                    :label="__('Is Hidden from List')"
                    :help="__('Hide this role from staff list.')"
                    name="is_hidden_from_staff_list"
                    :error="form.errors.is_hidden_from_staff_list"
                  />
                </div>

                <div
                  v-show="form.is_staff"
                  class="col-span-6 sm:col-span-6"
                >
                  <label
                    for="permissions"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Administrative Permissions") }}</label>
                  <Multiselect
                    id="permissions"
                    v-model="form.permissions"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="permissions"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    :placeholder="__('Search')+'...'"
                  />
                  <p
                    v-if="form.errors.permissions"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.permissions }}
                  </p>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.role.index')">
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
                {{ __("Update Role") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
