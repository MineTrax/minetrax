<template>
  <AdminLayout>
    <app-head
      :title="__('General Settings')"
    />

    <div class="flex max-w-6xl px-10 py-12 mx-auto">
      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="w-full bg-white rounded shadow dark:bg-cool-gray-800">
            <div class="px-6 py-4 font-bold border-b dark:border-gray-700 dark:text-gray-200">
              {{ __("General Settings") }}
            </div>

            <div class="mt-10 sm:mt-0">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="mt-5 md:mt-0 md:col-span-3">
                  <form
                    autocomplete="off"
                    @submit.prevent="saveSetting"
                  >
                    <div class="overflow-hidden shadow sm:rounded-md">
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
                            >{{ __("Site Header Logo Image Light (200x40)") }}</label>


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
                              @click.prevent="selectNewPhoto"
                            >
                              {{ __("Select A New Image") }}
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
                            >{{ __("Site Header Logo Image Dark (200x40)") }}</label>


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
                              @click.prevent="selectNewPhotoDark"
                            >
                              {{ __("Select A New Image") }}
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
                              :label="__('Site Name')"
                              :error="form.errors.site_name"
                              type="text"
                              name="site_nme"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_mcserver_onlineplayersbox"
                              v-model="form.enable_mcserver_onlineplayersbox"
                              :label="__('Online Players List Box')"
                              :help="__('Show live server player list in homepage. Query must be enabled for this to work.')"
                              name="enable_mcserver_onlineplayersbox"
                              :error="form.errors.enable_mcserver_onlineplayersbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_mcserver_statuspingbox"
                              v-model="form.enable_mcserver_statuspingbox"
                              :label="__('Online Players Count Box')"
                              :help="__('Show live player count box. Ping must be enabled for this to work.')"
                              name="enable_mcserver_statuspingbox"
                              :error="form.errors.enable_mcserver_statuspingbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_ingamechat"
                              v-model="form.enable_ingamechat"
                              :label="__('In-Game Chat')"
                              :help="__('Show ingame chatbox in homepage.')"
                              name="enable_ingamechat"
                              :error="form.errors.enable_ingamechat"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_shoutbox"
                              v-model="form.enable_shoutbox"
                              :label="__('Shout Box')"
                              :help="__('Enable shoutbox')"
                              name="enable_shoutbox"
                              :error="form.errors.enable_shoutbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_onlineuserbox"
                              v-model="form.enable_onlineuserbox"
                              :label="__('Online Users Box')"
                              :help="__('Enable online users list box.')"
                              name="enable_onlineuserbox"
                              :error="form.errors.enable_onlineuserbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_newuserbox"
                              v-model="form.enable_newuserbox"
                              :label="__('Newest User Box')"
                              :help="__('Enable newest user box.')"
                              name="enable_newuserbox"
                              :error="form.errors.enable_newuserbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_didyouknowbox"
                              v-model="form.enable_didyouknowbox"
                              :label="__('DidYouKnow Box')"
                              :help="__('Enable DidYouKnow Box')"
                              name="enable_didyouknowbox"
                              :error="form.errors.enable_didyouknowbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_topplayersbox"
                              v-model="form.enable_topplayersbox"
                              :label="__('Top Players Box')"
                              :help="__('Enable Top Players Box in Homepage')"
                              name="enable_topplayersbox"
                              :error="form.errors.enable_topplayersbox"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_socialbox"
                              v-model="form.enable_socialbox"
                              :label="__('Socials Box')"
                              :help="__('Enable social box in homepage.')"
                              name="enable_socialbox"
                              :error="form.errors.enable_socialbox"
                            />
                          </div>
                          <div
                            class="col-span-6 sm:col-span-3"
                          >
                            <x-input
                              id="discord_invite_url"
                              v-model="form.discord_invite_url"
                              :label="__('Discord Invite URL')"
                              :error="form.errors.discord_invite_url"
                              autocomplete="discord_invite_url"
                              type="text"
                              name="discord_invite_url"
                              :help="__('Eg: https://discord.gg/Hzfj27k')"
                              help-error-flex="flex-col"
                            />
                          </div>
                          <div
                            v-if="form.enable_socialbox"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="youtube_url"
                              v-model="form.youtube_url"
                              :label="__('Youtube URL')"
                              :error="form.errors.youtube_url"
                              autocomplete="youtube_url"
                              type="text"
                              name="youtube_url"
                              :help="__('Eg: https://www.youtube.com/channel/UCsMdRMBnxIVO0K_YS0KHiMA')"
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
                              :label="__('Facebook URL')"
                              :error="form.errors.facebook_url"
                              autocomplete="facebook_url"
                              type="text"
                              name="facebook_url"
                              :help="__('Eg: https://www.facebook.com/minecraft')"
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
                              :label="__('Twitter URL')"
                              :error="form.errors.twitter_url"
                              autocomplete="twitter_url"
                              type="text"
                              name="twitter_url"
                              :help="__('Eg: https://www.twitter.com/minetraxsuite')"
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
                              :label="__('Twitch URL')"
                              :error="form.errors.twitch_url"
                              autocomplete="twitch_url"
                              type="text"
                              name="twitch_url"
                              :help="__('Eg: https://www.twitch.tv/minecraft')"
                              help-error-flex="flex-col"
                            />
                          </div>
                          <div
                            v-if="form.enable_socialbox"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="tiktok_url"
                              v-model="form.tiktok_url"
                              :label="__('TikTok URL')"
                              :error="form.errors.tiktok_url"
                              autocomplete="tiktok_url"
                              type="text"
                              name="tiktok_url"
                              :help="__('Eg: https://www.tiktok.com/@minecraft')"
                              help-error-flex="flex-col"
                            />
                          </div>
                          <div
                            v-if="form.enable_socialbox"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="linkedin_url"
                              v-model="form.linkedin_url"
                              :label="__('LinkedIn URL')"
                              :error="form.errors.linkedin_url"
                              autocomplete="linkedin_url"
                              type="text"
                              name="linkedin_url"
                              :help="__('Eg: https://www.linkedin.com/in/minecraft')"
                              help-error-flex="flex-col"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_discordbox"
                              v-model="form.enable_discordbox"
                              :label="__('Discord Box')"
                              :help="__('Enable Discord Server Box')"
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
                              :label="__('Discord Server ID')"
                              :error="form.errors.discord_server_id"
                              autocomplete="discord_server_id"
                              type="text"
                              name="discord_server_id"
                              :help="__('Eg: 453365679416646355')"
                              help-error-flex="flex-col"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
                              id="enable_donation_box"
                              v-model="form.enable_donation_box"
                              :label="__('Donation Box')"
                              :help="__('Enable Donation Box.')"
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
                              :label="__('Donation URL')"
                              :error="form.errors.donation_box_url"
                              autocomplete="donation_box_url"
                              type="text"
                              name="donation_box_url"
                              :help="__('Eg: https://paypal.me/@username')"
                              help-error-flex="flex-col"
                            />
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-6">
                            <x-checkbox
                              id="enable_voteforserverbox"
                              v-model="form.enable_voteforserverbox"
                              :label="__('Vote for Server Box')"
                              :help="__('Enable Vote for server box.')"
                              name="enable_voteforserverbox"
                              :error="form.errors.enable_voteforserverbox"
                            />
                          </div>

                          <div
                            v-if="form.enable_voteforserverbox"
                            class="flex-col col-span-6 space-y-1 sm:col-span-6"
                          >
                            <div class="flex space-x-4">
                              <div class="w-5" />
                              <label
                                class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                              >{{ __("Voting Site Link") }}</label>
                              <label
                                class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                              >{{ __("Display Name") }}</label>
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
                                  :label="__('Voting Site URL :index', {index: index+1})"
                                  :error="form.errors[`voteforserverbox_content.${index}.url`]"
                                  type="text"
                                  help-error-flex="flex-col"
                                />
                              </div>
                              <div class="flex-1">
                                <x-input
                                  v-model="votingsite.name"
                                  :label="__('Voting Site Name :index', {index: index+1})"
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
                                {{ __("Add More") }}
                              </button>
                            </div>
                          </div>

                          <div class="flex items-center col-span-3 sm:col-span-3">
                            <x-checkbox
                              id="enable_status_feed"
                              v-model="form.enable_status_feed"
                              :label="__('Enable Status Feed')"
                              :help="__('Let player post status on homepage?')"
                              name="enable_status_feed"
                              :error="form.errors.enable_status_feed"
                            />
                          </div>

                          <div class="flex items-center col-span-3 sm:col-span-3">
                            <x-checkbox
                              id="enable_welcomebox"
                              v-model="form.enable_welcomebox"
                              :label="__('Welcome Box')"
                              :help="__('Enable welcome box in homepage.')"
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
                              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                            />
                            <jet-input-error
                              :message="form.errors.welcomebox_content"
                              class="mt-2"
                            />
                          </div>

                          <div class="col-span-6 sm:col-span-3">
                            <x-input
                              id="header_broadcast_text"
                              v-model="form.header_broadcast_text"
                              :label="__('Broadcast Text')"
                              :error="form.errors.header_broadcast_text"
                              type="text"
                              :help="__('Some important info or something to show in top at every page.')"
                              name="header_broadcast_text"
                            />
                          </div>
                          <div class="col-span-6 sm:col-span-3">
                            <x-input
                              id="header_broadcast_url"
                              v-model="form.header_broadcast_url"
                              :label="__('Broadcast URL')"
                              :error="form.errors.header_broadcast_url"
                              :help="__('URL to redirect when broadcast text is clicked. Leave empty to not redirect.')"
                              type="text"
                              name="header_broadcast_url"
                            />
                          </div>
                        </div>
                      </div>
                      <div class="flex justify-end px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6">
                        <loading-button
                          :loading="form.processing"
                          class="inline-flex justify-center px-4 py-2 text-sm font-bold text-white border border-transparent rounded-md shadow-sm bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600"
                          type="submit"
                        >
                          {{ __("Save General Settings") }}
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
  </AdminLayout>
</template>

<script>
import JetInputError from '@/Jetstream/InputError.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import Icon from '@/Components/Icon.vue';
import EasyMDE from 'easymde';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XCheckbox,
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
            form: useForm({
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
                enable_topplayersbox: this.settings.enable_topplayersbox,
                youtube_url: this.settings.youtube_url,
                facebook_url: this.settings.facebook_url,
                twitter_url: this.settings.twitter_url,
                twitch_url: this.settings.twitch_url,
                tiktok_url: this.settings.tiktok_url,
                linkedin_url: this.settings.linkedin_url,
                // threads_url: this.settings.threads_url,
                discord_invite_url: this.settings.discord_invite_url,
                enable_discordbox: this.settings.enable_discordbox,
                discord_server_id: this.settings.discord_server_id,
                enable_donation_box: this.settings.enable_donation_box,
                donation_box_url: this.settings.donation_box_url,
                enable_status_feed: this.settings.enable_status_feed,
                photo_light: null,
                photo_dark: null,
                header_broadcast_text: this.settings.header_broadcast_text,
                header_broadcast_url: this.settings.header_broadcast_url,
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
