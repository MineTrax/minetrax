<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import XDatePicker from '@/Components/Form/XDatePicker.vue';
import Multiselect from 'vue-multiselect';
import { ref, onMounted } from 'vue';

const { __ } = useTranslations();
const { can } = useAuthorizable();
const page = usePage();

const props = defineProps({
    userData: Object,
    rolesList: Object,
    badgesList: Object,
    countryList: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Users'),
        url: route('admin.user.index'),
        current: false,
    },
    {
        text: __('Edit User'),
        current: true,
    },
    {
        text: '@' + props.userData.username,
        current: true,
    }
];

const availableLocales = ref({});

const form = useForm({
    _method: 'PUT',
    username: props.userData.username,
    name: props.userData.name,
    email: props.userData.email,
    photo: null,
    dob: props.userData.dob,
    gender: props.userData.gender,
    cover_image_url: props.userData.cover_image_url,
    s_discord_username: props.userData?.social_links?.s_discord_username ?? null,
    s_steam_profile_url: props.userData?.social_links?.s_steam_profile_url ?? null,
    s_twitter_url: props.userData?.social_links?.s_twitter_url ?? null,
    s_youtube_url: props.userData?.social_links?.s_youtube_url ?? null,
    s_facebook_url: props.userData?.social_links?.s_facebook_url ?? null,
    s_twitch_url: props.userData?.social_links?.s_twitch_url ?? null,
    s_website_url: props.userData?.social_links?.s_website_url ?? null,
    s_linkedin_url: props.userData?.social_links?.s_linkedin_url ?? null,
    s_tiktok_url: props.userData?.social_links?.s_tiktok_url ?? null,
    about: props.userData.about,
    profile_photo_source: props.userData.settings ? props.userData.settings.profile_photo_source : null,
    show_gender: props.userData.settings ? !!+props.userData.settings.show_gender : false,
    show_yob: props.userData.settings ? !!+props.userData.settings.show_yob : false,
    verified: !!props.userData.verified_at,
    role: props.userData.roles[0].name,
    badges: props.userData.badges,
    country: props.userData.country,
    country_id: props.userData.country_id,
    password: '',
    locale: props.userData.locale,
});

function updateUserInformation() {
    const tempBadges = form.badges;
    form.badges = form.badges.map(b => b.id);
    form.country_id = form.country?.id;
    form.post(route('admin.user.update', props.userData.id), {
        preserveScroll: true
    });
    form.badges = tempBadges;
}

function getAvailableLocales() {
    axios.get(route('locale.list')).then(response => {
        const locales = response.data;
        locales.forEach(locale => {
            availableLocales.value[locale.code] = locale.display;
        });
    }).catch(error => {
        console.log(error);
    });
}

onMounted(() => {
    if (page.props.localeSwitcherEnabled) {
        getAvailableLocales();
    }
});
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit User @:username', {username: userData.username})" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateUserInformation">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <!-- Username -->
                <div class="col-span-6 sm:col-span-3">
                  <XInput
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
                  <XInput
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
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Full Name')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
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
                <div class="col-span-6 sm:col-span-3">
                  <XDatePicker
                    id="dob"
                    v-model="form.dob"
                    :label="__('Date of Birth')"
                    :placeholder="__('Select your date of birth')"
                    :error="form.errors.dob"
                    name="dob"
                  />
                </div>

                <div
                  v-if="form.dob"
                  class="col-span-6 sm:col-span-3 flex items-center"
                >
                  <XSwitch
                    id="show_yob"
                    v-model="form.show_yob"
                    :label="__('Show Year of Birth')"
                    :help="__('Show Year of Birth in your public profile.')"
                    name="show_yob"
                    :error="form.errors.show_yob"
                  />
                </div>

                <!-- Gender -->
                <div class="col-span-6 sm:col-span-3">
                  <XSelect
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
                  class="col-span-6 sm:col-span-3 flex items-center"
                >
                  <XSwitch
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
                  <XInput
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
                  <XInput
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
                  <XInput
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
                  <XInput
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
                  <XInput
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
                  <XInput
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
                  <XInput
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
                  <XInput
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
                  <XInput
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
                  <XSelect
                    id="role"
                    v-model="form.role"
                    name="role"
                    :error="form.errors.role"
                    :label="__('Role')"
                    :placeholder="__('Select role')"
                    :select-list="rolesList"
                    :disabled="!can('assign roles')"
                  />
                </div>

                <!-- about -->
                <div class="col-span-6 sm:col-span-3">
                  <XTextarea
                    id="about"
                    v-model="form.about"
                    :rows="4"
                    :label="__('About Yourself')"
                    :help="__('Something about yourself in 255 characters.')"
                    :error="form.errors.about"
                    name="about"
                  />
                </div>

                <div
                  v-if="can('assign badges')"
                  class="col-span-6 sm:col-span-3"
                >
                  <label
                    for="badges"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("User Badges") }}</label>
                  <Multiselect
                    id="badges"
                    v-model="form.badges"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="badgesList"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    :placeholder="__('Search')+'...'"
                    label="name"
                    track-by="id"
                  />
                  <p
                    v-if="form.errors.badges"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.badges }}
                  </p>
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <label
                    for="country"
                    class="block text-sm font-medium text-foreground mb-2"
                  >{{ __("Country") }}</label>
                  <Multiselect
                    id="country"
                    v-model="form.country"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="countryList"
                    :multiple="false"
                    :placeholder="__('Search')+'...'"
                    label="name"
                    track-by="id"
                  />
                  <p
                    v-if="form.errors.country_id"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.country_id }}
                  </p>
                </div>

                <div
                  v-if="$page.props.localeSwitcherEnabled"
                  class="col-span-6 sm:col-span-3"
                >
                  <XSelect
                    id="locale"
                    v-model="form.locale"
                    name="locale"
                    :error="form.errors.locale"
                    :label="__('Language')"
                    :placeholder="__('Select Language...')"
                    :select-list="availableLocales"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3 flex items-center">
                  <XSwitch
                    id="verified"
                    v-model="form.verified"
                    :label="__('Verified User')"
                    :help="__('Show a blue verified tick after username.')"
                    name="verified"
                    :error="form.errors.verified"
                  />
                </div>

                <div class="col-span-6 sm:col-span-6">
                  <XInput
                    id="password"
                    v-model="form.password"
                    :label="__('Change User Password')"
                    :error="form.errors.password"
                    autocomplete="new-password"
                    type="text"
                    name="password"
                    :help="__('Leave it empty if you dont want to change password')"
                  />
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.user.index')">
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
                {{ __("Update User") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
