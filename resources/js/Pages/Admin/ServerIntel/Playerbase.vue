<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import PlayersPerCountryMetricBox from '@/Shared/PlayersPerCountryMetricBox.vue';
import {computed} from 'vue';

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

    <div class="p-4 mx-auto space-y-4 max-w-7xl">
      <ServerIntelServerSelector
        :title="__('Playerbase')"
        :server-list="serverList"
        :filters="filters"
      />

      <div id="row1">
        <PlayersPerCountryMetricBox
          map-height="500px"
          :route="countryMapRoute"
        />
      </div>

      <div
        class="p-3 space-y-8 bg-white rounded shadow dark:bg-cool-gray-800"
      >
        <h3 class="animate-pulse text-3xl text-center p-5 m-10 text-light-blue-500 font-extrabold">
          {{ __("Coming Soon!") }}
        </h3>
      </div>
    </div>
  </AdminLayout>
</template>

