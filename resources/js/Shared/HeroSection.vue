<template>
  <div
    v-if="settings.enable_home_hero_section"
    class="flex justify-center items-center"
    :style="`
                background: url('${bgImageUrl}');
                background-size: ${settings.home_hero_bg_size_css};
                background-repeat: ${settings.home_hero_bg_repeat_css};
                background-position: ${settings.home_hero_bg_position_css};
                background-attachment: ${settings.home_hero_bg_attachment_css};
                height: ${settings.home_hero_bg_height_css};
              `"
  >
    <copy-to-clipboard
      v-if="joinBoxEnabled"
      v-slot="props"
    >
      <button
        v-tippy
        type="button"
        :title="__('Click to Copy')"
        class="bg-gray-800 bg-opacity-75 items-center px-5 py-3 rounded mx-3"
        @click="props.copy(server ? server.hostname : $page.props.defaultQueryServer.hostname)"
      >
        <loading-spinner :loading="loading" />
        <div v-if="!loading && !error">
          <span
            v-if="props.status !== 'copied'"
            class="text-gray-100 text-xl font-bold"
          >
            {{ __("Join") }} <span class="text-light-blue-500 dark:text-light-blue-400 font-extrabold">{{ serverInfo.players.online }}</span> {{ __("players on") }} {{ server ? server.hostname : $page.props.defaultQueryServer.hostname }}
          </span>
          <span
            v-else
            class="text-gray-100 text-xl font-bold"
          >
            {{ __("Copied!") }}
          </span>
        </div>

        <div v-if="error">
          <span class="text-red-400 font-bold">
            {{ error }}
          </span>
        </div>
      </button>
    </copy-to-clipboard>
  </div>
</template>

<script>
import CopyToClipboard from '@/Components/CopyToClipboard.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';

export default {
    components: {LoadingSpinner, CopyToClipboard},
    props: {
        settings: Object,
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
        bgImageUrl() {
            return window.colorMode === 'dark' ? this.settings.home_hero_bg_image_path_dark : this.settings.home_hero_bg_image_path_light;
        },
        joinBoxEnabled() {
            if (!this.settings.show_join_box_in_home_hero) return false;

            return !!(this.server || this.$page.props.defaultQueryServer);
        }
    },
    created() {
        if (this.joinBoxEnabled) {
            this.getServerPing();
            this.interval = setInterval(() => this.getServerPing(), 10000);
        }
    },
    unmounted() {
        if(this.joinBoxEnabled) {
            clearInterval(this.interval);
        }
    },
    methods: {
        getServerPing() {
            let serverToPing = this.server;
            if (!serverToPing) {
                serverToPing = this.$page.props.defaultQueryServer;
            }
            axios.get(route('server.ping.get', serverToPing.id)).then(data => {
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

<style scoped>

</style>
