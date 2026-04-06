<script setup>
import XSelect from "@/Components/Form/XSelect.vue";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { pickBy } from "lodash";

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

watch(selectedServers, (newSelectedServers) => {
    const query = {
        servers: newSelectedServers ? [newSelectedServers] : null,
    };

    router.get(route(route().current()), pickBy(query));
});
</script>

<template>
  <div class="flex items-center justify-between">
    <x-select
      id="select_servers"
      v-model="selectedServers"
      name="select_servers"
      :select-list="props.serverList"
      :placeholder="__('All Servers')"
      class="w-48 max-w-48 border rounded bg-card"
    />
  </div>
</template>
