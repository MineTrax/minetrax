<template>
  <app-layout>
    <app-head
      :title="__('Players')"
    />

    <div class="py-4 px-2 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="md:flex space-x-4">
        <div class="flex-grow">
          <div class="grid grid-cols-12 gap-4 mb-4">
            <div class="col-span-12  md:col-span-4">
              <div class="flex flex-row bg-white dark:bg-cool-gray-800 shadow rounded p-4">
                <div class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-light-blue-100 dark:bg-opacity-10 text-light-blue-500">
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  /></svg>
                </div>
                <div class="flex flex-col flex-grow ml-4">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Total Players") }}
                  </div>
                  <div class="font-bold dark:text-gray-200 text-lg">
                    {{ totalPlayersCount }}
                  </div>
                </div>
              </div>
            </div>
            <div class="col-span-12  md:col-span-4">
              <div class="flex flex-row bg-white dark:bg-cool-gray-800 shadow rounded p-4">
                <div class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-green-100 dark:bg-opacity-10 text-green-500">
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                  /></svg>
                </div>
                <div class="flex flex-col flex-grow ml-4">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Active Players") }}
                  </div>
                  <div class="font-bold text-lg dark:text-gray-200">
                    {{ activePlayersCount }}
                  </div>
                </div>
              </div>
            </div>
            <div class="col-span-12  md:col-span-4">
              <div class="flex flex-row bg-white dark:bg-cool-gray-800 shadow rounded p-4">
                <div class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-pink-100 dark:bg-opacity-10 text-pink-500">
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  /></svg>
                </div>
                <div class="flex flex-col flex-grow ml-4">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __("Play Time") }}
                  </div>
                  <div class="font-bold text-lg dark:text-gray-200">
                    {{ totalPlayTime === 0 ? '0 h' : secondsToHMS(totalPlayTime) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="-my-2 overflow-x-auto md:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
              <div class="shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 dark:border-none rounded">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-100 dark:bg-cool-gray-800 text-gray-700 dark:text-gray-200">
                    <tr>
                      <th
                        scope="col"
                        class="px-2 py-3 text-left text-xs font-bold text-center uppercase tracking-wider"
                      >
                        {{ __("#") }}
                      </th>
                      <th
                        scope="col"
                        class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider"
                      >
                        {{ __("Flag") }}
                      </th>
                      <th
                        scope="col"
                        class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider"
                      >
                        {{ __("Rank") }}
                      </th>
                      <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider"
                      >
                        {{ __("Name") }}
                      </th>
                      <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider"
                      >
                        {{ __("Rating") }}
                      </th>
                      <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider"
                      >
                        {{ __("Score") }}
                      </th>
                      <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider"
                      >
                        {{ __("Time Played") }}
                      </th>
                      <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider"
                      >
                        {{ __("Last Seen") }}
                      </th>
                    </tr>
                  </thead>

                  <tbody class="bg-white dark:bg-cool-gray-800 divide-y divide-gray-200 dark:divide-none">
                    <infinite-scroll :load-more="loadMorePlayers">
                      <tr
                        v-for="(player, index) in playersList.data"
                        :key="player.uuid"
                        :class="{'bg-gray-50 dark:bg-cool-gray-700 dark:bg-opacity-10': index % 2 === 1}"
                      >
                        <td class="px-2 py-4 whitespace-nowrap text-center text-sm text-light-blue-400 font-extrabold">
                          <span
                            v-if="player.position"
                            class="border-2 rounded text-lg px-2 border-light-blue-300 bg-light-blue-50 dark:bg-transparent"
                          >
                            {{ player.position }}
                          </span>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div
                              v-tippy
                              class="flex-shrink-0 h-10 w-10 focus:outline-none"
                              :content="player.country.name"
                            >
                              <img
                                class="h-10 w-10"
                                :src="player.country.photo_path"
                                alt=""
                              >
                            </div>
                          </div>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                          <div
                            v-if="player.rank"
                            class="flex items-center"
                          >
                            <div
                              v-tippy
                              class="flex-shrink-0 h-10 w-10 focus:outline-none"
                              :content="player.rank.name"
                            >
                              <img
                                class="h-10 w-10"
                                :src="player.rank.photo_url"
                                alt=""
                              >
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                              <img
                                class="h-10 w-10"
                                :src="player.avatar_url"
                                alt=""
                              >
                            </div>
                            <div class="ml-4">
                              <inertia-link
                                v-tippy
                                as="div"
                                :href="route('player.show', player.uuid)"
                                class="text-sm font-medium text-gray-900 dark:text-gray-200 focus:outline-none cursor-pointer hover:underline"
                                :content="player.uuid"
                              >
                                <span
                                  v-if="player.username"
                                  :class="player.is_active ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400'"
                                  class="font-extrabold"
                                >{{ player.username }}</span>
                                <span
                                  v-else
                                  class="text-red-500 italic"
                                >{{ __("Unknown") }}</span>
                              </inertia-link>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                          <span v-if="player.rating != null">
                            <icon
                              v-tippy
                              class="w-10 h-10 focus:outline-none"
                              :name="`rating-${player.rating}`"
                              :content="player.rating"
                            />
                          </span>
                          <span
                            v-else
                            class="text-gray-700 dark:text-gray-400 italic"
                          >{{ __("none") }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                          {{ player.total_score }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                          {{ secondsToHMS(player.play_time, true) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                          <span
                            v-tippy
                            class="focus:outline-none"
                            :content="formatToDayDateString(player.last_seen_at)"
                          >
                            {{
                              player.last_seen_at ? formatTimeAgoToNow(player.last_seen_at) : "unknown"
                            }}
                          </span>
                        </td>
                      </tr>

                      <template #loading>
                        <tr>
                          <td
                            class="border-t dark:border-none px-6 py-4 sm:text-center text-gray-700 dark:text-gray-500 italic"
                            colspan="8"
                          >
                            {{ __("Loading more...") }}
                          </td>
                        </tr>
                      </template>
                    </infinite-scroll>

                    <tr v-if="playersList.data.length === 0">
                      <td
                        class="px-6 py-4 text-center dark:text-gray-300 text-gray-700"
                        colspan="8"
                      >
                        {{ __("No players found.") }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="hidden md:flex flex-col space-y-4 flex-none w-1/4 h-screen sticky top-5">
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useAuthorizable } from '@/Composables/useAuthorizable';

export default {

    components: {
        ServerStatusBox,
        Icon,
        AppLayout,
        ShoutBox,
        InfiniteScroll
    },
    props: {
        players: Object,
        totalPlayersCount: Number,
        activePlayersCount: Number,
        totalPlayTime: {
            type: [String, Number]
        },
        lastScanAt: String,
    },
    setup() {
        const {can} = useAuthorizable();
        const {formatTimeAgoToNow, formatToDayDateString, secondsToHMS} = useHelpers();
        return {can, formatTimeAgoToNow, formatToDayDateString, secondsToHMS};
    },
    data() {
        return {
            playersList: this.players
        };
    },
    methods: {
        loadMorePlayers() {
            if (!this.playersList.next_page_url) {
                return Promise.resolve();
            }

            return axios(this.playersList.next_page_url).then(response => {
                this.playersList = {
                    ...response.data,
                    data: [...this.playersList.data, ...response.data.data]
                };
            });
        }
    },
};
</script>
