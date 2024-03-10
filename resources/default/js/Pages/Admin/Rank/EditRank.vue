<template>
  <AdminLayout>
    <app-head :title="__('Edit Rank - :name', {name: rank.name})" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Edit Rank") }}
        </h1>
        <inertia-link
          :href="route('admin.rank.index')"
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
                {{ __("Ranks are assigned to players when a given criteria is matched, eg: play time, score etc.") }}<br>{{ __("Play Time Need should be provided in Seconds. So if you want to add criteria to give rank if player played at-least 1 minute then you write 60") }}
              </p>
              <p class="mt-3 text-sm text-gray-600 dark:text-gray-500">
                {{ __("Each rank is sorted according to its weight. More the score and time is more the weight.") }}
              </p>
              <p class="mt-3 text-sm text-gray-600 dark:text-gray-500">
                {{ __("If you instead want to sync player rank from server to web, you can do that too from Settings -> Player Settings. Currently Luckperms is supported. Make sure to have same Short Name for rank name what you have chosen in Luckperms. While rank sync is enabled the criteria like score and time will be ignored but while adding ranks it is recommended to enter something in them so web know which rank has more weight.") }}
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form
              enctype="multipart/form-data"
              @submit.prevent="updateRank"
            >
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-4">
                      <x-input
                        id="name"
                        v-model="form.name"
                        :label="__('Rank Name')"
                        :help="__('Eg: Knight')"
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
                        :help="__('Eg: knight')"
                        :error="form.errors.shortname"
                        type="text"
                        name="shortname"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="total_score_needed"
                        v-model="form.total_score_needed"
                        :label="__('Score Needed')"
                        :error="form.errors.total_score_needed"
                        type="number"
                        name="total_score_needed"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="total_play_time_needed"
                        v-model="form.total_play_time_needed"
                        :label="__('Play Time Needed')"
                        :error="form.errors.total_play_time_needed"
                        type="number"
                        name="total_play_time_needed"
                      />
                    </div>


                    <div class="col-span-6 sm:col-span-2">
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
                      >{{ __("Rank Image") }}</label>


                      <!-- New Profile Photo Preview -->
                      <div
                        v-show="photoPreview"
                        class="mt-2"
                      >
                        <span
                          class="block rounded-full w-20 h-20"
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

                    <div class="col-span-6 sm:col-span-4">
                      <x-textarea
                        id="description"
                        v-model="form.description"
                        :rows="10"
                        :label="__('Description')"
                        :error="form.errors.description"
                        name="description"
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
                    {{ __("Edit Rank") }}
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
import XTextarea from '@/Components/Form/XTextarea.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XTextarea,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        XInput
    },
    props: {
        rank: Object
    },
    data() {
        return {
            form: useForm({
                name: this.rank.name,
                shortname: this.rank.shortname,
                description: this.rank.description,
                total_score_needed: this.rank.total_score_needed,
                total_play_time_needed: this.rank.total_play_time_needed,
                photo: null,
                '_method': 'PUT'
            }),
            photoPreview: null,
        };
    },

    methods: {
        updateRank() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0];
            }

            this.form.post(route('admin.rank.update', this.rank.id), {
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
