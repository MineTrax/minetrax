<template>
  <div v-if="enabled">
    <div class="p-3 sm:px-5 bg-white dark:bg-surface-800 rounded shadow break-words">
      <h3 class="font-extrabold text-secondary-800 dark:text-secondary-200">
        {{ __("Server Status") }}
      </h3>

      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="flex p-4 justify-center"
      >
        <svg
          class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary-600 dark:text-primary-400"
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

      <!-- Error -->
      <error-message v-if="error">
        {{ error }}
      </error-message>

      <div
        v-if="!loading && !error"
        class="flex flex-col"
      >
        <div class="mt-3 text-secondary-700 dark:text-secondary-300 text-center font-semibold">
          {{ __("Join") }} <span class="font-bold text-primary-500 dark:text-primary-400">{{ serverInfo.players.online }}</span> {{ __("Online Players") }}!
        </div>

        <copy-to-clipboard v-slot="props">
          <button
            v-tippy
            :title="__('Click to Copy')"
            type="button"
            class="text-center font-extrabold mt-3 border border-secondary-200 dark:border-secondary-700 text-secondary-600 dark:text-secondary-300 rounded p-2 hover:text-primary-500 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-surface-900 hover:border-primary-500 dark:hover:border-secondary-800 focus:ring focus:ring-primary-200 focus:ring-opacity-50 transition duration-150 ease-in-out focus:outline-none"
            @click="props.copy(server ? server.hostname : $page.props.defaultQueryServer?.server?.hostname)"
          >
            <span v-if="props.status !== 'copied'">
              {{ server ? server.hostname : $page.props.defaultQueryServer?.server?.hostname }}
            </span>
            <span v-else>
              {{ __("Copied!") }}
            </span>
          </button>
        </copy-to-clipboard>
      </div>
    </div>
  </div>
</template>

<script>

import CopyToClipboard from '@/Components/CopyToClipboard.vue';
import ErrorMessage from '@/Components/ErrorMessage.vue';

export default {
    components: {ErrorMessage, CopyToClipboard},

    props: {
        server: Object,
    },

    data() {
        return {
            serverInfo: Object,
            loading: true,
            error: null,
            interval: null
        };
    },

    computed: {
        enabled() {
            if (!this.$page.props.generalSettings.enable_mcserver_statuspingbox) return false;

            return !!(this.server || this.$page.props.defaultQueryServer.server);
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
                serverToQuery = this.$page.props.defaultQueryServer.server;
            }
            axios.get(route('server.ping.get', serverToQuery.id)).then(data => {
                this.serverInfo = data.data;
                this.error = null;
            }).catch(err => {
                this.error = err.response.data.message || err.message;
                this.serverInfo = null;
            }).finally(() => {
                this.loading = false;
            });
        }
    }
};
</script>
