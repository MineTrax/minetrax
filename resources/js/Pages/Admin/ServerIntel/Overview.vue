<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import ServerOnlineActivityOverTimeMetricBox from '@/Shared/ServerOnlineActivityOverTimeMetricBox.vue';
import Icon from '@/Components/Icon.vue';
import { PowerIcon } from '@heroicons/vue/24/outline';
import {useHelpers} from '@/Composables/useHelpers';
const { secondsToHMS } = useHelpers();

defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    last7DaysStats: {
        type: Object,
    },
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Overview - ServerIntel')" />

    <div class="p-4 mx-auto space-y-4 max-w-7xl">
      <ServerIntelServerSelector
        :title="__('Server Overview')"
        :server-list="serverList"
        :filters="filters"
      />

      <div id="row1">
        <ServerOnlineActivityOverTimeMetricBox :servers="filters?.servers" />
      </div>

      <div
        id="row2"
        class="flex justify-between flex-1 space-x-4"
      >
        <div class="bg-white dark:bg-cool-gray-800 rounded w-full shadow basis-4/6">
          Placeholder Shit for a Chart?
        </div>

        <div class="bg-white dark:bg-cool-gray-800 rounded w-full shadow basis-2/6">
          <h3 class="p-4 font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
            {{ __("Last 7 Days") }}
          </h3>

          <div class="flex flex-col px-3 text-sm">
            <table>
              <tbody>
                <tr>
                  <td class="py-2 flex">
                    <Icon
                      name="users"
                      class="w-5 text-indigo-500 mr-1"
                    />
                    {{ __("Unique Players") }}
                  </td>
                  <td class="p-2 text-right">
                    {{ last7DaysStats.uniquePlayersCount }}
                  </td>
                </tr>

                <tr>
                  <td class="py-2 flex">
                    <Icon
                      name="users"
                      class="w-5 text-green-500 mr-1"
                    />
                    {{ __("New Players") }}
                  </td>
                  <td class="p-2 text-right">
                    {{ last7DaysStats.newPlayersCount }}
                  </td>
                </tr>

                <tr>
                  <td class="py-2 flex">
                    <Icon
                      name="users"
                      class="w-5 text-gray-500 mr-1"
                    />
                    {{ __("Old Players") }}
                  </td>
                  <td class="p-2 text-right">
                    {{ last7DaysStats.oldPlayersCount }}
                  </td>
                </tr>

                <tr>
                  <td class="py-2 flex">
                    <Icon
                      name="joystick"
                      class="w-5 text-pink-500 mr-1"
                    />
                    {{ __("Avg TPS") }}
                  </td>
                  <td class="p-2 text-right">
                    {{ last7DaysStats.averageTps ?? __('none') }}
                  </td>
                </tr>

                <tr>
                  <td class="py-2 flex">
                    <Icon
                      name="cpu"
                      class="w-5 text-blue-500 mr-1"
                    />
                    {{ __("Avg CPU Load") }}
                  </td>
                  <td class="p-2 text-right">
                    {{ last7DaysStats.averageCpuLoad ?? __('none') }}
                  </td>
                </tr>

                <tr>
                  <td class="py-2 flex">
                    <PowerIcon
                      class="w-5 text-green-500 mr-1"
                    />
                    {{ __("Longest Uptime") }}
                  </td>
                  <td class="p-2 text-right">
                    {{ last7DaysStats.longestUptime ? secondsToHMS(last7DaysStats.longestUptime) : __('none') }}
                  </td>
                </tr>

                <tr>
                  <td class="py-2 flex">
                    <Icon
                      name="toggle-off"
                      class="w-5 text-red-500 mr-1"
                    />
                    {{ __("Restarts") }}
                  </td>
                  <td class="p-2 pb-5 text-right">
                    {{ last7DaysStats.noOfRestarts ?? __('none') }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

