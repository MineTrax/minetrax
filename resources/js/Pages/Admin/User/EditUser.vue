<template>
  <AdminLayout>
    <app-head :title="__('Edit User @:username', {username: userData.username})" />

    <div class="max-w-5xl px-10 py-12 mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Edit User ':username'", {username: userData.name}) }}
        </h1>
        <inertia-link
          :href="route('admin.user.index')"
          class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-400 border border-transparent rounded-md dark:bg-gray-600 hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>


      <div class="mt-10 sm:mt-0">
        <div class="">
          <div class="mt-5 md:mt-0">
            <form @submit.prevent="updateUserInformation">
              <div class="shadow sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div
                      v-if="$page.props.jetstream.managesProfilePhotos"
                      class="hidden col-span-6 sm:col-span-4"
                    >
                      <!-- Profile Photo File Input -->
                      <input
                        ref="photo"
                        type="file"
                        class="hidden"
                        @change="updatePhotoPreview"
                      >

                      <jet-label
                        for="photo"
                        value="Photo"
                      />

                      <!-- Current Profile Photo -->
                      <div
                        v-show="! photoPreview"
                        class="mt-2"
                      >
                        <img
                          :src="userData.profile_photo_url"
                          :alt="userData.name"
                          class="object-cover w-20 h-20 rounded-full"
                        >
                      </div>

                      <!-- New Profile Photo Preview -->
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
                        {{ __("Select A New Photo") }}
                      </jet-secondary-button>

                      <jet-input-error
                        :message="form.errors.photo"
                        class="mt-2"
                      />
                    </div>

                    <!-- Username -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="username"
                        v-model="form.username"
                        :label="__('Username')"
                        :error="form.errors.username"
                        type="text"
                        name="username"
                      />
                    </div>

                    <!-- Email -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="email"
                        v-model="form.email"
                        :label="__('Email Address')"
                        :error="form.errors.email"
                        type="email"
                        name="email"
                      />
                    </div>

                    <!-- Name -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="name"
                        v-model="form.name"
                        :label="__('Full Name')"
                        :error="form.errors.name"
                        type="text"
                        name="name"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-select
                        id="profile_photo_source"
                        v-model="form.profile_photo_source"
                        name="profile_photo_source"
                        :error="form.errors.profile_photo_source"
                        :label="__('Use Avatar from')"
                        :select-list="{linked_player: __('Linked Player'), gravatar: __('Gravatar')}"
                        :placeholder="__('Uploaded Photo')"
                      />
                    </div>

                    <!-- DOB -->
                    <div class="relative col-span-6 sm:col-span-3">
                      <date-picker
                        id="dob"
                        v-model:value="form.dob"
                        :placeholder="__('Select your date of birth')"
                        class="w-full"
                        value-type="format"
                        input-class="block w-full p-3 text-sm border-gray-300 rounded-md h-14 pt-7 focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 dark:bg-cool-gray-900 dark:text-gray-300 dark:border-gray-900"
                      />

                      <label
                        for="dob"
                        class="absolute -top-2.5 left-0 px-3 py-5 text-xs text-gray-500 h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out "
                      >{{ __("Date of Birth") }}</label>
                      <jet-input-error
                        :message="form.errors.dob"
                        class="mt-1"
                      />
                    </div>

                    <div
                      v-if="form.dob"
                      class="col-span-6 sm:col-span-3"
                    >
                      <x-checkbox
                        id="show_yob"
                        v-model="form.show_yob"
                        :label="__('Show Your of Birth')"
                        :help="__('Show Year of Birth in your public profile.')"
                        name="show_yob"
                        :error="form.errors.show_yob"
                      />
                    </div>

                    <!-- Gender -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-select
                        id="gender"
                        v-model="form.gender"
                        name="gender"
                        :error="form.errors.gender"
                        :label="__('Gender')"
                        placeholder="Prefer not to say"
                        :select-list="{m: __('Male'), f: __('Female'), o: __('Others')}"
                      />
                    </div>

                    <div
                      v-if="form.gender"
                      class="col-span-6 sm:col-span-3"
                    >
                      <x-checkbox
                        id="show_gender"
                        v-model="form.show_gender"
                        :label="__('Show Gender')"
                        :help="__('Show Gender in your public profile.')"
                        name="show_gender"
                        :error="form.errors.show_gender"
                      />
                    </div>

                    <!-- s_discord_username -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_discord_username"
                        v-model="form.s_discord_username"
                        :label="__('Discord Username')"
                        :error="form.errors.s_discord_username"
                        autocomplete="s_discord_username"
                        type="text"
                        name="s_discord_username"
                        :help="__('Eg: username#1234')"
                      />
                    </div>

                    <!-- s_steam_profile_url -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_steam_profile_url"
                        v-model="form.s_steam_profile_url"
                        :label="__('Steam Profile URL')"
                        :error="form.errors.s_steam_profile_url"
                        autocomplete="s_steam_profile_url"
                        type="text"
                        name="s_steam_profile_url"
                        :help="__('Eg: https://steamcommunity.com/id/username')"
                      />
                    </div>

                    <!-- s_twitter_url -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_twitter_url"
                        v-model="form.s_twitter_url"
                        :label="__('Twitter Profile URL')"
                        :error="form.errors.s_twitter_url"
                        autocomplete="s_twitter_url"
                        type="text"
                        name="s_twitter_url"
                        :help="__('Eg: https://twitter.com/@username')"
                      />
                    </div>

                    <!-- s_youtube_url -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_youtube_url"
                        v-model="form.s_youtube_url"
                        :label="__('YouTube URL')"
                        :error="form.errors.s_youtube_url"
                        autocomplete="s_youtube_url"
                        type="text"
                        name="s_youtube_url"
                        :help="__('Eg: https://www.youtube.com/minecraft')"
                      />
                    </div>

                    <!-- s_facebook_url -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_facebook_url"
                        v-model="form.s_facebook_url"
                        :label="__('Facebook URL')"
                        :error="form.errors.s_facebook_url"
                        autocomplete="s_facebook_url"
                        type="text"
                        name="s_facebook_url"
                        :help="__('Eg: https://www.facebook.com/minecraft')"
                      />
                    </div>

                    <!-- s_twitch_url -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_twitch_url"
                        v-model="form.s_twitch_url"
                        :label="__('Twitch URL')"
                        :error="form.errors.s_twitch_url"
                        autocomplete="s_twitch_url"
                        type="text"
                        name="s_twitch_url"
                        :help="__('Eg: https://www.twitch.tv/minecraft')"
                      />
                    </div>

                    <!-- s_linkedin_url -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_linkedin_url"
                        v-model="form.s_linkedin_url"
                        :label="__('LinkedIn URL')"
                        :error="form.errors.s_linkedin_url"
                        autocomplete="s_linkedin_url"
                        type="text"
                        name="s_linkedin_url"
                        :help="__('Eg: https://www.linkedin.com/in/minecraft')"
                      />
                    </div>

                    <!-- s_tiktok_url -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_tiktok_url"
                        v-model="form.s_tiktok_url"
                        :label="__('TikTok URL')"
                        :error="form.errors.s_tiktok_url"
                        autocomplete="s_tiktok_url"
                        type="text"
                        name="s_tiktok_url"
                        :help="__('Eg: https://www.tiktok.com/@minecraft')"
                      />
                    </div>

                    <!-- s_website_url -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="s_website_url"
                        v-model="form.s_website_url"
                        :label="__('Website URL')"
                        :error="form.errors.s_website_url"
                        autocomplete="s_website_url"
                        type="text"
                        name="s_website_url"
                        :help="__('Eg: https://my-personal-blog.com')"
                      />
                    </div>

                    <!-- Role  -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-select
                        id="role"
                        v-model="form.role"
                        name="role"
                        :error="form.errors.role"
                        label="Role"
                        :placeholder="__('Select role')"
                        :select-list="rolesList"
                      />
                      <!--                                        <jet-label for="role" value="Role"/>-->
                      <!--                                        <multiselect v-model="form.role" deselect-label="Can't remove" track-by="id" label="display_name" placeholder="Select role" :options="rolesList" :searchable="false" :allow-empty="false"></multiselect>-->
                      <!--                                        <jet-input-error :message="form.errors.role" class="mt-2"/>-->
                    </div>

                    <!-- about -->
                    <div class="col-span-6 sm:col-span-3">
                      <x-textarea
                        id="about"
                        v-model="form.about"
                        :rows="10"
                        :label="__('About Yourself')"
                        :help="__('Something about yourself in 255 characters.')"
                        :error="form.errors.about"
                        name="about"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-checkbox
                        id="verified"
                        v-model="form.verified"
                        :label="__('Verified User')"
                        :help="__('Show a blue verified tick after username.')"
                        name="verified"
                        :error="form.errors.verified"
                      />
                    </div>


                    <div
                      v-if="can('assign badges')"
                      class="col-span-6 sm:col-span-3"
                    >
                      <label
                        for="badges"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >{{ __("User Badges") }}</label>
                      <multiselect
                        id="badges"
                        v-model="form.badges"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                        :options="badgesList"
                        :multiple="true"
                        :close-on-select="false"
                        :clear-on-select="false"
                        :preserve-search="true"
                        :placeholder="__('Search')+'...'"
                        label="name"
                        track-by="id"
                      />
                      <jet-input-error
                        :message="form.errors.permissions"
                        class="mt-2"
                      />
                    </div>

                    <div
                      class="col-span-6 sm:col-span-3"
                    >
                      <label
                        for="badges"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >{{ __("Country") }}</label>
                      <multiselect
                        id="country"
                        v-model="form.country"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                        :options="countryList"
                        :multiple="false"
                        :placeholder="__('Search')+'...'"
                        label="name"
                        track-by="id"
                      />
                      <jet-input-error
                        :message="form.errors.country_id"
                        class="mt-2"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <x-input
                        id="password"
                        v-model="form.password"
                        :label="__('Change User Password')"
                        :error="form.errors.password"
                        autocomplete="password"
                        type="text"
                        name="password"
                        :help="__('Leave it empty if you dont want to change password')"
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
                    {{ __("Update User") }}
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
import JetLabel from '@/Jetstream/Label.vue';
import DatePicker from 'vue-datepicker-next';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import Multiselect from 'vue-multiselect';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {useAuthorizable} from '@/Composables/useAuthorizable';

