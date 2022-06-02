<template>
  <app-layout>
    <app-head title="Add Player Rank" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          Add Player Rank
        </h1>
        <inertia-link
          :href="route('admin.rank.index')"
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
                Rank Information
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                Add a New Rank which Bla Bla Bla
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="addRank">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-4">
                      <x-input
                        id="name"
                        v-model="form.name"
                        label="Rank Name"
                        help="Eg: Apex Legend"
                        :error="form.errors.name"
                        type="text"
                        name="name"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                      <x-input
                        id="shortname"
                        v-model="form.shortname"
                        label="Short Name"
                        help="Eg: apexlegend"
                        :error="form.errors.shortname"
                        type="text"
                        name="shortname"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="total_score_needed"
                        v-model="form.total_score_needed"
                        label="Score Needed"
                        :error="form.errors.total_score_needed"
                        type="number"
                        name="total_score_needed"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="total_play_one_minute_needed"
                        v-model="form.total_play_one_minute_needed"
                        label="Play Time Needed"
                        :error="form.errors.total_play_one_minute_needed"
                        type="number"
                        name="total_play_one_minute_needed"
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
                      >Rank Image</label>


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
                        @click.native.prevent="selectNewPhoto"
                      >
                        Select A New Image
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
                        label="Description"
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
                    Add Rank
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
import XTextarea from '@/Components/Form/XTextarea';

export default {
    components: {
        XTextarea,
        AppLayout,
        JetSectionBorder,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        XInput
    },
    data() {
        return {
            form: this.$inertia.form({
                name: '',
                shortname: '',
                description: '',
                total_score_needed: '',
                total_play_one_minute_needed: '',
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
