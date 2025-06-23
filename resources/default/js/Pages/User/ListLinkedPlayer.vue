<template>
  <app-layout>
    <app-head
      :title="__('Your Linked Players')"
    />

    <div
      class="max-w-6xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10"
    >
      <div
        class="px-4 py-3 mb-4 bg-white border-t-4 rounded-b shadow dark:bg-surface-800"
        :class="{
          'border-primary text-primary dark:text-primary': linkSlotsLeft > 0,
          'border-orange-500 opacity-35 text-orange-900 dark:text-orange-400': linkSlotsLeft <= 0
        }"
        role="alert"
      >
        <div class="flex">
          <div class="py-1">
            <svg
              class="w-6 h-6 mr-4 fill-current text-primary"
              :class="{
                'text-orange-500': linkSlotsLeft <= 0
              }"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path
                d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"
              />
            </svg>
          </div>
          <div class="w-full">
            <p class="font-bold">
              <span
                v-if="linkSlotsLeft > 0"
              >
                {{ __("You can link upto :count :player to your account! ( :left available )", {
                  count: maxPlayerPerUser,
                  player: maxPlayerPerUser === 1 ? __('player'): __('players'),
                  left: linkSlotsLeft
                }) }}
              </span>
              <span v-else>
                {{ __("You have already linked maximum number of players to your account!") }}
              </span>
            </p>
            <div class="flex flex-col items-center mt-2 text-sm">
              <p class="text-foreground dark:text-foreground">
                {{ __("To link a player to your account, join server and type '/link :otp' in your chat.", {
                  otp: currentLinkOtp?.otp
                }) }}
              </p>
              <h1
                :class="{
                  'line-through': otpExpiryCountdownSeconds <= 0
                }"
                class="font-mono text-6xl font-extrabold tracking-widest text-center text-black dark:text-white"
              >
                {{ currentLinkOtp?.otp }}
              </h1>

              <copy-to-clipboard v-slot="props">
                <button
                  v-tippy
                  :title="__('Click to Copy')"
                  type="button"
                  class="p-2 mt-3 font-semibold text-center font-mono tracking-wider text-foreground transition duration-150 ease-in-out border border-foreground rounded w-full md:w-1/2 dark:border-foreground dark:text-foreground hover:text-primary dark:hover:text-primary hover:bg-primary dark:hover:bg-surface-900 hover:border-primary dark:hover:border-foreground focus:ring focus:ring-primary focus:ring-opacity-50 focus:outline-none"
                  @click="props.copy('/link ' + currentLinkOtp?.otp)"
                >
                  <span v-if="props.status !== 'copied'">
                    {{ '/link ' + currentLinkOtp?.otp }}
                  </span>
                  <span v-else>
                    {{ __("Copied!") }}
                  </span>
                </button>
              </copy-to-clipboard>

              <p
                v-if="otpExpiryCountdownSeconds > 0"
                class="italic dark:text-foreground mt-4"
              >
                {{ __("This OTP will expire in :seconds seconds.", {
                  seconds: otpExpiryCountdownSeconds
                }) }}
              </p>
              <div v-else>
                <p class="dark:text-foreground mt-4">
                  {{ __("This OTP has expired. Refresh the page to get a new OTP.") }}

                  <inertia-link
                    as="button"
                    :href="route('linked-player.list')"
                    class="dark:text-primary dark:hover:text-primary text-primary hover:text-primary hover:underline"
                  >
                    {{ __("Click here to refresh.") }}
                  </inertia-link>
                </p>
              </div>
              <div class="text-xs font-bold mt-2 text-error-500 dark:text-error-400">
                {{ __("CAUTION: DO NOT SHARE THIS OTP WITH ANYONE!") }}
              </div>
            </div>
          </div>
        </div>
      </div>


      <div
        class="gap-4 space-y-2 md:grid md:space-y-0"
        :class="linkedPlayers.length > 1 ? 'grid-cols-2' : 'grid-cols-1 place-items-center'"
      >
        <div
          v-if="linkedPlayers.length <= 0"
          class="flex justify-center pt-5 pb-5 pr-10"
        >
          <p class="italic text-error-500">
            {{ __("No players linked to your account right now.") }}
          </p>
        </div>

        <div
          v-for="player in linkedPlayers"
          :key="player.uuid"
          class="flex flex-col p-4 items-center bg-white rounded shadow dark:bg-surface-800"
        >

        <div class="flex justify-center pr-10">
          <!-- <img :src="`https://crafatar.com/renders/body/${player.uuid}?scale=7`" :alt="player.username"> -->
          <canvas :id="`skin_container_${player.uuid}`" />
          <div class="flex flex-col space-y-2">
            <div class="username">
              <inertia-link
                as="a"
                :href="route('player.show', player.uuid)"
                class="text-lg font-bold text-primary hover:text-primary"
              >
                {{ player.username }}
              </inertia-link>
              <p class="text-xs text-foreground dark:text-foreground">
                {{ player.uuid }}
              </p>
            </div>

            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-foreground">
                {{ __("Position") }}:
              </p>
              <div class="flex items-center space-x-2 text-sm font-extrabold text-center text-primary">
                <span
                  v-if="player.position"
                  class="px-2 text-lg border-2 rounded border-primary bg-primary dark:bg-surface-800"
                >
                  {{ player.position }}
                </span>
                <span
                  v-else
                  class="text-sm italic text-foreground dark:text-foreground"
                >{{ __("None") }}</span>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-foreground">
                {{ __("Rating") }}:
              </p>
              <icon
                v-if="player.rating != null"
                v-tippy
                class="w-8 h-8 focus:outline-none"
                :name="`rating-${player.rating}`"
                :content="player.rating"
              />
              <p
                v-else
                class="text-sm italic text-foreground dark:text-foreground"
              >
                {{ __("None") }}
              </p>
            </div>
            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-foreground">
                {{ __("Rank") }}:
              </p>
              <div class="flex items-center space-x-2">
                <p
                  v-if="player.rank"
                  class="dark:text-foreground"
                >
                  {{ player.rank.name }}
                </p>
                <p
                  v-else
                  class="text-sm italic text-foreground dark:text-foreground"
                >
                  {{ __("None") }}
                </p>
                <img
                  v-if="player.rank && player.rank.photo_url"
                  v-tippy
                  :src="player.rank.photo_url"
                  :alt="player.rank.name"
                  :title="player.rank.name"
                  class="max-h-12 max-w-12 focus:outline-none"
                >
              </div>
            </div>
            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-foreground">
                {{ __("Country") }}:
              </p>
              <div class="flex items-center space-x-2">
                <p class="dark:text-foreground">
                  {{ player.country.name }}
                </p>
                <img
                  v-tippy
                  :title="player.country.name"
                  :src="player.country.photo_path"
                  :alt="player.country.name"
                  class="h-8 w-8 -mt-0.5 focus:outline-none"
                >
              </div>
            </div>

            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-foreground">
                {{ __("Last Seen") }}:
              </p>
              <div class="flex items-center space-x-2">
                <p
                  v-tippy
                  class="focus:outline-none dark:text-foreground"
                  :title="formatToDayDateString(player.last_seen_at)"
                >
                  {{
                    formatTimeAgoToNow(player.last_seen_at)
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-4">
              <inertia-link
                v-if="$page.props?.pluginSettings?.playerPasswordResetEnabled"
                v-tippy
                as="a"
                :href="route('reset-player-password.show', {
                  player_uuid: player.uuid,
                })"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-surface-600 hover:bg-surface-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-foreground disabled:opacity-50"
                :title="__('Change Password of this player.')"
              >
                <LockClosedIcon class="w-5 h-5" />
                <span class="hidden md:block mt-0.5 ml-1">
                {{ __("Change Password") }}
                </span>
              </inertia-link>

              <inertia-link
                v-if="$page.props.playerSkinChangerEnabled"
                v-tippy
                as="a"
                :href="route('change-player-skin.show', {
                  player_uuid: player.uuid,
                })"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary hover:bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
                :title="__('Change Skin of this player.')"
              >
                <PaintBrushIcon class="w-5 h-5" />
                <span class="hidden md:block mt-0.5 ml-1">
                {{ __("Change Skin") }}
                </span>
              </inertia-link>

              <inertia-link
                v-if="!isUnlinkingDisabled"
                v-tippy
                v-confirm="{message: __('Are you sure you want to unlink this player from your account?')}"
                as="button"
                :preserve-scroll="true"
                :preserve-state="false"
                method="delete"
                :href="route('account-link.delete', player.uuid)"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-error-600 border border-transparent rounded-md shadow-sm hover:bg-error-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error-500 disabled:opacity-50"
                :title="__('Unlink this player from your account.')"
              >
                <BookmarkSlashIcon class="w-5 h-5" />
                <span class="hidden md:block mt-0.5 ml-1">
                {{ __("Unlink Player") }}
                </span>
              </inertia-link>
        </div>

        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import * as skinview3d from 'skinview3d';
