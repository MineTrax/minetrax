<template>
  <AdminLayout>
    <app-head :title="__('Add Player Rank')" />

    <div class="max-w-6xl px-10 py-12 mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Add Player Rank") }}
        </h1>
        <inertia-link
          :href="route('admin.rank.index')"
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
            <form @submit.prevent="addRank">
              <div class="overflow-hidden shadow sm:rounded-md">
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
                        id="rank_img"
                        ref="photo"
                        type="file"
                        class="hidden"
                        @change="updatePhotoPreview"
                      >

                      <label
                        for="rank_img"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >{{ __("Rank Image") }}</label>


                      <div
                        v-show="photoPreview"
                        class="mt-2"
                      >
                        <span
                          class="block w-20 h-20 rounded-full"
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
                <div class="flex justify-end px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Add Rank") }}
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
import JetInputError from '@/Jetstream/InputError.vue';
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
    data() {
        return {
            form: useForm({
                name: '',
                shortname: '',
                description: '',
                total_score_needed: '',
                total_play_time_needed: '',
                photo: null,
            }),
            photoPreview: null,
        };
    },

    methods: {
        addRank() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0];
            }

            this.form.post(route('admin.rank.store'), {
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
