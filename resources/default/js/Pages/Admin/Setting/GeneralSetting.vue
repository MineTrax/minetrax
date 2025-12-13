<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import ImageUpload from '@/Components/Form/ImageUpload.vue';
import TipTapEditor from '@/Components/TipTapEditor.vue';
import Icon from '@/Components/Icon.vue';
import Draggable from 'vuedraggable';
import { ArrowsUpDownIcon } from '@heroicons/vue/24/outline';

const { __ } = useTranslations();

const props = defineProps({
    settings: {
        type: Object,
        default: null
    }
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Settings'),
        current: false,
    },
    {
        text: __('General Settings'),
        current: true,
    }
];

const form = useForm({
    site_name: props.settings.site_name,
    enable_mcserver_onlineplayersbox: props.settings.enable_mcserver_onlineplayersbox,
    enable_mcserver_statuspingbox: props.settings.enable_mcserver_statuspingbox,
    enable_ingamechat: props.settings.enable_ingamechat,
    enable_shoutbox: props.settings.enable_shoutbox,
    enable_welcomebox: props.settings.enable_welcomebox,
    welcomebox_content: props.settings.welcomebox_content,
    enable_voteforserverbox: props.settings.enable_voteforserverbox,
    voteforserverbox_content: props.settings.voteforserverbox_content?.map((item, index) => ({
        ...item,
        id: index,
    })) || [{ url: '', name: '', id: 1 }],
    enable_onlineuserbox: props.settings.enable_onlineuserbox,
    enable_newuserbox: props.settings.enable_newuserbox,
    enable_didyouknowbox: props.settings.enable_didyouknowbox,
    enable_socialbox: props.settings.enable_socialbox,
    enable_topplayersbox: props.settings.enable_topplayersbox,
    youtube_url: props.settings.youtube_url,
    facebook_url: props.settings.facebook_url,
    twitter_url: props.settings.twitter_url,
    twitch_url: props.settings.twitch_url,
    tiktok_url: props.settings.tiktok_url,
    linkedin_url: props.settings.linkedin_url,
    instagram_url: props.settings.instagram_url,
    whatsapp_url: props.settings.whatsapp_url,
    telegram_url: props.settings.telegram_url,
    reddit_url: props.settings.reddit_url,
    threads_url: props.settings.threads_url,
    github_url: props.settings.github_url,
    discord_invite_url: props.settings.discord_invite_url,
    enable_discordbox: props.settings.enable_discordbox,
    discord_server_id: props.settings.discord_server_id,
    enable_donation_box: props.settings.enable_donation_box,
    donation_box_url: props.settings.donation_box_url,
    enable_status_feed: props.settings.enable_status_feed,
    photo_light: null,
    photo_dark: null,
    header_broadcast_text: props.settings.header_broadcast_text,
    header_broadcast_url: props.settings.header_broadcast_url,
});

function addMoreVotingSite() {
    form.voteforserverbox_content.push({
        url: '',
        name: '',
        id: form.voteforserverbox_content.length + 1,
    });
}

function deleteVotingSite(index) {
    form.voteforserverbox_content.splice(index, 1);
}

