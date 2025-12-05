<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import PlayersPerCountryMetricBox from '@/Shared/PlayersPerCountryMetricBox.vue';
import { computed } from 'vue';
import PlayersJoinAddressMetricBox from '@/Shared/PlayersJoinAddressMetricBox.vue';
import PlayersMinecraftVersionMetricBox from '@/Shared/PlayersMinecraftVersionMetricBox.vue';
import PlayersJoinAddressOverTimeMetricBox from '@/Shared/PlayersJoinAddressOverTimeMetricBox.vue';
import PlayersMinecraftVersionOverTimeMetricBox from '@/Shared/PlayersMinecraftVersionOverTimeMetricBox.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

const props = defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
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
        text: __('Playerbase'),
        current: true,
    },
    {
        text: props.serverList[selectedServers] ?? __('All Servers'),
        current: true,
    }
];

const countryMapRoute = computed(() => {
    return route('admin.intel.server.playerbase.countries', {
        servers: props.filters?.servers,
    });
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Playerbase - ServerIntel')" />

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
