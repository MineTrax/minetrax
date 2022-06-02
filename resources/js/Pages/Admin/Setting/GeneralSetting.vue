<template>
  <app-layout>
    <app-head title="General Settings" />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="w-64 flex-shrink-0 pr-10">
        <div class="flex flex-col">
          <div class="uppercase mb-2 text-xs tracking-wide text-gray-600 dark:text-gray-400 font-bold">
            SETTINGS
          </div>

          <setting-link
            :href="route('admin.setting.general.show')"
            :active="route().current('admin.setting.general.show')"
          >
            General
          </setting-link>
          <setting-link
            :href="route('admin.setting.theme.show')"
            :active="route().current('admin.setting.theme.show')"
          >
            Theme
          </setting-link>
          <setting-link
            :href="route('admin.setting.plugin.show')"
            :active="route().current('admin.setting.plugin.show')"
          >
            Plugin
          </setting-link>
          <setting-link
            :href="route('admin.setting.player.show')"
            :active="route().current('admin.setting.player.show')"
          >
            Player
          </setting-link>
        </div>
      </div>

      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full">
            <div class="px-6 py-4 border-b font-bold dark:border-gray-700 dark:text-gray-200">
              General Settings
            </div>

            <div class="mt-10 sm:mt-0">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="mt-5 md:mt-0 md:col-span-3">
                  <form
                    autocomplete="off"
                    @submit.prevent="saveSetting"
                  >
                    <div class="shadow overflow-hidden sm:rounded-md">
                      <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                          <div class="col-span-6 sm:col-span-3">
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
                            >Site
                              Header Logo Image Light (200x40)</label>


                            <!-- New Profile Photo Preview -->
                            <img
                              v-if="settings.site_header_logo_path_light && !photoPreview"
                              :src="settings.site_header_logo_path_light"
                              alt=""
                              class="h-14"
                            >
                            <div
                              v-show="photoPreview"
                              class="mt-2"
                            >
                              <span
                                class="block h-14"
                                :style="'background-size: contain; background-repeat: no-repeat; background-image: url(\'' + photoPreview + '\');'"
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
                              :message="form.errors.photo_light"
                              class="mt-2"
                            />
                          </div>


                          <div class="col-span-6 sm:col-span-3">
                            <!-- Profile Photo File Input -->
                            <input
                              id="photo_dark"
                              ref="photo_dark"
                              type="file"
                              class="hidden"
                              @change="updatePhotoPreviewDark"
                            >

                            <label
                              for="photo_dark"
                              class="block text-sm font-medium text-gray-700 dark:text-gray-400"
                            >Site
                              Header Logo Image Dark (200x40)</label>


                            <!-- New Profile Photo Preview -->
                            <img
                              v-if="settings.site_header_logo_path_dark && !photoPreviewDark"
                              :src="settings.site_header_logo_path_dark"
                              alt=""
                              class="h-14"
                            >
                            <div
                              v-show="photoPreviewDark"
                              class="mt-2"
                            >
                              <span
                                class="block h-14"
                                :style="'background-size: contain; background-repeat: no-repeat; background-image: url(\'' + photoPreviewDark + '\');'"
                              />
                            </div>

                            <jet-secondary-button
                              class="mt-2 mr-2"
                              type="button"
                              @click.native.prevent="selectNewPhotoDark"
                            >
                              Select A New Image
                            </jet-secondary-button>

                            <jet-input-error
                              :message="form.errors.photo_dark"
                              class="mt-2"
                            />
                          </div>

                          <div class="col-span-6 sm:col-span-6">
                            <x-input
                              id="site_name"
                              v-model="form.site_name"
                              label="Site Name"
                              :error="form.errors.site_name"
                              type="text"
                              name="site_nme"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_mcserver_onlineplayersbox"
                              v-model="form.enable_mcserver_onlineplayersbox"
                              label="Online Players List Box"
                              help="Show live server player list in homepage. Query must be enabled for this to work."
                              name="enable_mcserver_onlineplayersbox"
                              :error="form.errors.enable_mcserver_onlineplayersbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_mcserver_statuspingbox"
                              v-model="form.enable_mcserver_statuspingbox"
                              label="Online Players Count Box"
                              help="Show live player count box. Ping must be enabled for this to work."
                              name="enable_mcserver_statuspingbox"
                              :error="form.errors.enable_mcserver_statuspingbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_ingamechat"
                              v-model="form.enable_ingamechat"
                              label="In-Game Chat"
                              help="Show ingame chatbox in homepage."
                              name="enable_ingamechat"
                              :error="form.errors.enable_ingamechat"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_shoutbox"
                              v-model="form.enable_shoutbox"
                              label="Shout Box"
                              help="Enable shoutbox"
                              name="enable_shoutbox"
                              :error="form.errors.enable_shoutbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_onlineuserbox"
                              v-model="form.enable_onlineuserbox"
                              label="Online Users Box"
                              help="Enable online users list box."
                              name="enable_onlineuserbox"
                              :error="form.errors.enable_onlineuserbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_newuserbox"
                              v-model="form.enable_newuserbox"
                              label="Newest User Box"
                              help="Enable newest user box."
                              name="enable_newuserbox"
                              :error="form.errors.enable_newuserbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_didyouknowbox"
                              v-model="form.enable_didyouknowbox"
                              label="DidYouKnow Box"
                              help="Enable DidYouKnow Box"
                              name="enable_didyouknowbox"
                              :error="form.errors.enable_didyouknowbox"
                            />
                          </div>


                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_socialbox"
                              v-model="form.enable_socialbox"
                              label="Socials Box"
                              help="Enable social box in homepage."
                              name="enable_socialbox"
                              :error="form.errors.enable_socialbox"
                            />
                          </div>
                          <div
                            v-if="form.enable_socialbox"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="youtube_url"
                              v-model="form.youtube_url"
                              label="Youtube URL"
                              :error="form.errors.youtube_url"
                              autocomplete="youtube_url"
                              type="text"
                              name="youtube_url"
                              help="Eg: https://www.youtube.com/channel/UCsMdRMBnxIVO0K_YS0KHiMA"
                              help-error-flex="flex-col"
                            />
                          </div>
                          <div
                            v-if="form.enable_socialbox"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="facebook_url"
                              v-model="form.facebook_url"
                              label="Facebook URL"
                              :error="form.errors.facebook_url"
                              autocomplete="facebook_url"
                              type="text"
                              name="facebook_url"
                              help="Eg: https://www.facebook.com/minecraft"
                              help-error-flex="flex-col"
                            />
                          </div>
                          <div
                            v-if="form.enable_socialbox"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="twitter_url"
                              v-model="form.twitter_url"
                              label="Twitter URL"
                              :error="form.errors.twitter_url"
                              autocomplete="twitter_url"
                              type="text"
                              name="twitter_url"
                              help="Eg: https://www.twitter.com/minecraft"
                              help-error-flex="flex-col"
                            />
                          </div>
                          <div
                            v-if="form.enable_socialbox"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="twitch_url"
                              v-model="form.twitch_url"
                              label="Twitch URL"
                              :error="form.errors.twitch_url"
                              autocomplete="twitch_url"
                              type="text"
                              name="twitch_url"
                              help="Eg: https://www.twitch.tv/minecraft"
                              help-error-flex="flex-col"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_discordbox"
                              v-model="form.enable_discordbox"
                              label="Discord Box"
                              help="Enable Discord Server Box"
                              name="enable_discordbox"
                              :error="form.errors.enable_discordbox"
                            />
                          </div>
                          <div
                            v-if="form.enable_discordbox"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-input
                              id="discord_server_id"
                              v-model="form.discord_server_id"
                              label="Discord Server ID"
                              :error="form.errors.discord_server_id"
                              autocomplete="discord_server_id"
                              type="text"
                              name="discord_server_id"
                              help="Eg: 453365679416646355"
                              help-error-flex="flex-col"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_donation_box"
                              v-model="form.enable_donation_box"
                              label="Donation Box"
                              help="Enable Donation Box."
                              name="enable_donation_box"
                              :error="form.errors.enable_donation_box"
                            />
                          </div>
                          <div
                            v-if="form.enable_donation_box"
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-input
                              id="donation_box_url"
                              v-model="form.donation_box_url"
                              label="Donation URL"
                              :error="form.errors.donation_box_url"
                              autocomplete="donation_box_url"
                              type="text"
                              name="donation_box_url"
                              help="Eg: https://paypal.me/@username"
                              help-error-flex="flex-col"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-6">
                            <x-checkbox
                              id="enable_voteforserverbox"
                              v-model="form.enable_voteforserverbox"
                              label="Vote for Server Box"
                              help="Enable Vote for server box."
                              name="enable_voteforserverbox"
                              :error="form.errors.enable_voteforserverbox"
                            />
                          </div>

                          <div
                            v-if="form.enable_voteforserverbox"
                            class="col-span-6 sm:col-span-6 flex-col space-y-1"
                          >
                            <div class="flex space-x-4">
                              <div class="w-5" />
                              <label
                                class="block flex-1 text-sm font-medium text-gray-700 dark:text-gray-400"
                              >Voting
                                Site Link</label>
                              <label
                                class="block flex-1 text-sm font-medium text-gray-700 dark:text-gray-400"
                              >Display
                                Name</label>
                            </div>

                            <div
                              v-for="(votingsite, index) in form.voteforserverbox_content"
                              :key="index"
                              class="flex space-x-4"
                            >
                              <button
                                type="button"
                                class="focus:outline-none group"
                                @click="deleteVotingSite(index)"
                              >
                                <icon
                                  class="w-5 h-5 text-gray-300 group-hover:text-red-500"
                                  name="trash"
                                />
                              </button>
                              <div class="flex-1">
                                <x-input
                                  v-model="votingsite.url"
                                  :label="`Voting Site URL ${index+1}`"
                                  :error="form.errors[`voteforserverbox_content.${index}.url`]"
                                  type="text"
                                  help-error-flex="flex-col"
                                />
                              </div>
                              <div class="flex-1">
                                <x-input
                                  v-model="votingsite.name"
                                  :label="`Voting Site Name ${index+1}`"
                                  :error="form.errors[`voteforserverbox_content.${index}.name`]"
                                  type="text"
                                  help-error-flex="flex-col"
                                />
                              </div>
                            </div>

                            <div class="flex justify-end mt-1">
                              <button
                                type="button"
                                class="p-1.5 text-xs text-light-blue-500 rounded border border-light-blue-500 focus:outline-none"
                                @click="addMoreVotingSite"
                              >
                                Add More
                              </button>
                            </div>
                          </div>

                          <div class="flex items-center col-span-3 sm:col-span-3">
                            <x-checkbox
                              id="enable_status_feed"
                              v-model="form.enable_status_feed"
                              label="Enable Status Feed"
                              help="Let player post status on homepage?"
                              name="enable_status_feed"
                              :error="form.errors.enable_status_feed"
                            />
                          </div>

                          <div class="flex items-center col-span-3 sm:col-span-3">
                            <x-checkbox
                              id="enable_welcomebox"
                              v-model="form.enable_welcomebox"
                              label="Welcome Box"
                              help="Enable welcome box in homepage."
                              name="enable_welcomebox"
                              :error="form.errors.enable_welcomebox"
                            />
                          </div>

                          <div
                            v-show="form.enable_welcomebox"
                            class="col-span-6 sm:col-span-6"
                          >
                            <textarea
                              id="welcomebox_content"
                              v-model="form.welcomebox_content"
                              aria-label="welcomebox"
                              name="welcomebox_content"
                              class="mt-1 focus:ring-light-blue-500 focus:border-light-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            />
                            <jet-input-error
                              :message="form.errors.welcomebox_content"
                              class="mt-2"
                            />
                          </div>
                        </div>
                      </div>
                      <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                        <loading-button
                          :loading="form.processing"
                          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600"
                          type="submit"
                        >
                          Save General Settings
                        </loading-button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import JetInputError from '@/Jetstream/InputError';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import LoadingButton from '@/Components/LoadingButton';
