<script setup>
import { onMounted, ref } from 'vue';
import Icon from '@/Components/Icon.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import {useHelpers} from '@/Composables/useHelpers';
import { ClockIcon, FireIcon, QrCodeIcon, UserGroupIcon, UsersIcon } from '@heroicons/vue/24/outline';
const { secondsToHMS } = useHelpers();
import millify from 'millify';

const props = defineProps({
    servers: {
        type: Array,
        required: false,
    },
});

let numbers = ref(null);
let isLoading = ref(true);

async function fetchData() {
    isLoading.value = true;

    let params = {};

    if (props.servers && props.servers.length > 0) {
        params['servers'] = props.servers;
    }

    const response = await axios.get(
        route('admin.intel.server.index.numbers', params)
    );

    isLoading.value = false;
    numbers.value = response.data.numbers;
}

onMounted(async () => {
    await fetchData();
});

</script>

<template>
  <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full p-3 shadow">
    <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex mt-2 items-center">
      <QrCodeIcon
        class="w-6 mr-1"
      />
      {{ __("Numbers") }}
    </h3>

    <div
      v-if="isLoading"
      class="h-80 flex justify-center items-center"
    >
      <LoadingSpinner :loading="isLoading" />
    </div>

    <div
      v-else
      class="m-0 p-0"
    >
      <table class="table-auto min-w-full text-sm dark:text-gray-300 text-gray-700">
        <thead class="border-b dark:border-gray-700">
          <tr>
            <th
              scope="col"
              class="text-left p-2"
            />
            <th
              scope="col"
              class="text-left p-2"
            >
              {{ __("Last 24 hours") }}
            </th>
            <th
              scope="col"
              class="text-left p-2"
            >
              {{ __("Last 7 days") }}
            </th>
            <th
              scope="col"
              class="text-left p-2"
            >
              {{ __("Last 30 days") }}
            </th>
            <th
              scope="col"
              class="text-left p-2"
            >
              {{ __("All Time") }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="p-2 flex">
              <UserGroupIcon
                class="w-5 text-indigo-500 mr-1"
              />
              {{ __("Total Players") }}
            </td>
            <td class="p-2">
              {{ numbers.total_players.last_24h }}
            </td>
            <td class="p-2">
              {{ numbers.total_players.last_7days }}
            </td>
            <td class="p-2">
              {{ numbers.total_players.last_30days }}
            </td>
            <td class="p-2">
              {{ numbers.total_players.all_time }}
            </td>
          </tr>

          <tr>
            <td class="p-2 flex">
              <ClockIcon
                class="w-5 text-green-500 mr-1"
              />
              {{ __("Total Play Time") }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.total_playtime.last_24h, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.total_playtime.last_7days, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.total_playtime.last_30days, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.total_playtime.all_time, true) }}
            </td>
          </tr>

          <tr>
            <td class="p-2 flex">
              <ClockIcon
                class="w-5 text-red-500 mr-1"
              />
              {{ __("Total Afk Time") }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.total_afktime.last_24h, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.total_afktime.last_7days, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.total_afktime.last_30days, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.total_afktime.all_time, true) }}
            </td>
          </tr>

          <tr>
            <td class="p-2 flex">
              <UsersIcon
                class="w-5 text-green-500 mr-1"
              />
              {{ __("Total Sessions") }}
            </td>
            <td class="p-2">
              {{ numbers.total_sessions.last_24h }}
            </td>
            <td class="p-2">
              {{ numbers.total_sessions.last_7days }}
            </td>
            <td class="p-2">
              {{ numbers.total_sessions.last_30days }}
            </td>
            <td class="p-2">
              {{ numbers.total_sessions.all_time }}
            </td>
          </tr>

          <tr>
            <td class="p-2 flex">
              <UsersIcon
                class="w-5 text-lime-500 mr-1"
              />
              {{ __("Avg Session / Player") }}
            </td>
            <td class="p-2">
              {{ millify(numbers.avg_session_per_player.last_24h, {precision: 2}) }}
            </td>
            <td class="p-2">
              {{ millify(numbers.avg_session_per_player.last_7days, {precision: 2}) }}
            </td>
            <td class="p-2">
              {{ millify(numbers.avg_session_per_player.last_30days, {precision: 2}) }}
            </td>
            <td class="p-2">
              {{ millify(numbers.avg_session_per_player.all_time, {precision: 2}) }}
            </td>
          </tr>

          <tr>
            <td class="p-2 flex">
              <ClockIcon
                class="w-5 text-amber-500 mr-1"
              />
              {{ __("Avg Session Playtime") }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.avg_session_playtime.last_24h, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.avg_session_playtime.last_7days, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.avg_session_playtime.last_30days, true) }}
            </td>
            <td class="p-2">
              {{ secondsToHMS(numbers.avg_session_playtime.all_time, true) }}
            </td>
          </tr>

          <tr>
            <td class="p-2 flex">
              <FireIcon
                class="w-5 text-pink-500 mr-1"
              />
              {{ __("Total Player Kills") }}
            </td>
            <td class="p-2">
              {{ numbers.total_player_kills.last_24h }}
            </td>
            <td class="p-2">
              {{ numbers.total_player_kills.last_7days }}
            </td>
            <td class="p-2">
              {{ numbers.total_player_kills.last_30days }}
            </td>
            <td class="p-2">
              {{ numbers.total_player_kills.all_time }}
            </td>
          </tr>

          <tr>
            <td class="p-2 flex">
              <FireIcon
                class="w-5 text-green-500 mr-1"
              />
              {{ __("Total Mob Kills") }}
            </td>
            <td class="p-2">
              {{ numbers.total_mob_kills.last_24h }}
            </td>
            <td class="p-2">
              {{ numbers.total_mob_kills.last_7days }}
            </td>
            <td class="p-2">
              {{ numbers.total_mob_kills.last_30days }}
            </td>
            <td class="p-2">
              {{ numbers.total_mob_kills.all_time }}
            </td>
          </tr>

          <tr>
            <td class="p-2 flex">
              <icon
                name="skull-outline"
                class="w-5 text-red-500 mr-1"
              />
              {{ __("Total Deaths") }}
            </td>
            <td class="p-2">
              {{ numbers.total_deaths.last_24h }}
            </td>
            <td class="p-2">
              {{ numbers.total_deaths.last_7days }}
            </td>
            <td class="p-2">
              {{ numbers.total_deaths.last_30days }}
            </td>
            <td class="p-2">
              {{ numbers.total_deaths.all_time }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
