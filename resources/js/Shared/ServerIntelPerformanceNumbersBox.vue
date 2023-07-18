<script setup>
import { onMounted, ref } from 'vue';
import Icon from '@/Components/Icon.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import {useHelpers} from '@/Composables/useHelpers';
const { secondsToHMS } = useHelpers();

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
        route('admin.intel.server.performance.numbers', params)
    );

    isLoading.value = false;
    numbers.value = response.data.numbers;
}

onMounted(async () => {
    await fetchData();
});

</script>

<template>
  <div class="flex">
    <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full p-3 shadow">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex mt-2 items-center">
        <Icon
          name="calculator"
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
                {{ __("Last 3 months") }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="p-2 flex">
                <icon
                  name="users"
                  class="w-5 text-green-500 mr-1"
                />
                {{ __("Peak Online Players") }}
              </td>
              <td class="p-2">
                {{ numbers.max_players.last_24h }}
              </td>
              <td class="p-2">
                {{ numbers.max_players.last_7days }}
              </td>
              <td class="p-2">
                {{ numbers.max_players.last_30days }}
              </td>
              <td class="p-2">
                {{ numbers.max_players.last_90days }}
              </td>
            </tr>

            <tr>
              <td class="p-2 flex">
                <icon
                  name="finger-print"
                  class="w-5 text-indigo-500 mr-1"
                />
                {{ __("Avg Player Session Length") }}
              </td>
              <td class="p-2">
                {{ secondsToHMS(numbers.player_avg_session_length.last_24h, true) }}
              </td>
              <td class="p-2">
                {{ secondsToHMS(numbers.player_avg_session_length.last_7days, true) }}
              </td>
              <td class="p-2">
                {{ secondsToHMS(numbers.player_avg_session_length.last_30days, true) }}
              </td>
              <td class="p-2">
                {{ secondsToHMS(numbers.player_avg_session_length.last_90days, true) }}
              </td>
            </tr>

            <tr>
              <td class="p-2 flex">
                <icon
                  name="moon-outline"
                  class="w-5 text-gray-500 mr-1"
                />
                {{ __("Avg Player AFK Time") }}
              </td>
              <td class="p-2">
                {{ secondsToHMS(numbers.player_avg_afk_time.last_24h, true) }}
              </td>
              <td class="p-2">
                {{ secondsToHMS(numbers.player_avg_afk_time.last_7days, true) }}
              </td>
              <td class="p-2">
                {{ secondsToHMS(numbers.player_avg_afk_time.last_30days, true) }}
              </td>
              <td class="p-2">
                {{ secondsToHMS(numbers.player_avg_afk_time.last_90days, true) }}
              </td>
            </tr>

            <tr>
              <td class="p-2 flex">
                <icon
                  name="joystick"
                  class="w-5 text-pink-500 mr-1"
                />
                {{ __("Lowest TPS") }}
              </td>
              <td class="p-2">
                {{ numbers.low_tps.last_24h }}
              </td>
              <td class="p-2">
                {{ numbers.low_tps.last_7days }}
              </td>
              <td class="p-2">
                {{ numbers.low_tps.last_30days }}
              </td>
              <td class="p-2">
                {{ numbers.low_tps.last_90days }}
              </td>
            </tr>

            <tr>
              <td class="p-2 flex">
                <icon
                  name="cpu"
                  class="w-5 text-blue-500 mr-1"
                />
                {{ __("Avg CPU Usage") }}
              </td>
              <td class="p-2">
                {{ numbers.avg_cpu.last_24h.toFixed(2) }}%
              </td>
              <td class="p-2">
                {{ numbers.avg_cpu.last_7days.toFixed(2) }}%
              </td>
              <td class="p-2">
                {{ numbers.avg_cpu.last_30days.toFixed(2) }}%
              </td>
              <td class="p-2">
                {{ numbers.avg_cpu.last_90days.toFixed(2) }}%
              </td>
            </tr>

            <tr>
              <td class="p-2 flex">
                <icon
                  name="ram"
                  class="w-5 text-orange-500 mr-1"
                />
                {{ __("Avg RAM Usage") }}
              </td>
              <td class="p-2">
                {{ Math.round(numbers.avg_memory.last_24h / 1024) }} MB
              </td>
              <td class="p-2">
                {{ Math.round(numbers.avg_memory.last_7days / 1024) }} MB
              </td>
              <td class="p-2">
                {{ Math.round(numbers.avg_memory.last_30days / 1024) }} MB
              </td>
              <td class="p-2">
                {{ Math.round(numbers.avg_memory.last_90days / 1024) }} MB
              </td>
            </tr>

            <tr>
              <td class="p-2 flex">
                <icon
                  name="grid"
                  class="w-5 text-purple-500 mr-1"
                />
                {{ __("Avg Chunks Loaded") }}
              </td>
              <td class="p-2">
                {{ Math.round(numbers.avg_chunks.last_24h) }}
              </td>
              <td class="p-2">
                {{ Math.round(numbers.avg_chunks.last_7days) }}
              </td>
              <td class="p-2">
                {{ Math.round(numbers.avg_chunks.last_30days) }}
              </td>
              <td class="p-2">
                {{ Math.round(numbers.avg_chunks.last_90days) }}
              </td>
            </tr>

            <tr>
              <td class="p-2 flex">
                <icon
                  name="server"
                  class="w-5 text-light-blue-500 mr-1"
                />
                {{ __("Min Free Disk") }}
              </td>
              <td class="p-2">
                {{ Math.round(numbers.min_free_disk.last_24h / 1048576) }} GB
              </td>
              <td class="p-2">
                {{ Math.round(numbers.min_free_disk.last_7days / 1048576) }} GB
              </td>
              <td class="p-2">
                {{ Math.round(numbers.min_free_disk.last_30days / 1048576) }} GB
              </td>
              <td class="p-2">
                {{ Math.round(numbers.min_free_disk.last_90days / 1048576) }} GB
              </td>
            </tr>

            <tr>
              <td class="p-2 flex">
                <icon
                  name="toggle-off"
                  class="w-5 text-red-500 mr-1"
                />
                {{ __("Total Restarts") }}
              </td>
              <td class="p-2">
                {{ numbers.total_restarts.last_24h }}
              </td>
              <td class="p-2">
                {{ numbers.total_restarts.last_7days }}
              </td>
              <td class="p-2">
                {{ numbers.total_restarts.last_30days }}
              </td>
              <td class="p-2">
                {{ numbers.total_restarts.last_90days }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