function saveSetting() {
    form.post(route('admin.setting.general.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('General Settings')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form
          autocomplete="off"
          @submit.prevent="saveSetting"
        >
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <!-- Site Header Logo Light -->
                <div class="col-span-6 sm:col-span-3">
                  <ImageUpload
                    id="photo_light"
                    name="photo_light"
                    :label="__('Site Header Logo Image Light (200x40)')"
                    v-model="form.photo_light"
                    :current-url="settings.site_header_logo_path_light"
                    :error="form.errors.photo_light"
                    :removable="false"
                    shape="rect"
                    :preview-class="'h-20 w-full max-w-xs'"
                    :upload-label="__('Upload')"
                    :change-label="__('Change')"
                    object-fit="contain"
                    :hint="__('PNG, JPG up to 100KB')"
                    :max-size-mb="0.1"
                  />
                </div>

                <!-- Site Header Logo Dark -->
                <div class="col-span-6 sm:col-span-3">
                  <ImageUpload
                    id="photo_dark"
                    name="photo_dark"
                    :label="__('Site Header Logo Image Dark (200x40)')"
                    v-model="form.photo_dark"
                    :current-url="settings.site_header_logo_path_dark"
                    :error="form.errors.photo_dark"
                    :removable="false"
                    shape="rect"
                    :preview-class="'h-20 w-full max-w-xs'"
                    :upload-label="__('Upload')"
                    :change-label="__('Change')"
                    object-fit="contain"
                    :hint="__('PNG, JPG up to 100KB')"
                    :max-size-mb="0.1"
                  />
                </div>

                <!-- Site Name -->
                <div class="col-span-6">
                  <XInput
                    id="site_name"
                    v-model="form.site_name"
                    :label="__('Site Name')"
                    :error="form.errors.site_name"
                    type="text"
                    name="site_name"
                  />
                </div>

                <!-- Homepage Box Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <XSwitch
                        id="enable_mcserver_onlineplayersbox"
                        v-model="form.enable_mcserver_onlineplayersbox"
                        :label="__('Online Players List Box')"
                        :help="__('Show live server player list in homepage. Query must be enabled for this to work.')"
                        name="enable_mcserver_onlineplayersbox"
                        :error="form.errors.enable_mcserver_onlineplayersbox"
                      />

                      <XSwitch
                        id="enable_mcserver_statuspingbox"
                        v-model="form.enable_mcserver_statuspingbox"
                        :label="__('Online Players Count Box')"
                        :help="__('Show live player count box. Ping must be enabled for this to work.')"
                        name="enable_mcserver_statuspingbox"
                        :error="form.errors.enable_mcserver_statuspingbox"
                      />

                      <XSwitch
                        id="enable_ingamechat"
                        v-model="form.enable_ingamechat"
                        :label="__('In-Game Chat')"
                        :help="__('Show ingame chatbox in homepage.')"
                        name="enable_ingamechat"
                        :error="form.errors.enable_ingamechat"
                      />

                      <XSwitch
                        id="enable_shoutbox"
                        v-model="form.enable_shoutbox"
                        :label="__('Shout Box')"
                        :help="__('Enable shoutbox')"
                        name="enable_shoutbox"
                        :error="form.errors.enable_shoutbox"
                      />

                      <XSwitch
                        id="enable_onlineuserbox"
                        v-model="form.enable_onlineuserbox"
                        :label="__('Online Users Box')"
                        :help="__('Enable online users list box.')"
                        name="enable_onlineuserbox"
                        :error="form.errors.enable_onlineuserbox"
                      />

                      <XSwitch
                        id="enable_newuserbox"
                        v-model="form.enable_newuserbox"
                        :label="__('Newest User Box')"
                        :help="__('Enable newest user box.')"
                        name="enable_newuserbox"
                        :error="form.errors.enable_newuserbox"
                      />

                      <XSwitch
                        id="enable_didyouknowbox"
                        v-model="form.enable_didyouknowbox"
                        :label="__('DidYouKnow Box')"
                        :help="__('Enable DidYouKnow Box')"
                        name="enable_didyouknowbox"
                        :error="form.errors.enable_didyouknowbox"
                      />

                      <XSwitch
                        id="enable_topplayersbox"
                        v-model="form.enable_topplayersbox"
                        :label="__('Top Players Box')"
                        :help="__('Enable Top Players Box in Homepage')"
                        name="enable_topplayersbox"
                        :error="form.errors.enable_topplayersbox"
                      />

                      <XSwitch
                        id="enable_status_feed"
                        v-model="form.enable_status_feed"
                        :label="__('Enable Status Feed')"
                        :help="__('Let player post status on post page?')"
                        name="enable_status_feed"
                        :error="form.errors.enable_status_feed"
                      />
                    </div>
                  </fieldset>
                </div>

                <!-- Discord Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="grid grid-cols-6 gap-4">
                      <div class="col-span-6 sm:col-span-3">
                        <XInput
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

                      <div class="col-span-6 sm:col-span-3">
                        <XInput
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

                      <div class="col-span-6">
                        <XSwitch
                          id="enable_discordbox"
                          v-model="form.enable_discordbox"
                          :label="__('Discord Box')"
                          :help="__('Enable Discord Server Box')"
                          name="enable_discordbox"
                          :error="form.errors.enable_discordbox"
                        />
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Social Links -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="grid grid-cols-6 gap-4">
                      <div class="col-span-6">
                        <XSwitch
                          id="enable_socialbox"
                          v-model="form.enable_socialbox"
                          :label="__('Socials Box')"
                          :help="__('Enable social box in homepage.')"
                          name="enable_socialbox"
                          :error="form.errors.enable_socialbox"
                        />
                      </div>

                      <template v-if="form.enable_socialbox">
                        <div class="col-span-6 sm:col-span-3">
                          <XInput
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

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
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

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
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

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
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

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
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

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
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

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
                            id="instagram_url"
                            v-model="form.instagram_url"
                            :label="__('Instagram URL')"
                            :error="form.errors.instagram_url"
                            autocomplete="instagram_url"
                            type="text"
                            name="instagram_url"
                            :help="__('Eg: https://www.instagram.com/minecraft')"
                            help-error-flex="flex-col"
                          />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
                            id="whatsapp_url"
                            v-model="form.whatsapp_url"
                            :label="__('WhatsApp URL')"
                            :error="form.errors.whatsapp_url"
                            autocomplete="whatsapp_url"
                            type="text"
                            name="whatsapp_url"
                            :help="__('Eg: https://wa.me/123456789')"
                            help-error-flex="flex-col"
                          />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
                            id="telegram_url"
                            v-model="form.telegram_url"
                            :label="__('Telegram URL')"
                            :error="form.errors.telegram_url"
                            autocomplete="telegram_url"
                            type="text"
                            name="telegram_url"
                            :help="__('Eg: https://t.me/minecraft')"
                            help-error-flex="flex-col"
                          />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
                            id="reddit_url"
                            v-model="form.reddit_url"
                            :label="__('Reddit URL')"
                            :error="form.errors.reddit_url"
                            autocomplete="reddit_url"
                            type="text"
                            name="reddit_url"
                            :help="__('Eg: https://www.reddit.com/r/minecraft')"
                            help-error-flex="flex-col"
                          />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
                            id="threads_url"
                            v-model="form.threads_url"
                            :label="__('Threads URL')"
                            :error="form.errors.threads_url"
                            autocomplete="threads_url"
                            type="text"
                            name="threads_url"
                            :help="__('Eg: https://www.threads.net/@minecraft')"
                            help-error-flex="flex-col"
                          />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                          <XInput
                            id="github_url"
                            v-model="form.github_url"
                            :label="__('GitHub URL')"
                            :error="form.errors.github_url"
                            autocomplete="github_url"
                            type="text"
                            name="github_url"
                            :help="__('Eg: https://www.github.com/minetrax')"
                            help-error-flex="flex-col"
                          />
                        </div>
                      </template>
                    </div>
                  </fieldset>
                </div>

                <!-- Donation Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="grid grid-cols-6 gap-4">
                      <div class="col-span-6 sm:col-span-3">
                        <XSwitch
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
                        <XInput
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
                    </div>
                  </fieldset>
                </div>

                <!-- Vote for Server Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="space-y-4">
                      <XSwitch
                        id="enable_voteforserverbox"
                        v-model="form.enable_voteforserverbox"
                        :label="__('Vote for Server Box')"
                        :help="__('Enable Vote for server box.')"
                        name="enable_voteforserverbox"
                        :error="form.errors.enable_voteforserverbox"
                      />

                      <div
                        v-if="form.enable_voteforserverbox"
                        class="space-y-3"
                      >
                        <div class="flex space-x-4">
                          <div class="w-5" />
                          <div class="w-5" />
                          <label class="flex-1 block text-sm font-medium text-foreground">
                            {{ __("Voting Site Link") }}
                          </label>
                          <label class="flex-1 block text-sm font-medium text-foreground">
                            {{ __("Display Name") }}
                          </label>
                        </div>

                        <Draggable
                          v-model="form.voteforserverbox_content"
                          :swap-threshold="0.65"
                          class="space-y-2"
                          item-key="id"
                          handle=".drag-handle"
                        >
                          <template #item="{ element: votingsite, index }">
                            <div class="flex space-x-4 items-start">
                              <div class="drag-handle cursor-move mt-6">
                                <ArrowsUpDownIcon class="w-5 h-5 text-muted-foreground hover:text-foreground" />
                              </div>
                              <button
                                type="button"
                                class="focus:outline-none group mt-6"
                                @click="deleteVotingSite(index)"
                              >
                                <Icon
                                  class="w-5 h-5 text-muted-foreground group-hover:text-destructive"
                                  name="trash"
                                />
                              </button>
                              <div class="flex-1">
                                <XInput
                                  v-model="votingsite.url"
                                  :label="__('Voting Site URL :index', { index: index + 1 })"
                                  :error="form.errors[`voteforserverbox_content.${index}.url`]"
                                  type="text"
                                  help-error-flex="flex-col"
                                />
                              </div>
                              <div class="flex-1">
                                <XInput
                                  v-model="votingsite.name"
                                  :label="__('Voting Site Name :index', { index: index + 1 })"
                                  :error="form.errors[`voteforserverbox_content.${index}.name`]"
                                  type="text"
                                  help-error-flex="flex-col"
                                />
                              </div>
                            </div>
                          </template>
                        </Draggable>

                        <div class="flex justify-end">
                          <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="addMoreVotingSite"
                          >
                            {{ __("Add More") }}
                          </Button>
                        </div>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Welcome Box Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="space-y-4">
                      <XSwitch
                        id="enable_welcomebox"
                        v-model="form.enable_welcomebox"
                        :label="__('Welcome Box')"
                        :help="__('Enable welcome box in homepage.')"
                        name="enable_welcomebox"
                        :error="form.errors.enable_welcomebox"
                      />

                      <div
                        v-show="form.enable_welcomebox"
                        class="mt-4"
                      >
                        <TipTapEditor
                          id="welcomebox_content"
                          v-model="form.welcomebox_content"
                        />
                        <p
                          v-if="form.errors.welcomebox_content"
                          class="text-xs text-destructive mt-2"
                        >
                          {{ form.errors.welcomebox_content }}
                        </p>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Broadcast Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="grid grid-cols-6 gap-4">
                      <div class="col-span-6 sm:col-span-3">
                        <XInput
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
                        <XInput
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
                  </fieldset>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
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
                {{ __("Save General Settings") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