export default {

    components: {
        AdminLayout,
        XTextarea,
        XSelect,
        XCheckbox,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        XInput,
        JetLabel,
        DatePicker,
        Multiselect
    },
    props: {
        userData: Object,
        rolesList: Object,
        badgesList: Object,
        countryList: Object,
    },
    setup() {
        const {can} = useAuthorizable();
        return {can};
    },
    data() {
        return {
            form: useForm({
                _method: 'PUT',
                username: this.userData.username,
                name: this.userData.name,
                email: this.userData.email,
                photo: null,
                dob: this.userData.dob,
                gender: this.userData.gender,
                cover_image_url: this.userData.cover_image_url,
                s_discord_username: this.userData?.social_links?.s_discord_username ?? null,
                s_steam_profile_url: this.userData?.social_links?.s_steam_profile_url ?? null,
                s_twitter_url: this.userData?.social_links?.s_twitter_url ?? null,
                s_youtube_url: this.userData?.social_links?.s_youtube_url ?? null,
                s_facebook_url: this.userData?.social_links?.s_facebook_url ?? null,
                s_twitch_url: this.userData?.social_links?.s_twitch_url ?? null,
                s_website_url: this.userData?.social_links?.s_website_url ?? null,
                s_linkedin_url: this.userData?.social_links?.s_linkedin_url ?? null,
                s_tiktok_url: this.userData?.social_links?.s_tiktok_url ?? null,
                about: this.userData.about,
                profile_photo_source: this.userData.settings ? this.userData.settings.profile_photo_source : null,
                show_gender: this.userData.settings ? !!+this.userData.settings.show_gender : false,                     // coz in old version, data store as string 1,0
                show_yob: this.userData.settings ? !!+this.userData.settings.show_yob : false,                            // coz in old version, data store as string 1,0
                verified: !!this.userData.verified_at,
                role: this.userData.roles[0].name,
                badges: this.userData.badges,
                country: this.userData.country,
                country_id: this.userData.country_id,
                password: '',
            }),

            photoPreview: null,
        };
    },

    methods: {
        updateUserInformation() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0];
            }

            const tempBadges = this.form.badges;
            this.form.badges = this.form.badges.map(b => b.id);
            this.form.country_id = this.form.country?.id;
            this.form.post(route('admin.user.update', this.userData.id), {
                preserveScroll: true
            });
            this.form.badges = tempBadges;
        },

        selectNewPhoto() {
            this.$refs.photo.click();
        },

        updatePhotoPreview() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.photoPreview = e.target.result;
            };

            reader.readAsDataURL(this.$refs.photo.files[0]);
        },
    },
};
</script>


<style scoped>
.mx-datepicker {
    width: 100% !important;
}
</style>
