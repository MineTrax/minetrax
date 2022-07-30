<template>
  <div v-if="enabled">
    <div class="p-3 sm:px-5 bg-white dark:bg-gray-800 rounded shadow">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
        {{ __("Online Players") }}
        <span
          v-if="!loading && !error"
          class="float-right text-green-500 font-semibold"
        >
          {{ serverInfo['Players'] }} / {{ serverInfo['MaxPlayers'] }}
        </span>
      </h3>

      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="flex p-4 justify-center"
      >
        <svg
          class="animate-spin -ml-1 mr-3 h-5 w-5 text-light-blue-600"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
      </div>

      <error-message v-if="error">
        {{ error }}
      </error-message>

      <div
        v-if="!loading && !error"
        class="mt-3 text-gray-500 flex flex-wrap justify-center"
      >
        <div
          v-for="(uuid, username) of playersList"
          :key="uuid"
          class="flex-shrink-0 mr-1 mb-1"
          :class="sizeClass"
        >
          <img
            v-tippy
            :title="username"
            :src="route('player.avatar.get', uuid, {size: 25})"
            :alt="username"
            class="focus:outline-none"
            :class="sizeClass"
          >
        </div>
      </div>

      <div
        v-if="!error && (!playersList || playersList.length <= 0)"
        class="italic p-1 rounded text-center text-gray-400"
      >
        {{ __("No players online.") }}
      </div>
    </div>
  </div>
</template>

<script>

import ErrorMessage from '@/Components/ErrorMessage.vue';

export default {
    components: {ErrorMessage},
    props: {
        server: Object,
    },

    data() {
        return {
            serverInfo: Object,
            playersList: Object,
            loading: true,
            error: null,
            sizeClass: 'w-5 h-5',
            interval: null
        };
    },

    computed: {
        enabled() {
            if (!this.$page.props.generalSettings.enable_mcserver_onlineplayersbox) return false;

            return !!(this.server || this.$page.props.defaultQueryServer);
        }
    },

    created() {
        if (!this.enabled) return;
        this.getServerQuery();
        this.interval = setInterval(() => this.getServerQuery(), 10000);
    },

    unmounted() {
        clearInterval(this.interval);
    },

    methods: {
        getServerQuery() {
            let serverToQuery = this.server;
            if (!serverToQuery) {
                serverToQuery = this.$page.props.defaultQueryServer;
            }

            axios.get(route('server.query.get', serverToQuery.id)).then(data => {
                this.serverInfo = data.data.server_info;
                this.playersList = data.data.players_list;

                for (let pl in this.playersList) {
                    if (!this.playersList[pl]) {
                        this.playersList[pl] = '00000000-0000-0000-0000-000000000000';
                    }
                }

                this.error = null;

                // Change avatar size according to number of people
                if (Object.keys(this.playersList).length <= 5) {
                    this.sizeClass = 'w-8 h-8';
                }

            }).catch(err => {
                this.error = err.response.data.message || err.message;
                this.serverInfo = null;
                this.playersList = null;
            }).finally(() => {
                this.loading = false;
            });
        }
    }
};
</script>
