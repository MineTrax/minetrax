<template>
  <jet-form-section @submitted="updateProfileInformation">
    <template #title>
      {{ __("Profile Information") }}
    </template>

    <template #description>
      {{ __("Update your account's profile information and email address.") }}
    </template>

    <template #form>
      <!-- Profile Photo -->
      <div
        v-if="$page.props.jetstream.managesProfilePhotos"
        class="col-span-6 sm:col-span-3"
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
          value="Avatar"
        />

        <!-- Current Profile Photo -->
        <div
          v-show="! photoPreview"
          class="mt-2"
        >
          <img
            :src="user.profile_photo_url"
            :alt="user.name"
            class="rounded-full h-28 w-28 object-cover"
          >
        </div>

        <!-- New Profile Photo Preview -->
        <div
          v-show="photoPreview"
          class="mt-2"
        >
          <span
            class="block rounded-full w-28 h-28"
            :style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'"
          />
        </div>

        <jet-secondary-button
          class="mt-2 mr-2"
          type="button"
          @click.prevent="selectNewPhoto"
        >
          {{ __("New Photo") }}
        </jet-secondary-button>

        <jet-secondary-button
          v-if="user.profile_photo_path"
          type="button"
          class="mt-2"
          @click.prevent="deletePhoto"
        >
          {{ __("Remove Photo") }}
        </jet-secondary-button>

        <jet-input-error
          :message="form.errors.photo"
          class="mt-2"
        />
      </div>

      <!-- Cover Image -->
      <div class="col-span-6 sm:col-span-3">
        <input
          ref="coverImage"
          type="file"
          class="hidden"
          @change="updateCoverImagePreview"
        >

        <jet-label
          for="coverImage"
          :value="__('Profile Cover Image')"
        />

        <div
          v-show="! coverImagePreview"
          class="mt-2"
        >
          <img
            :src="user.cover_image_url"
            :alt="user.name"
            class="rounded h-28 object-cover w-full"
          >
        </div>

        <div
          v-show="coverImagePreview"
          class="mt-2"
        >
          <span
            class="block rounded h-28"
            :style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + coverImagePreview + '\');'"
          />
        </div>

        <jet-secondary-button
          class="mt-2 mr-2"
          type="button"
          @click.prevent="selectNewCoverImage"
        >
          {{ __("New Cover") }}
        </jet-secondary-button>

        <jet-secondary-button
          v-if="user.cover_image_path"
          type="button"
          class="mt-2"
          @click.prevent="deleteCoverImage"
        >
          {{ __("Default Cover") }}
        </jet-secondary-button>

        <jet-input-error
          :message="form.errors.cover_image"
          class="mt-2"
        />
      </div>

      <!-- Username -->
      <div class="col-span-6 sm:col-span-3">
        <x-input
          id="username"
          :model-value="user.username"
          :label="__('Username')"
          :error="form.errors.username"
          type="text"
          name="username"
          :disabled="true"
        />
      </div>

      <!-- Email -->
      <div class="col-span-6 sm:col-span-3">
        <x-input
          id="email"
          :model-value="user.email"
          :label="__('Email')"
          :error="form.errors.email"
          type="email"
          name="email"
          :disabled="true"
        />
      </div>

      <!-- Name -->
      <div class="col-span-6 sm:col-span-3">
        <x-input
          id="name"
          v-model="form.name"
          :label="__('Name')"
          :error="form.errors.name"
          :required="true"
          autocomplete="name"
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
          :select-list="{linked_player: 'Linked Player', gravatar: 'Gravatar'}"
          placeholder="Uploaded Photo"
        />
      </div>

      <!-- DOB -->
      <div class="col-span-6 sm:col-span-3 relative">
        <date-picker
          id="dob"
          v-model:value="form.dob"
          :placeholder="__('Select your date of birth')"
          class="w-full"
          value-type="format"
          input-class="border-gray-300 h-14 p-3 text-sm pt-7 focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 rounded-md block w-full dark:bg-cool-gray-900 dark:text-gray-300 dark:border-gray-900"
        />

        <label
          for="dob"
          class="absolute -top-2.5 left-0 px-3 py-5 text-xs text-gray-500 h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out dark:text-gray-400"
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
          :placeholder="__('Prefer not to say')"
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
          help="Eg: https://facebook.com/minecraft"
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

      <!-- about -->
      <div class="col-span-6 sm:col-span-3">
        <x-textarea
          id="about"
          v-model="form.about"
          :label="__('About Yourself')"
          :help="__('Something about yourself in 255 characters.')"
          :error="form.errors.about"
          name="about"
        />
      </div>
    </template>

    <template #actions>
      <jet-action-message
        :on="form.recentlySuccessful"
        class="mr-3"
      >
        {{ __("Saved.") }}
      </jet-action-message>

      <jet-button
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        :loading="form.processing"
      >
        {{ __("Save") }}
      </jet-button>
    </template>
  </jet-form-section>
