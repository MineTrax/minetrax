<template>
  <Card v-if="enabled">
    <CardContent class="p-3 sm:px-5">
      <h3 class="font-extrabold text-foreground dark:text-foreground">
        {{ __("Online Players") }}
        <span
          v-if="!loading && !error"
          class="float-right text-primary font-semibold"
        >
          <span v-if="serverInfo['MaxPlayers']">
            {{ serverInfo["Players"] }} /
            {{ serverInfo["MaxPlayers"] }}
          </span>
          <span v-else>
            {{ serverInfo["Players"] }} {{ __("online") }}
          </span>
        </span>
      </h3>

      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="flex p-4 justify-center"
      >
        <LoadingSpinner :loading="loading" class="w-5 h-5" />
      </div>

      <error-message v-if="error">
        {{ error }}
      </error-message>

      <div
        v-if="!loading && !error"
        class="mt-3 text-foreground flex flex-wrap justify-center"
      >
        <div
          v-for="pl of playersList"
          :key="pl.uuid"
          class="flex-shrink-0 mr-1 mb-1"
          :class="sizeClass"
        >
          <img
            v-tippy
            :title="pl.username"
            :src="route('player.avatar.get', {
              uuid: pl.uuid,
              username: pl.username,
              textureid: pl.skin_texture_id,
              size: 50,
            })
            "
            :alt="pl.username"
            class="focus:outline-none"
            :class="sizeClass"
          >
        </div>
      </div>

      <div
        v-if="!error &&
          !loading &&
          (!playersList || playersList.length <= 0)
        "
        class="italic p-1 rounded text-center text-foreground"
      >
        {{ __("No players online.") }}
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import ErrorMessage from '@/Components/ErrorMessage.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import {
  Card,
  CardContent,
} from '@/Components/ui/card'
import LoadingSpinner from '@/Components/LoadingSpinner.vue';

const props = defineProps({
    server: {
        type: Object,
    },
});

let serverInfo = ref({});
let playersList = ref([]);
let loading = ref(true);
let error = ref(null);
let sizeClass = ref('w-5 h-5');
let interval = null;

function getServerQuery() {
    let shouldUseWebQuery = false;
    let serverToQuery = props.server;
    if (!serverToQuery) {
        serverToQuery = usePage().props.defaultQueryServer.server;
        shouldUseWebQuery =
            usePage().props.defaultQueryServer.shouldUseWebQuery;
    }

    if (shouldUseWebQuery) {
        tryFetchUsingWebQuery(serverToQuery);
    } else {
        tryFetchUsingQuery(serverToQuery);
    }
}

function tryFetchUsingQuery(serverToQuery) {
    axios
        .get(route('server.query.get', serverToQuery.id))
        .then((data) => {
            serverInfo.value = data.data.server_info;
            playersList.value = [];
            for (let pl in data.data.players_list) {
                const player = {
                    username: pl,
                    uuid:
                        data.data.players_list[pl] ||
                        '00000000-0000-0000-0000-000000000000',
                    skin_texture_id: null,
                };
                playersList.value.push(player);
            }

            error.value = null;

            // Change avatar size according to number of people
            if (playersList.value.length <= 5) {
                sizeClass.value = 'w-8 h-8';
            }
        })
        .catch((err) => {
            error.value = err.response.data.message || err.message;
            serverInfo.value = null;
            playersList.value = null;
        })
        .finally(() => {
            loading.value = false;
        });
}

function tryFetchUsingWebQuery(serverToQuery) {
    axios
        .get(route('server.webquery.status', serverToQuery.id))
        .then((data) => {
            if (data.data.players.length > 0) {
                playersList.value = data.data.players.map((player) => {
                    return {
                        uuid: player.id,
                        username: player.username,
                        skin_texture_id: player.skin_texture_id,
                    };
                });
            } else {
                playersList.value = [];
            }

            serverInfo.value = {
                Players: data.data.online_players,
                MaxPlayers: data.data.max_players,
            };

            error.value = null;

            // Change avatar size according to number of people
            if (playersList.value.length <= 5) {
                sizeClass.value = 'w-8 h-8';
            }
        })
        .catch((err) => {
            error.value = err.response.data.message || err.message;
            serverInfo.value = null;
            playersList.value = null;
        })
        .finally(() => {
            loading.value = false;
        });
}

let enabled = computed(() => {
    if (!usePage().props.generalSettings.enable_mcserver_onlineplayersbox) {
        return false;
    }
    return !!(props.server || usePage().props.defaultQueryServer.server);
});

onMounted(() => {
    if (!enabled.value) return;
    getServerQuery();
    interval = setInterval(() => getServerQuery(), 10000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>
