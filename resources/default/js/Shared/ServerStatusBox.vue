<template>
  <Card v-if="enabled">
    <CardContent class="p-3 sm:px-5 break-words">
      <h3 class="font-extrabold text-card-foreground">
        {{ __("Server Status") }}
      </h3>

      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="flex p-4 justify-center"
      >
        <svg
          class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary"
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
        <div class="mt-3 text-card-foreground text-center font-semibold">
          {{ __("Join") }} <span class="font-bold text-primary">{{ serverInfo.players.online }}</span> {{ __("Online Players") }}!
        </div>

        <copy-to-clipboard v-slot="props">
          <Button
            class="mt-3 font-extrabold"
            size="lg"
            v-tippy
            :title="__('Click to Copy')"
            type="button"
            @click="props.copy(server ? server.hostname : $page.props.defaultQueryServer?.server?.hostname)"
          >
            <span v-if="props.status !== 'copied'">
              {{ server ? server.hostname : $page.props.defaultQueryServer?.server?.hostname }}
            </span>
            <span v-else>
              {{ __("Copied!") }}
            </span>
        </Button>
        </copy-to-clipboard>
      </div>
    </CardContent>
  </Card>
</template>

<script>
import {
  Card,
  CardContent,
} from '@/Components/ui/card'
import CopyToClipboard from '@/Components/CopyToClipboard.vue';
import ErrorMessage from '@/Components/ErrorMessage.vue';
import Button from '@/Components/ui/button/Button.vue';

export default {
    components: {ErrorMessage, CopyToClipboard, Card, CardContent, Button},

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