import XInput from '@/Components/Form/XInput';
import Icon from '@/Components/Icon';
import SettingLink from '@/Jetstream/SettingLink';
import * as EasyMDE from 'easymde';
import XCheckbox from '@/Components/Form/XCheckbox';
import XTextarea from '@/Components/Form/XTextarea';

export default {
    components: {
        XTextarea,
        XCheckbox,
        SettingLink,
        AppLayout,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        Icon,
        XInput
    },
    props: {
        settings: {
            type: Object,
            default: null
        }
    },

    data() {
        return {
            easyMDE: null,
            form: this.$inertia.form({
                site_name: this.settings.site_name,
                enable_mcserver_onlineplayersbox: this.settings.enable_mcserver_onlineplayersbox,
                enable_mcserver_statuspingbox: this.settings.enable_mcserver_statuspingbox,
                enable_ingamechat: this.settings.enable_ingamechat,
                enable_shoutbox: this.settings.enable_shoutbox,
                enable_welcomebox: this.settings.enable_welcomebox,
                welcomebox_content: this.settings.welcomebox_content,
                enable_voteforserverbox: this.settings.enable_voteforserverbox,
                voteforserverbox_content: this.settings.voteforserverbox_content || [{url: '', name: ''}],
                enable_onlineuserbox: this.settings.enable_onlineuserbox,
                enable_newuserbox: this.settings.enable_newuserbox,
                enable_didyouknowbox: this.settings.enable_didyouknowbox,
                enable_socialbox: this.settings.enable_socialbox,
                youtube_url: this.settings.youtube_url,
                facebook_url: this.settings.facebook_url,
                twitter_url: this.settings.twitter_url,
                twitch_url: this.settings.twitch_url,
                enable_discordbox: this.settings.enable_discordbox,
                discord_server_id: this.settings.discord_server_id,
                enable_donation_box: this.settings.enable_donation_box,
                donation_box_url: this.settings.donation_box_url,
                enable_status_feed: this.settings.enable_status_feed,
                photo_light: null,
                photo_dark: null,
            }),
            photoPreview: null,
            photoPreviewDark: null,
        };
    },

    mounted() {
        this.easyMDE = new EasyMDE({
            previewClass: 'editor-preview prose max-w-none',
            element: document.querySelector('#welcomebox_content')
        });
    },

    methods: {
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

        updatePhotoPreviewDark() {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.photoPreviewDark = e.target.result;
            };

            reader.readAsDataURL(this.$refs.photo_dark.files[0]);
        },
        selectNewPhotoDark() {
            this.$refs.photo_dark.click();
        },

        addMoreVotingSite() {
            this.form.voteforserverbox_content.push({
                url: '',
                name: '',
            });
        },
        deleteVotingSite(index) {
            this.form.voteforserverbox_content.splice(index, 1);
        },

        saveSetting() {
            if (this.$refs.photo) {
                this.form.photo_light = this.$refs.photo.files[0];
            }

            if (this.$refs.photo_dark) {
                this.form.photo_dark = this.$refs.photo_dark.files[0];
            }

            this.form.welcomebox_content = this.easyMDE.value();

            this.form.post(route('admin.setting.general.update'), {
                preserveScroll: true,
            });
        }
    }
};
</script>
