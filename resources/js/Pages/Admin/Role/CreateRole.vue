<template>
  <app-layout>
    <app-head title="Add User Role" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          Add User Role
        </h1>
        <inertia-link
          :href="route('admin.role.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>Cancel</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
              <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-400">
                Role Information
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                Add a New Rank which Bla Bla Bla
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="addRole">
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
                      >Role Image</label>

                      <!-- New Profile Photo Preview -->
                      <div
                        v-show="photoPreview"
                        class="mt-2"
                      >
                        <span
                          class="block h-12 w-2/5"
                          :style="'background-size: fill; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'"
                        />
                      </div>

                      <jet-secondary-button
                        class="mt-2 mr-2"
                        type="button"
                        @click.native.prevent="selectNewPhoto"
                      >
                        Select A New Image
                      </jet-secondary-button>


                      <jet-input-error
                        :message="form.errors.photo"
                        class="mt-2"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="name"
                        v-model="form.name"
                        label="Role Name"
                        help="Eg: superadmin"
                        :error="form.errors.name"
                        type="text"
                        name="name"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="display_name"
                        v-model="form.display_name"
                        label="Display Name"
                        help="Eg: Super Administrator"
                        :error="form.errors.display_name"
                        type="text"
                        name="display_name"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="web_message_format"
                        v-model="form.web_message_format"
                        label="Web Message Format"
                        help="Eg: &4&b{USERNAME}&r&7"
                        :error="form.errors.web_message_format"
                        type="text"
                        name="web_message_format"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="color"
                        v-model="form.color"
                        label="Color"
                        help="Eg: #ff00ff"
                        :error="form.errors.color"
                        type="text"
                        name="color"
                      />
                    </div>



                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="weight"
                        v-model="form.weight"
                        label="Weight"
                        help="More important the role more the weight"
                        :error="form.errors.weight"
                        type="number"
                        name="weight"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-4">
                      <x-checkbox
                        id="is_staff"
                        v-model="form.is_staff"
                        label="Is Staff"
                        help="This role is a staff member. This flag is used to let other know staff and show admin panel."
                        name="is_staff"
                        :error="form.errors.is_staff"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-4">
                      <x-checkbox
                        id="is_hidden_from_staff_list"
                        v-model="form.is_hidden_from_staff_list"
                        label="Is Hidden from List"
                        help="Hide this role from staff list."
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
                      >Administrative Permissions</label>
                      <multiselect
                        id="permissions"
                        v-model="form.permissions"
                        class="mt-1 focus:ring-light-blue-500 focus:border-light-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                        :options="permissions"
                        :multiple="true"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :preserve-search="true"
                        placeholder="Search..."
                      />
                      <jet-input-error
                        :message="form.errors.permissions"
                        class="mt-2"
                      />
                    </div>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    Add Role
                  </loading-button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import JetSectionBorder from '@/Jetstream/SectionBorder';
import JetInputError from '@/Jetstream/InputError';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import LoadingButton from '@/Components/LoadingButton';
import XInput from '@/Components/Form/XInput';
import Multiselect from 'vue-multiselect';
import XCheckbox from '@/Components/Form/XCheckbox';

export default {

    components: {
        XCheckbox,
        AppLayout,
        JetSectionBorder,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        Multiselect,
        XInput
    },
    props: {
        permissions: Array
    },
    data() {
        return {
            form: this.$inertia.form({
                name: '',
                is_staff: false,
                is_hidden_from_staff_list: false,
                display_name: '',
                color: null,
                weight: null,
                permissions: [],
                web_message_format: null,
                photo: null,
            }),
            photoPreview: null,
        };
    },

    methods: {
        addRole() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0];
            }

            this.form.post(route('admin.role.store'), {
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

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
