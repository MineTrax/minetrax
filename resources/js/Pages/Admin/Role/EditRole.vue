<template>
  <AdminLayout>
    <app-head :title="__('Edit Role - :name', {name: role.name})" />

    <div class="max-w-6xl px-10 py-12 mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Edit Role") }}
        </h1>
        <inertia-link
          :href="route('admin.role.index')"
          class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-400 border border-transparent rounded-md dark:bg-gray-600 hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray"
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
                {{ __("Roles are used to authorize registered users of your website to perform actions on web like ban, mute users etc.") }}
                <br>
                {{ __("For each role you can customize 'Web Message Format' which is the in-game chat format.") }}
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="updateRole">
              <div class="shadow sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
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
                      >{{ __("Role Image") }}</label>

                      <!-- New Profile Photo Preview -->
                      <div
                        v-show="photoPreview"
                        class="mt-2"
                      >
                        <span
                          class="block w-2/5 h-12"
                          :style="'background-size: fill; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'"
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

                    <div
                      v-tippy
                      content="Role name cannot be changed!"
                      class="col-span-6 sm:col-span-3 focus:outline-none"
                    >
                      <x-input
                        id="name"
                        v-model="form.name"
                        v-tippy
                        :label="__('Role Name')"
                        :help="__('Eg: superadmin')"
                        :error="form.errors.name"
                        type="text"
                        name="name"
                        :disabled="true"
                        :title="__('Role name cannot be changed!')"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
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
                      <x-input
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
                      <x-input
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
                      <x-input
                        id="weight"
                        v-model="form.weight"
                        :label="__('Weight')"
                        :help="__('More important the role more the weight')"
                        :error="form.errors.weight"
                        type="number"
                        name="weight"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-4">
                      <x-checkbox
                        id="is_staff"
                        v-model="form.is_staff"
                        :label="__('Is Staff')"
                        :help="__('This role is a staff member. This flag is used to let other know staff and show admin panel.')"
                        name="is_staff"
                        :error="form.errors.is_staff"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-4">
                      <x-checkbox
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
                        class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >{{ __("Administrative Permissions") }}</label>
                      <multiselect
                        id="permissions"
                        v-model="form.permissions"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                        :options="permissions"
                        :multiple="true"
                        :close-on-select="false"
                        :clear-on-select="false"
                        :preserve-search="true"
                        :placeholder="__('Search')+'...'"
                      />
                      <jet-input-error
                        :message="form.errors.permissions"
                        class="mt-2"
                      />
                    </div>
                  </div>
                </div>
                <div class="flex justify-end px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Edit Role") }}
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
import Multiselect from 'vue-multiselect';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {

    components: {
        AdminLayout,
        XCheckbox,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        Multiselect,
        XInput
    },
    props: {
        permissions: Array,
        role: Object
    },
    data() {
        return {
            form: useForm({
                name: this.role.name,
                display_name: this.role.display_name,
                color: this.role.color,
                weight: this.role.weight,
                is_staff: this.role.is_staff,
                is_hidden_from_staff_list: this.role.is_hidden_from_staff_list,
                permissions: this.role.permissions,
                web_message_format: this.role.web_message_format,
                photo: null,
                '_method': 'PUT'
            }),
            photoPreview: null,
        };
    },

    methods: {
        updateRole() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0];
            }

            this.form.post(route('admin.role.update', this.role.id), {
                preserveScroll: true
            });
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

