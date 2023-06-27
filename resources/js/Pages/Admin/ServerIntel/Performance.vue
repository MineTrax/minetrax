<script setup>
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import ServerPerformanceOverTimeMetricBox from '@/Shared/ServerPerformanceOverTimeMetricBox.vue';
import ServerIntelPerformanceNumbersBox from '@/Shared/ServerIntelPerformanceNumbersBox.vue';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { pickBy } from 'lodash';

const props = defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
});

let selectedServers = ref(
    props.filters?.servers?.length ? props.filters?.servers[0] : null
);

const showing = computed(() => {
    if (props.filters.servers && props.filters.servers.length > 0) {
        return props.filters.servers
            .map((id) => {
                return props.serverList[id];
            })
            .join(', ');
    }
    return null;
});

watch(selectedServers, (newSelectedServers) => {
    const query = {
        servers: newSelectedServers ? [newSelectedServers] : null,
    };

    router.get(route('admin.intel.server.performance'), pickBy(query));
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Performance - ServerIntel')" />

    <div class="p-4 mx-auto space-y-4 max-w-7xl">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-extrabold text-gray-800 dark:text-gray-200">
          {{ __("Server Performance") }}:
          {{ showing ?? __("All Servers") }}
        </h3>

        <x-select
          id="select_servers"
          v-model="selectedServers"
          name="select_servers"
          :select-list="props.serverList"
          :placeholder="__('All Servers')"
          class="w-48 dark:border dark:rounded dark:border-gray-700"
        />
      </div>

      <ServerPerformanceOverTimeMetricBox :servers="filters?.servers" />

      <ServerIntelPerformanceNumbersBox :servers="filters?.servers" />
    </div>
  </AdminLayout>
</template>