import { useHelpers } from '@/Composables/useHelpers';
import { PaintBrushIcon, BookmarkSlashIcon, LockClosedIcon } from '@heroicons/vue/24/solid';
import CopyToClipboard from '@/Components/CopyToClipboard.vue';

export default {

    components: {
        Icon,
        AppLayout,
        PaintBrushIcon,
        CopyToClipboard,
        BookmarkSlashIcon,
        LockClosedIcon
    },
    props: {
        linkedPlayers: Array,
        maxPlayerPerUser: Number,
        currentLinkOtp: Object,
        isUnlinkingDisabled: Boolean,
    },
    setup() {
        const {formatTimeAgoToNow,formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow, formatToDayDateString};
    },
    data() {
        return {
            skinViewers: [],
            otpExpiryCountdownInterval: null,
            otpExpiryCountdownSeconds: this.currentLinkOtp?.expires_at ? Math.floor((new Date(this.currentLinkOtp.expires_at).getTime() - new Date().getTime()) / 1000) : null,
        };
    },
    computed: {
        linkSlotsLeft () {
            return this.maxPlayerPerUser - this.linkedPlayers.length;
        }
    },
    unmounted() {
        for (const skinViewer of this.skinViewers) {
            skinViewer.dispose();
        }

        clearInterval(this.otpExpiryCountdownInterval);
    },
    mounted() {
        for (const player of this.linkedPlayers) {
            let skinViewer = new skinview3d.SkinViewer({
                canvas: document.getElementById(`skin_container_${player.uuid}`),
                width: 200,
                height: 300,
                skin: route('player.skin.get', {uuid: player.uuid, username: player.username, textureid: player.skin_texture_id}),
            });
            skinViewer.autoRotate = true;
            skinViewer.animation = new skinview3d.WalkingAnimation();
            skinViewer.animation.speed = 0.1;
            skinViewer.autoRotateSpeed = 0.5;
            this.skinViewers.push(skinViewer);
        }

        this.startOtpExpiryCountdown();
    },
    methods: {
        startOtpExpiryCountdown() {
            if (this.otpExpiryCountdownSeconds > 0) {
                this.otpExpiryCountdownInterval = setInterval(() => {
                    this.otpExpiryCountdownSeconds--;
                    if (this.otpExpiryCountdownSeconds <= 0) {
                        clearInterval(this.otpExpiryCountdownInterval);
                    }
                }, 1000);
            }
        },
    }
};
</script>
