<template>
  <app-layout>
    <app-head
      :title="__('Your Linked Players')"
    />

    <div class="py-3 px-2 md:py-12 md:px-10 max-w-6xl mx-auto space-y-4">
      <div
        class="mb-4 bg-white dark:bg-cool-gray-800 border-t-4 border-light-blue-500 rounded-b text-light-blue-900 dark:text-light-blue-400 px-4 py-3 shadow"
        role="alert"
      >
        <div class="flex">
          <div class="py-1">
            <svg
              class="fill-current h-6 w-6 text-light-blue-500 mr-4"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path
                d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"
              />
            </svg>
          </div>
          <div>
            <p class="font-bold">
              {{ __("You can link upto :count :player to your account! ( :left available )", {
                count: maxPlayerPerUser,
                player: maxPlayerPerUser === 1 ? __('player'): __('players'),
                left: maxPlayerPerUser - linkedPlayers.length
              }) }}
            </p>
            <p class="text-sm">
              {{ __("Initiate the process by joining the server and typing /account-link in chat. A link will be generated, click on that link and your player will added to your account.") }}
            </p>
          </div>
        </div>
      </div>


      <div
        class="md:grid gap-4 space-y-2 md:space-y-0"
        :class="linkedPlayers.length > 1 ? 'grid-cols-2' : 'grid-cols-1 place-items-center'"
      >
        <div
          v-if="linkedPlayers.length <= 0"
          class="pt-5 pb-5 pr-10 flex justify-center"
        >
          <p class="text-red-500 italic">
            {{ __("No players linked to your account right now.") }}
          </p>
        </div>

        <div
          v-for="player in linkedPlayers"
          :key="player.uuid"
          class="shadow pt-5 pb-5 pr-10 bg-white dark:bg-cool-gray-800 rounded flex justify-center"
        >
          <!-- <img :src="`https://crafatar.com/renders/body/${player.uuid}?scale=7`" :alt="player.username"> -->
          <canvas :id="`skin_container_${player.uuid}`" />
          <div class="flex flex-col space-y-2">
            <div class="username">
              <inertia-link
                as="a"
                :href="route('player.show', player.uuid)"
                class="font-bold text-lg text-light-blue-400 hover:text-light-blue-500"
              >
                {{ player.username }}
              </inertia-link>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ player.uuid }}
              </p>
            </div>

            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-gray-400">
                {{ __("Position") }}:
              </p>
              <div class="flex items-center space-x-2 text-center text-sm text-light-blue-400 font-extrabold">
                <span
                  v-if="player.position"
                  class="border-2 rounded text-lg px-2 border-light-blue-300 bg-light-blue-50 dark:bg-cool-gray-800"
                >
                  {{ player.position }}
                </span>
                <span
                  v-else
                  class="italic text-sm text-gray-500 dark:text-gray-400"
                >{{ __("None") }}</span>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-gray-400">
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
                class="italic text-sm text-gray-500 dark:text-gray-400"
              >
                {{ __("None") }}
              </p>
            </div>
            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-gray-400">
                {{ __("Rank") }}:
              </p>
              <div class="flex items-center space-x-2">
                <p
                  v-if="player.rank"
                  class="dark:text-gray-200"
                >
                  {{ player.rank.name }}
                </p>
                <p
                  v-else
                  class="italic text-sm text-gray-500 dark:text-gray-400"
                >
                  {{ __("None") }}
                </p>
                <img
                  v-if="player.rank && player.rank.photo_url"
                  v-tippy
                  :src="player.rank.photo_url"
                  :alt="player.rank.name"
                  :title="player.rank.name"
                  class="h-8 w-8 focus:outline-none"
                >
              </div>
            </div>
            <div class="flex items-center justify-between">
              <p class="font-bold dark:text-gray-400">
                {{ __("Country") }}:
              </p>
              <div class="flex items-center space-x-2">
                <p class="dark:text-gray-200">
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
              <p class="font-bold dark:text-gray-400">
                {{ __("Last Seen") }}:
              </p>
              <div class="flex items-center space-x-2">
                <p
                  v-tippy
                  class="focus:outline-none dark:text-gray-200"
                  :title="formatToDayDateString(player.last_seen_at)"
                >
                  {{
                    formatTimeAgoToNow(player.last_seen_at)
                  }}
                </p>
              </div>
            </div>

            <div class="flex justify-end">
              <inertia-link
                as="button"
                :preserve-scroll="true"
                :preserve-state="false"
                method="delete"
                :href="route('account-link.delete', player.uuid)"
                class="mt-5 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
              >
                {{ __("Unlink") }}&nbsp;<span class="hidden md:block">&nbsp;{{ player.username }}</span>
              </inertia-link>
            </div>
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

export default {

    components: {
        Icon,
        AppLayout,
    },
    props: {
        linkedPlayers: Array,
        maxPlayerPerUser: Number
    },
    setup() {
        const {formatTimeAgoToNow,formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow, formatToDayDateString};
    },
    mounted() {
        for (const player of this.linkedPlayers) {
            let skinViewer = new skinview3d.SkinViewer({
                canvas: document.getElementById(`skin_container_${player.uuid}`),
                width: 200,
                height: 300,
                skin: route('player.skin.get', {uuid: player.uuid, username: player.username}),
            });
            let control = skinview3d.createOrbitControls(skinViewer);
            control.enableRotate = true;
            control.enableZoom = false;
            control.enablePan = false;
            let walk = skinViewer.animations.add(skinview3d.WalkingAnimation);
            walk.speed = 0.1;
            let rotate = skinViewer.animations.add(skinview3d.RotatingAnimation);
            rotate.speed = 0.5;
        }
    },
};
</script>
