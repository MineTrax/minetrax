<template>
  <Card v-if="enabled">
    <CardContent class="p-4 sm:px-5 warp-break-words">
      <div class="flex items-center justify-between">
        <h3 class="font-extrabold text-card-foreground">
          {{ __("Server Status") }}
        </h3>
        <span
          v-if="!loading && !error"
          class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-500"
        >
          <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500" />
          </span>
          {{ __("Online") }}
        </span>
        <span
          v-if="!loading && error"
          class="inline-flex items-center gap-1.5 rounded-full bg-destructive/10 px-2.5 py-0.5 text-xs font-semibold text-destructive"
        >
          <span class="h-2 w-2 rounded-full bg-destructive" />
          {{ __("Offline") }}
        </span>
      </div>

      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="flex p-6 justify-center"
      >
        <LoadingSpinner
          :loading="loading"
          class="w-5 h-5"
        />
      </div>

      <!-- Error -->
      <error-message v-if="error">
        {{ error }}
      </error-message>

      <div
        v-if="!loading && !error"
        class="mt-4 flex flex-col gap-4"
      >
        <!-- Player count -->
        <div class="flex flex-col gap-2">
          <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-extrabold text-primary tabular-nums">
              {{ serverInfo.players.online }}
            </span>
            <span class="text-sm text-muted-foreground">
              {{ __("players online — join them!") }}
            </span>
          </div>
        </div>

        <!-- Copy server address -->
        <copy-to-clipboard v-slot="slotProps">
          <Button
            v-tippy
            class="w-full font-bold gap-2"
            size="lg"
            :title="__('Click to Copy')"
            type="button"
            @click="slotProps.copy(hostname)"
          >
            <template v-if="slotProps.status !== 'copied'">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="h-4 w-4 shrink-0 opacity-70"
              >
                <path
                  fill-rule="evenodd"
                  d="M13.887 3.182c.396.037.79.08 1.183.128C16.194 3.45 17 4.414 17 5.517V16.75A2.25 2.25 0 0 1 14.75 19h-9.5A2.25 2.25 0 0 1 3 16.75V5.517c0-1.103.806-2.068 1.93-2.207.393-.048.787-.09 1.183-.128A3.001 3.001 0 0 1 9 1h2c1.373 0 2.531.923 2.887 2.182ZM7.5 4A1.5 1.5 0 0 1 9 2.5h2A1.5 1.5 0 0 1 12.5 4v.5h-5V4Z"
                  clip-rule="evenodd"
                />
              </svg>
              {{ hostname }}
            </template>
            <template v-else>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="h-4 w-4 shrink-0"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                  clip-rule="evenodd"
                />
              </svg>
              {{ __("Copied!") }}
            </template>
          </Button>
        </copy-to-clipboard>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import CopyToClipboard from "@/Components/CopyToClipboard.vue";
import ErrorMessage from "@/Components/ErrorMessage.vue";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import Button from "@/Components/ui/button/Button.vue";
import {
    Card,
    CardContent,
} from "@/Components/ui/card";
import { usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
    server: {
        type: Object,
    },
});

const serverInfo = ref(null);
const loading = ref(true);
const error = ref(null);
let interval = null;

const enabled = computed(() => {
    if (!usePage().props.generalSettings.enable_mcserver_statuspingbox) {
        return false;
    }
    return !!(props.server || usePage().props.defaultQueryServer.server);
});

const hostname = computed(() => {
    return props.server ? props.server.hostname : usePage().props.defaultQueryServer?.server?.hostname;
});

function getServerQuery() {
    let serverToQuery = props.server;
    if (!serverToQuery) {
        serverToQuery = usePage().props.defaultQueryServer.server;
    }
    axios.get(route("server.ping.get", serverToQuery.id)).then(data => {
        serverInfo.value = data.data;
        error.value = null;
    }).catch(err => {
        error.value = err.response?.data?.message || err.message;
        serverInfo.value = null;
    }).finally(() => {
        loading.value = false;
    });
}

onMounted(() => {
    if (!enabled.value) {
        return;
    }
    getServerQuery();
    interval = setInterval(() => getServerQuery(), 10000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>
