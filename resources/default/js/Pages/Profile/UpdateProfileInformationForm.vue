<template>
    <form @submit.prevent="updateProfileInformation">
        <div class="grid grid-cols-6 gap-6">
            <!-- Profile Photo -->
            <div v-if="page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-3">
                <ImageUpload
                    id="photo"
                    name="photo"
                    :label="__('Avatar')"
                    :current-url="user.profile_photo_url"
                    v-model="form.photo"
                    :error="form.errors.photo"
                    :removable="!!user.profile_photo_path"
                    shape="circle"
                    :preview-class="'h-28 w-28'"
                    @remove="deletePhoto"
                />
            </div>

            <!-- Cover Image -->
            <div class="col-span-6 sm:col-span-3">
                <ImageUpload
                    id="coverImage"
                    name="cover_image"
                    :label="__('Profile Cover Image')"
                    :current-url="user.cover_image_url"
                    v-model="form.cover_image"
                    :error="form.errors.cover_image"
                    :removable="!!user.cover_image_path"
                    shape="rect"
                    :preview-class="'h-28 w-full'"
                    @remove="deleteCoverImage"
                />
            </div>

            <!-- Username -->
            <div class="col-span-6 sm:col-span-3">
                <x-input id="username" :model-value="user.username" :label="__('Username')" :error="form.errors.username" type="text" name="username" :disabled="true" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-3">
                <x-input id="email" :model-value="user.email" :label="__('Email')" :error="form.errors.email" type="email" name="email" :disabled="true" />
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-3">
                <x-input id="name" v-model="form.name" :label="__('Name')" :error="form.errors.name" :required="true" autocomplete="name" type="text" name="name" />
            </div>

            <div class="col-span-6 sm:col-span-3">
                <x-select
                    id="profile_photo_source"
                    v-model="form.profile_photo_source"
                    name="profile_photo_source"
                    :error="form.errors.profile_photo_source"
                    :label="__('Use Avatar from')"
                    :select-list="{ linked_player: 'Linked Player', gravatar: 'Gravatar' }"
                    :placeholder="__('Uploaded Photo')"
                />
            </div>

            <!-- DOB -->
            <div class="col-span-6 sm:col-span-3">
                <x-date-picker id="dob" v-model="form.dob" :label="__('Date of Birth')" :placeholder="__('Select your date of birth')" :error="form.errors.dob" name="dob" />
            </div>

            <div v-if="form.dob" class="col-span-6 sm:col-span-3">
                <x-checkbox id="show_yob" v-model="form.show_yob" :label="__('Show Year of Birth')" :help="__('Show Year of Birth in your public profile.')" name="show_yob" :error="form.errors.show_yob" />
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
                    :select-list="{ m: __('Male'), f: __('Female'), o: __('Others') }"
                />
            </div>

            <div v-if="form.gender" class="col-span-6 sm:col-span-3">
                <x-checkbox id="show_gender" v-model="form.show_gender" :label="__('Show Gender')" :help="__('Show Gender in your public profile.')" name="show_gender" :error="form.errors.show_gender" />
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
                <x-textarea id="about" v-model="form.about" :label="__('About Yourself')" :help="__('Something about yourself in 255 characters.')" :error="form.errors.about" name="about" />
            </div>

            <div v-if="page.props.localeSwitcherEnabled" class="col-span-6 sm:col-span-3">
                <x-select id="locale" v-model="form.locale" name="locale" :error="form.errors.locale" :label="__('Language')" :placeholder="__('Select Language...')" :select-list="availableLocales" />
            </div>
        </div>

        <div class="flex items-center justify-end pt-6 gap-4">
            <LoadingButton :loading="form.processing">
                {{ __("Save") }}
            </LoadingButton>
        </div>
    </form>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import LoadingButton from "@/Components/LoadingButton.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import XInput from "@/Components/Form/XInput.vue";
import XCheckbox from "@/Components/Form/XCheckbox.vue";
import XSelect from "@/Components/Form/XSelect.vue";
import XTextarea from "@/Components/Form/XTextarea.vue";
import XDatePicker from "@/Components/Form/XDatePicker.vue";
import ImageUpload from "@/Components/Form/ImageUpload.vue";
import { useTranslations } from "@/Composables/useTranslations";

const { __ } = useTranslations();

// Define props
const props = defineProps(["user"]);

// Get page data
const page = usePage();

// Expose user for template
const user = props.user;

// Reactive data
const availableLocales = ref({});

// Form setup
const form = useForm({
    _method: "PUT",
    name: props.user.name,
    photo: null,
    cover_image: null,
    dob: props.user.dob,
    gender: props.user.gender,
    cover_image_url: props.user.cover_image_url,
    s_discord_username: props.user?.social_links?.s_discord_username ?? null,
    s_steam_profile_url: props.user?.social_links?.s_steam_profile_url ?? null,
    s_twitter_url: props.user?.social_links?.s_twitter_url ?? null,
    s_youtube_url: props.user?.social_links?.s_youtube_url ?? null,
    s_facebook_url: props.user?.social_links?.s_facebook_url ?? null,
    s_twitch_url: props.user?.social_links?.s_twitch_url ?? null,
    s_linkedin_url: props.user?.social_links?.s_linkedin_url ?? null,
    s_tiktok_url: props.user?.social_links?.s_tiktok_url ?? null,
    s_website_url: props.user?.social_links?.s_website_url ?? null,
    about: props.user.about,
    profile_photo_source: props.user.settings && props.user.settings.profile_photo_source ? props.user.settings.profile_photo_source : null,
    show_gender: props.user.settings ? !!+props.user.settings.show_gender : false,
    show_yob: props.user.settings ? !!+props.user.settings.show_yob : false,
    locale: props.user.locale,
});

// Methods
const updateProfileInformation = () => {
    const localeChanged = form.locale !== props.user.locale;

    form.post(route("user-profile-information.update"), {
        errorBag: "updateProfileInformation",
        preserveScroll: true,
        preserveState: "errors",
        onSuccess: () => {
            Toast.fire({
                icon: "success",
                title: __("Profile Updated!"),
                timer: 3000,
            });

            if (localeChanged) {
                location.reload();
            }
        },
    });
};

const deletePhoto = () => {
    router.delete(route("current-user-photo.destroy"), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {},
    });
};

const deleteCoverImage = () => {
    router.delete(route("current-user-cover.destroy"), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {},
    });
};

const getAvailableLocales = () => {
    axios
        .get(route("locale.list"))
        .then((response) => {
            const locales = response.data;
            locales.forEach((locale) => {
                availableLocales.value[locale.code] = locale.display;
            });
        })
        .catch((error) => {
            console.log(error);
        });
};

// Lifecycle hooks
onMounted(() => {
    if (page.props.localeSwitcherEnabled) {
        getAvailableLocales();
    }
});
</script>
