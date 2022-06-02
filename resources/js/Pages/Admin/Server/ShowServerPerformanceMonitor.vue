<template>
  <app-layout>
    <app-head :title="`Server #${server.id}`" />

    <div class="py-12 px-10 max-w-6xl mx-auto space-y-4">
      <div class="flex justify-between">
        <h2 class="text-2xl text-gray-600 dark:text-gray-200">
          <span class="font-bold">#{{ server.id }}</span>
          -
          <span class="font-bold">{{ server.name }}</span>
        </h2>

        <date-picker
          v-model="dateRange"
          type="date"
          range
          placeholder="View graph for range"
          input-class="border-gray-300 p-2 text-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 rounded-md block w-full dark:bg-cool-gray-800 dark:text-gray-300 dark:border-gray-800"
          @change="filter()"
        />
      </div>

      <server-sub-menu :id="server.id" />

      <div class="flex space-x-2">
        <server-cpu-metric-box
          :server-id="server.id"
          :live-info="serverLiveInfo"
        />
        <server-ram-metric-box
          :server-id="server.id"
          :live-info="serverLiveInfo"
        />
      </div>

      <div class="flex">
        <server-all-live-stats-box
          :server-id="server.id"
          :live-info="serverLiveInfo"
        />
      </div>

      <div class="flex">
        <div class="bg-white dark:bg-cool-gray-800 rounded w-full h-full space-y-2 p-3 shadow">
          <h3 class="font-extrabold text-gray-800 dark:text-gray-200 flex items-center">
            <icon
              name="calculator"
              class="w-6 mr-1"
            />
            Numbers
          </h3>

          <div class="">
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
                    Last 24 hours
                  </th>
                  <th
                    scope="col"
                    class="text-left p-2"
                  >
                    Last week
                  </th>
                  <th
                    scope="col"
                    class="text-left p-2"
                  >
                    Last month
                  </th>
                  <th
                    scope="col"
                    class="text-left p-2"
                  >
                    All time
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
                    Max Online Players
                  </td>
                  <td class="p-2">
                    {{ numbers.max_players.last_24h }}
                  </td>
                  <td class="p-2">
                    {{ numbers.max_players.last_week }}
                  </td>
                  <td class="p-2">
                    {{ numbers.max_players.last_month }}
                  </td>
                  <td class="p-2">
                    {{ numbers.max_players.all_time }}
                  </td>
                </tr>

                <tr>
                  <td class="p-2 flex">
                    <icon
                      name="joystick"
                      class="w-5 text-pink-500 mr-1"
                    />
                    Lowest TPS
                  </td>
                  <td class="p-2">
                    {{ numbers.low_tps.last_24h }}
                  </td>
                  <td class="p-2">
                    {{ numbers.low_tps.last_week }}
                  </td>
                  <td class="p-2">
                    {{ numbers.low_tps.last_month }}
                  </td>
                  <td class="p-2">
                    {{ numbers.low_tps.all_time }}
                  </td>
                </tr>

                <tr>
                  <td class="p-2 flex">
                    <icon
                      name="cpu"
                      class="w-5 text-blue-500 mr-1"
                    />
                    Avg CPU Usage
                  </td>
                  <td class="p-2">
                    {{ numbers.avg_cpu.last_24h.toFixed(2) }}%
                  </td>
                  <td class="p-2">
                    {{ numbers.avg_cpu.last_week.toFixed(2) }}%
                  </td>
                  <td class="p-2">
                    {{ numbers.avg_cpu.last_month.toFixed(2) }}%
                  </td>
                  <td class="p-2">
                    {{ numbers.avg_cpu.all_time.toFixed(2) }}%
                  </td>
                </tr>

                <tr>
                  <td class="p-2 flex">
                    <icon
                      name="ram"
                      class="w-5 text-orange-500 mr-1"
                    />
                    Avg RAM Usage
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.avg_memory.last_24h / 1024) }} MB
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.avg_memory.last_week / 1024) }} MB
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.avg_memory.last_month / 1024) }} MB
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.avg_memory.all_time / 1024) }} MB
                  </td>
                </tr>

                <tr>
                  <td class="p-2 flex">
                    <icon
                      name="grid"
                      class="w-5 text-purple-500 mr-1"
                    />
                    Avg Chunks Loaded
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.avg_chunks.last_24h) }}
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.avg_chunks.last_week) }}
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.avg_chunks.last_month) }}
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.avg_chunks.all_time) }}
                  </td>
                </tr>

                <tr>
                  <td class="p-2 flex">
                    <icon
                      name="server"
                      class="w-5 text-light-blue-500 mr-1"
                    />
                    Min Free Disk
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.min_free_disk.last_24h / 1048576) }} GB
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.min_free_disk.last_week / 1048576) }} GB
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.min_free_disk.last_month / 1048576) }} GB
                  </td>
                  <td class="p-2">
                    {{ Math.round(numbers.min_free_disk.all_time / 1048576) }} GB
                  </td>
                </tr>

                <tr>
                  <td class="p-2 flex">
                    <icon
                      name="toggle-off"
                      class="w-5 text-red-500 mr-1"
                    />
                    Total Restarts
                  </td>
                  <td class="p-2">
                    {{ numbers.total_restarts.last_24h }}
                  </td>
                  <td class="p-2">
                    {{ numbers.total_restarts.last_week }}
                  </td>
                  <td class="p-2">
                    {{ numbers.total_restarts.last_month }}
                  </td>
                  <td class="p-2">
                    {{ numbers.total_restarts.all_time }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import {formatDistanceToNowStrict, format} from 'date-fns';
import ServerSubMenu from '@/Pages/Admin/Server/ServerSubMenu';
import ServerCpuMetricBox from '@/Shared/ServerCpuMetricBox';
import ServerRamMetricBox from '@/Shared/ServerRamMetricBox';
import ServerAllLiveStatsBox from '@/Shared/ServerAllLiveStatsBox';
import DatePicker from 'vue2-datepicker';
import {pickBy} from 'lodash';
import Icon from '@/Components/Icon';

export default {

    components: {
        ServerAllLiveStatsBox,
        ServerRamMetricBox,
        ServerCpuMetricBox,
        ServerSubMenu,
        AppLayout,
        DatePicker,
        Icon,
    },
    props: {
        server: Object,
        serverLiveInfo: Array,
        queryParams: Object,
        numbers: Object,
    },

    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict,
            format: format,
            dateRange: [
                this.queryParams.dateFrom ? new Date(this.queryParams.dateFrom) : null,
                this.queryParams.dateTo ?  new Date(this.queryParams.dateTo) : null
            ],
            query: {
                dateFrom: this.queryParams.dateFrom,
                dateTo: this.queryParams.dateTo
            }
        };
    },

    methods: {
        filter() {
            this.query = {
                dateFrom: this.dateRange[0],
                dateTo: this.dateRange[1],
            };
            this.$inertia.get(route('admin.server.show.perfmon', this.server.id), pickBy(this.query));
        }
    }
};
</script>
