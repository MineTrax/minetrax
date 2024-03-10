<template>
  <AdminLayout>
    <app-head :title="__('Add User Badge')" />

    <div class="max-w-6xl px-10 py-12 mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Add User Badge") }}
        </h1>
        <inertia-link
          :href="route('admin.badge.index')"
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
                {{ __("Badges are something you assign to users for some achievements.") }}<br>{{ __("Eg: You can create a badge for Special User, Top Donator, Most Active etc.") }}
              </p>
              <p class="mt-3 text-sm text-gray-600 dark:text-gray-500">
                {{ __("By default badges are marked non sticky. Means it will only show when you visit user's profile page. If you want the badge to always display beside username tick the 'Is Sticky' checkbox.") }}
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="addBadge">
              <div class="overflow-hidden shadow sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-4">
                      <x-input
                        id="name"
                        v-model="form.name"
                        :label="__('Badge Name')"
                        :help="__('Eg: Special')"
                        :error="form.errors.name"
                        type="text"
                        name="name"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                      <x-input
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
                      <x-input
                        id="sort_order"
                        v-model="form.sort_order"
                        :label="__('Sort Order')"
                        :error="form.errors.sort_order"
                        type="number"
                        name="sort_order"
                      />
                    </div>


                    <div class="flex items-center col-span-6 sm:col-span-3">
                      <x-checkbox
                        id="is_sticky"
                        v-model="form.is_sticky"
                        :label="__('Is Sticky')"
                        :help="__('Tick if you want this badge to always appear with username')"
                        name="is_sticky"
                        :error="form.errors.is_sticky"
                      />
                    </div>


                    <div class="col-span-6 sm:col-span-6">
                      <!-- Profile Photo File Input -->
                      <input
                        id="badge_img"
                        ref="photo"
                        type="file"
                        class="hidden"
                        @change="updatePhotoPreview"
                      >

                      <label
                        for="badge_img"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >{{ __("Badge Icon Image (A small square(Eg: 50x50) image is recomended)") }}</label>


                      <div
                        v-show="photoPreview"
                        class="mt-2"
                      >
                        <span
                          class="block w-20 h-20"
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
                  </div>
                </div>
                <div class="flex justify-end px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Add Badge") }}
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
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        XInput,
        XCheckbox
    },
    data() {
        return {
            form: useForm({
                name: '',
                shortname: '',
                sort_order: '',
                is_sticky: false,
                photo: null,
            }),
            photoPreview: null,
        };
    },

    methods: {
        addBadge() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0];
            }

            this.form.post(route('admin.badge.store'), {
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
