<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import PlayersPerCountryMetricBox from '@/Shared/PlayersPerCountryMetricBox.vue';
import {computed} from 'vue';
import PlayersJoinAddressMetricBox from '@/Shared/PlayersJoinAddressMetricBox.vue';
import PlayersMinecraftVersionMetricBox from '@/Shared/PlayersMinecraftVersionMetricBox.vue';
import PlayersJoinAddressOverTimeMetricBox from '@/Shared/PlayersJoinAddressOverTimeMetricBox.vue';
import PlayersMinecraftVersionOverTimeMetricBox from '@/Shared/PlayersMinecraftVersionOverTimeMetricBox.vue';

const props = defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
});

const countryMapRoute = computed(() => {
    return route('admin.intel.server.playerbase.countries', {
        servers: props.filters?.servers,
    });
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Playerbase - ServerIntel')" />

    <div class="p-4 mx-auto space-y-4 px-10">
      <ServerIntelServerSelector
        :title="__('Playerbase')"
        :server-list="serverList"
        :filters="filters"
      />

      <div id="row1">
        <PlayersPerCountryMetricBox
          map-height="500px"
          :route-name="countryMapRoute"
        />
      </div>

      <div
        id="row2"
        class="flex justify-between flex-1 space-x-4"
      >
        <PlayersJoinAddressOverTimeMetricBox
          chart-height="387px"
          class="basis-2/3"
          :servers="filters.servers"
        />

        <PlayersJoinAddressMetricBox
          class="basis-1/3"
          chart-height="400px"
          :servers="filters.servers"
        />
      </div>

      <div
        id="row3"
        class="flex justify-between flex-1 space-x-4"
      >
        <PlayersMinecraftVersionOverTimeMetricBox
          class="basis-2/3"
          chart-height="387px"
          :servers="filters.servers"
        />

        <PlayersMinecraftVersionMetricBox
          class="basis-1/3"
          chart-height="400px"
          :servers="filters.servers"
        />
      </div>
    </div>
  </AdminLayout>
</template>