</template>

<script>
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import DatePicker from 'vue-datepicker-next';
import XInput from '@/Components/Form/XInput.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        XTextarea,
        XSelect,
        XCheckbox,
        XInput,
        JetActionMessage,
        JetButton,
        JetFormSection,
        JetInputError,
        JetLabel,
        JetSecondaryButton,
        DatePicker
    },

    props: ['user'],

    data() {
        return {
            form: useForm({
                _method: 'PUT',
                name: this.user.name,
                photo: null,
                cover_image: null,
                dob: this.user.dob,
                gender: this.user.gender,
                cover_image_url: this.user.cover_image_url,
                s_discord_username: this.user?.social_links?.s_discord_username ??  null,
                s_steam_profile_url: this.user?.social_links?.s_steam_profile_url ?? null,
                s_twitter_url: this.user?.social_links?.s_twitter_url ?? null,
                s_youtube_url: this.user?.social_links?.s_youtube_url ?? null,
                s_facebook_url: this.user?.social_links?.s_facebook_url ?? null,
                s_twitch_url: this.user?.social_links?.s_twitch_url ?? null,
                s_linkedin_url: this.user?.social_links?.s_linkedin_url ?? null,
                s_tiktok_url: this.user?.social_links?.s_tiktok_url ?? null,
                s_website_url: this.user?.social_links?.s_website_url ?? null,
                about: this.user.about,

                profile_photo_source: this.user.settings ? this.user.settings.profile_photo_source : null,
                show_gender: this.user.settings ? !!+this.user.settings.show_gender : false,            // coz in old version, data store as string 1,0
                show_yob: this.user.settings ? !!+this.user.settings.show_yob : false,                  // coz in old version, data store as string 1,0
            }),

            photoPreview: null,
            coverImagePreview: null,
        };
    },

    methods: {
        updateProfileInformation() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0];
            }

            if (this.$refs.coverImage) {
                this.form.cover_image = this.$refs.coverImage.files[0];
            }

            this.form.post(route('user-profile-information.update'), {
                errorBag: 'updateProfileInformation',
                preserveScroll: true
            });
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

        deletePhoto() {
            this.$inertia.delete(route('current-user-photo.destroy'), {
                preserveScroll: true,
                onSuccess: () => (this.photoPreview = null),
            });
        },

        selectNewCoverImage() {
            this.$refs.coverImage.click();
        },

        updateCoverImagePreview() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.coverImagePreview = e.target.result;
            };

            reader.readAsDataURL(this.$refs.coverImage.files[0]);
        },

        deleteCoverImage() {
            this.$inertia.delete(route('current-user-cover.destroy'), {
                preserveScroll: true,
                onSuccess: () => (this.coverImagePreview = null),
            });
        },

    },
};
</script>

<style scoped>
.mx-datepicker {
    width: 100% !important;
}
</style>
