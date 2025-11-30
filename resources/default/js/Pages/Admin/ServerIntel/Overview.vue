<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import ServerOnlineActivityOverTimeMetricBox from '@/Shared/ServerOnlineActivityOverTimeMetricBox.vue';
import Icon from '@/Components/Icon.vue';
import { PowerIcon, ServerStackIcon } from '@heroicons/vue/24/outline';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
const { secondsToHMS } = useHelpers();
const { __ } = useTranslations();
import millify from 'millify';
import ServerIntelOverviewNumbersBox from '@/Shared/ServerIntelOverviewNumbersBox.vue';
import AlertCard from '@/Components/AlertCard.vue';
import { Card, CardContent } from '@/Components/ui/card';

const props = defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    last7DaysStats: {
        type: Object,
    },
    noIntelForOverWeek: {
        type: Boolean
    }
});


let selectedServers = props.filters?.servers?.length ? props.filters?.servers[0] : null;
const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Server Intel'),
        current: false,
    },
    {
        text: __('Overview'),
        current: true,
    },
    {
        text: props.serverList[selectedServers] ?? __('All Servers'),
        current: true,
    }
];
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Overview - ServerIntel')" />

    <div class="px-10 py-8 mx-auto space-y-4">
      <div class="flex justify-between items-center">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
        <ServerIntelServerSelector
          :server-list="serverList"
          :filters="filters"
        />
      </div>

      <AlertCard
        v-if="noIntelForOverWeek"
        variant="warning"
      >
        {{ __("Server haven't sent Intel data for over 7 days.") }}
      </AlertCard>

      <div id="row1">
        <ServerOnlineActivityOverTimeMetricBox :servers="filters?.servers" />
      </div>

      <div
        id="row2"
        class="flex justify-between flex-1 space-x-4"
      >
        <ServerIntelOverviewNumbersBox :servers="filters?.servers" />

        <Card class="w-full basis-2/6">
          <CardContent class="p-4">
            <h3 class="font-extrabold text-foreground flex items-center">
              <ServerStackIcon class="w-6 mr-2" />
              {{ __("Last 7 Days") }}
            </h3>

            <div class="flex flex-col text-sm mt-5">
              <table class="table-auto min-w-full text-foreground">
                <tbody>
                  <tr>
                    <td class="py-2 flex">
                      <Icon
                        name="users"
                        class="w-5 text-primary mr-2"
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
                        class="w-5 text-lime-500 mr-2"
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
                        class="w-5 text-muted-foreground mr-2"
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
                        name="users"
                        class="w-5 text-success-500 mr-2"
                      />
                      {{ __("Peak Online Players") }}
                    </td>
                    <td class="p-2 text-right">
                      {{ last7DaysStats.peekOnlinePlayersCount ?? __('none') }}
                    </td>
                  </tr>

                  <tr>
                    <td class="py-2 flex">
                      <Icon
                        name="joystick"
                        class="w-5 text-lime-500 mr-2"
                      />
                      {{ __("Avg TPS") }}
                    </td>
                    <td class="p-2 text-right">
                      {{ last7DaysStats.averageTps ? millify(last7DaysStats.averageTps, {precision: 2}) : __('none') }}
                    </td>
                  </tr>

                  <tr>
                    <td class="py-2 flex">
                      <Icon
                        name="joystick"
                        class="w-5 text-destructive mr-2"
                      />
                      {{ __("Lowest TPS") }}
                    </td>
                    <td class="p-2 text-right">
                      {{ last7DaysStats.lowestTps ? millify(last7DaysStats.lowestTps, {precision: 2}) : __('none') }}
                    </td>
                  </tr>

                  <tr>
                    <td class="py-2 flex">
                      <Icon
                        name="cpu"
                        class="w-5 text-primary mr-2"
                      />
                      {{ __("Avg CPU Load") }}
                    </td>
                    <td class="p-2 text-right">
                      {{ last7DaysStats.averageCpuLoad ? millify(last7DaysStats.averageCpuLoad, {precision: 2}) : __('none') }}
                    </td>
                  </tr>

                  <tr>
                    <td class="py-2 flex">
                      <PowerIcon class="w-5 text-success-500 mr-2" />
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
                        class="w-5 text-destructive mr-2"
                      />
                      {{ __("Restarts") }}
                    </td>
                    <td class="p-2 pb-4 text-right">
                      {{ last7DaysStats.noOfRestarts ?? __('none') }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>
