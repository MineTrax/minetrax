<template>
  <div v-if="enabled">
    <div class="p-3 sm:px-5 bg-white dark:bg-gray-800 rounded shadow">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
        Online Players
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

      <div
        v-if="error"
        class="bg-red-50 border border-red-400 mt-2 p-1 rounded text-center text-red-400"
      >
        {{ error }}
      </div>

      <div
        v-if="!loading && !error"
        class="mt-3 text-gray-500 flex flex-wrap justify-center"
      >
        <div
          v-for="(uuid, username) of playersList"
          class="flex-shrink-0 mr-1 mb-1"
          :class="sizeClass"
        >
          <img
            v-tippy
            :title="username"
            :src="`https://crafatar.com/avatars/${uuid}?size=50`"
            :alt="username"
            class="focus:outline-none"
            :class="sizeClass"
          >
        </div>
      </div>

      <div
        v-if="!playersList || playersList.length <= 0"
        class="italic p-1 rounded text-center text-gray-400"
      >
        No players online.
      </div>
    </div>
  </div>
</template>

<script>

export default {
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

    destroyed() {
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
                this.error = null;

                // Change avatar size according to number of people
                if (Object.keys(this.playersList).length <= 5) {
                    this.sizeClass = 'w-8 h-8';
                }

            }).catch(err => {
                this.error = err.response.data.message || err.message;
                this.serverInfo = null;
                this.playersList = null;
            }).finally(e => {
                this.loading = false;
            });
        }
    }
};
</script>
