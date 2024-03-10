<script setup>
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import { FaceSmileIcon, UserGroupIcon,UserPlusIcon, FaceFrownIcon, ChartBarIcon, ArrowTrendingUpIcon  } from '@heroicons/vue/24/outline';
import { useAxios } from '@vueuse/integrations/useAxios';
import millify from 'millify';
import {useHelpers} from '@/Composables/useHelpers';
const { secondsToHMS } = useHelpers();

const { data, isFinished, isLoading, error } = useAxios(route('admin.graph.network-trends-vs-month'));
</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full shadow">
    <h3 class="p-3 font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
      {{ __("Network Trends") }}
    </h3>

    <div class="flex flex-col">
      <LoadingSpinner
        class="w-full mt-10"
        :loading="isLoading && !isFinished"
      />

      <div
        v-if="error"
        class="p-3 text-center bg-red-100 dark:bg-red-800/20 text-red-500 dark:text-red-400"
      >
        {{ error.message }}
      </div>

      <div class="overflow-x-auto">
        <div class="inline-block min-w-full align-middle">
          <div class="overflow-hidden shadow-lg shadow-gray-200">
            <table
              v-if="!isLoading && isFinished && !error"
              class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
            >
              <thead>
                <tr>
                  <th
                    scope="col"
                    class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-400 uppercase whitespace-nowrap"
                  >
                    {{ __("Metric") }}
                  </th>
                  <th
                    scope="col"
                    class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-400 uppercase whitespace-nowrap"
                  >
                    {{ __("Last Month") }}
                  </th>
                  <th
                    scope="col"
                    class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-400 uppercase whitespace-nowrap"
                  >
                    {{ __("This Month") }}
                  </th>
                  <th
                    scope="col"
                    class="p-4 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-400 uppercase whitespace-nowrap"
                  >
                    {{ __("Trend") }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr>
                  <td class="flex items-center p-4 text-sm font-normal text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    <UserGroupIcon
                      class="w-5 h-5 text-light-blue-400"
                    />
                    <span
                      v-tippy="{trigger: 'click'}"
                      :title="__('All players including old and new who is seen on the given time interval')"
                      class="mx-5 ml-3 w-32 font-medium text-gray-900 dark:text-gray-300 sm:flex-none"
                    >{{ __("Total Players") }}</span>
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ data.total_players.previous_month }}
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ data.total_players.current_month }}
                  </td>
                  <td
                    class="p-4 text-sm whitespace-nowrap"
                    :class="{'text-red-500 font-semibold' : data.total_players.change < 0, 'text-green-500 font-semibold' : data.total_players.change > 0, 'text-gray-500 dark:text-gray-400 font-normal' : data.total_players.change == 0}"
                  >
                    {{ data.total_players.change }}%
                  </td>
                </tr>
                <tr>
                  <td class="flex items-center p-4 text-sm font-normal text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    <UserGroupIcon class="w-5 h-5 text-green-400" />
                    <span
                      v-tippy="{trigger: 'click'}"
                      :title="__('New players who is seen on the given time interval')"
                      class="mx-5 ml-3 w-32 font-medium text-gray-900 dark:text-gray-300 sm:flex-none"
                    >{{ __("New Players") }}</span>
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ data.total_new_players.previous_month }}
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ data.total_new_players.current_month }}
                  </td>
                  <td
                    class="p-4 text-sm whitespace-nowrap"
                    :class="{'text-red-500 font-semibold' : data.total_new_players.change < 0, 'text-green-500 font-semibold' : data.total_new_players.change > 0, 'text-gray-500 dark:text-gray-400 font-normal' : data.total_new_players.change == 0}"
                  >
                    {{ data.total_new_players.change }}%
                  </td>
                </tr>
                <tr>
                  <td class="flex items-center p-4 text-sm font-normal text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    <UserPlusIcon class="w-5 h-5 text-orange-400" />
                    <span
                      v-tippy="{trigger: 'click'}"
                      :title="__('Total sessions of players. One session is counted when player join server and then leave. A player can have multiple sessions.')"
                      class="flex-none mx-5 ml-3 w-32 font-medium text-gray-900 dark:text-gray-300"
                    >{{ __("Total Sessions") }}</span>
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ data.total_player_sessions.previous_month }}
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ data.total_player_sessions.current_month }}
                  </td>
                  <td
                    class="p-4 text-sm whitespace-nowrap"
                    :class="{'text-red-500 font-semibold' : data.total_player_sessions.change < 0, 'text-green-500 font-semibold' : data.total_player_sessions.change > 0, 'text-gray-500 dark:text-gray-400 font-normal' : data.total_player_sessions.change == 0}"
                  >
                    {{ data.total_player_sessions.change }}%
                  </td>
                </tr>
                <tr>
                  <td class="flex items-center p-4 text-sm font-normal text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    <FaceSmileIcon class="w-5 h-5 text-green-400" />
                    <span
                      v-tippy="{trigger: 'click'}"
                      :title="__('Average playtime player played during a session.')"
                      class="flex-none mx-5 ml-3 w-32 font-medium text-gray-900 dark:text-gray-300"
                    >{{ __("Avg Session Time") }}</span>
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ secondsToHMS(data.avg_playtime.previous_month, true) }}
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ secondsToHMS(data.avg_playtime.current_month, true) }}
                  </td>
                  <td
                    class="p-4 text-sm whitespace-nowrap"
                    :class="{'text-red-500 font-semibold' : data.avg_playtime.change < 0, 'text-green-500 font-semibold' : data.avg_playtime.change > 0, 'text-gray-500 dark:text-gray-400 font-normal' : data.avg_playtime.change == 0}"
                  >
                    {{ data.avg_playtime.change }}%
                  </td>
                </tr>
                <tr>
                  <td class="flex items-center p-4 text-sm font-normal text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    <FaceFrownIcon class="w-5 h-5 text-red-400" />
                    <span
                      v-tippy="{trigger: 'click'}"
                      :title="__('Average afktime player spent during a session.')"
                      class="flex-none mx-5 ml-3 w-32 font-medium text-gray-900 dark:text-gray-300"
                    >{{ __("Avg AFK Time") }}</span>
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ secondsToHMS(data.avg_afktime.previous_month, true) }}
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ secondsToHMS(data.avg_afktime.current_month, true) }}
                  </td>
                  <td
                    class="p-4 text-sm whitespace-nowrap"
                    :class="{'text-red-500 font-semibold' : data.avg_afktime.change < 0, 'text-green-500 font-semibold' : data.avg_afktime.change > 0, 'text-gray-500 dark:text-gray-400 font-normal' : data.avg_afktime.change == 0}"
                  >
                    {{ data.avg_afktime.change }}%
                  </td>
                </tr>
                <tr>
                  <td class="flex items-center p-4 text-sm font-normal text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    <ChartBarIcon class="w-5 h-5 text-yellow-400" />
                    <span
                      v-tippy="{trigger: 'click'}"
                      :title="__('Average ping players getting on your servers.')"
                      class="flex-none mx-5 ml-3 w-32 font-medium text-gray-900 dark:text-gray-300"
                    >{{ __("Avg Player Ping") }}</span>
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ millify(data.avg_player_ping.previous_month) }} ms
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ millify(data.avg_player_ping.current_month) }} ms
                  </td>
                  <td
                    class="p-4 text-sm whitespace-nowrap"
                    :class="{'text-red-500 font-semibold' : data.avg_player_ping.change < 0, 'text-green-500 font-semibold' : data.avg_player_ping.change > 0, 'text-gray-500 dark:text-gray-400 font-normal' : data.avg_player_ping.change == 0}"
                  >
                    {{ data.avg_player_ping.change }}%
                  </td>
                </tr>
                <tr>
                  <td class="flex items-center p-4 text-sm font-normal text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    <ArrowTrendingUpIcon class="w-5 h-5 text-light-blue-400" />
                    <span
                      v-tippy="{trigger: 'click'}"
                      :title="__('Peek Online player reached on one server.')"
                      class="flex-none mx-5 ml-3 w-32 font-medium text-gray-900 dark:text-gray-300"
                    >Peek Online Players</span>
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ __(":players players", {players: data.peek_online_players.previous_month}) }}
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap">
                    {{ __(":players players", {players: data.peek_online_players.current_month}) }}
                  </td>
                  <td
                    class="p-4 text-sm whitespace-nowrap"
                    :class="{'text-red-500 font-semibold' : data.peek_online_players.change < 0, 'text-green-500 font-semibold' : data.peek_online_players.change > 0, 'text-gray-500 dark:text-gray-400 font-normal' : data.peek_online_players.change == 0}"
                  >
                    {{ data.peek_online_players.change }}%
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
