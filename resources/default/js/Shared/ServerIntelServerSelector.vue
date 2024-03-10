<script setup>
import XSelect from '@/Components/Form/XSelect.vue';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { pickBy } from 'lodash';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    serverList: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
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

    router.get(route(route().current()), pickBy(query));
});
</script>

<template>
  <div class="flex items-center justify-between">
    <h3 class="text-xl font-extrabold text-gray-800 dark:text-gray-200">
      {{ title }}:
      {{ showing ?? __("All Servers") }}
    </h3>

    <x-select
      id="select_servers"
      v-model="selectedServers"
      name="select_servers"
      :select-list="props.serverList"
      :placeholder="__('All Servers')"
      class="w-48 max-w-48 dark:border dark:rounded dark:border-gray-700"
    />
  </div>
</template>
