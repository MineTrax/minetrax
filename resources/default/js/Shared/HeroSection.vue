<template>
  <div
    v-if="settings.enable_home_hero_section"
    class="relative flex items-center justify-center fade-img-light dark:fade-img-dark"
    :style="`
                background: url('${isBgImageVideo ? '' : bgImageUrl}');
                        background-size: ${settings.home_hero_bg_size_css};
                        background-repeat: ${settings.home_hero_bg_repeat_css};
                        background-position: ${settings.home_hero_bg_position_css};
                        background-attachment: ${settings.home_hero_bg_attachment_css};
                        height: ${settings.home_hero_bg_height_css};
              `"
  >
    <vue-particles
      v-if="settings.home_hero_bg_particles"
      id="tsparticles"
      class="absolute w-full h-full"
      :options="particleOptions"
    />

    <div
      class="flex items-center justify-center w-full h-full"
      :class="{
        'backdrop-brightness-50': settings.show_fg_image_box_in_home_hero || settings.show_join_box_in_home_hero || settings.show_discord_box_in_home_hero,
      }"
    >
      <video
        v-if="isBgImageVideo"
        id="home_hero_bg_image_light_video"
        class="absolute w-full -z-10"
        autoplay
        loop
        muted
        :style="`
                object-fit: ${settings.home_hero_bg_size_css};
                                object-position: ${settings.home_hero_bg_position_css};
                                height: ${settings.home_hero_bg_height_css};
      `"
      >
        <source
          :src="bgImageUrl"
          type="video/webm"
        >
      </video>

      <div class="flex flex-col items-center justify-around w-4/5 md:flex-row">
        <div
          v-if="joinBoxEnabled"
          class="flex justify-center cursor-pointer basis-1/4 hover:rainbow mb-4 md:mb-0 hover:scale-110 transition ease-in-out duration-500"
          @click="isSupported && copy(server ? server.hostname : $page.props.defaultQueryServer?.server?.hostname)"
        >
          <svg
            class="w-12 h-12 text-primary shrink-0 mr-3"
            viewBox="0 0 24 24"
            fill="currentColor"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z" />
          </svg>
          <div class="flex flex-col">
            <p
              class="font-bold text-left"
            >
              <span
                v-if="!loading && !error"
                class="uppercase text-primary"
              >{{ serverInfo?.players?.online }} {{ __("Players Online") }}
              </span>
              <span
                v-if="loading"
                class="text-foreground uppercase"
              >
                {{ __("Loading..") }}
              </span>
              <span
                v-if="error && !loading"
                class="text-red-400"
              >
                {{ error }}
              </span>
            </p>
            <p class="font-semibold text-sm mt-1 uppercase text-white">
              {{ copied ? __('Copied to Clipboard') : (server ? server.hostname :
                $page.props.defaultQueryServer?.server?.hostname) }}
            </p>
          </div>
        </div>

        <div
          v-if="fgImageBoxEnabled"
          class="flex items-center justify-center basis-1/2"
          :style="`
      max-width: ${settings.home_hero_bg_height_css};
      `"
        >
          <img
            class="object-contain animate-[scale_6s_ease-in-out_infinite]"
            :src="fgImageUrl"
            alt="HeaderFgImage"
          >
        </div>

        <a
          v-if="discordBoxEnabled"
          :href="$page.props.generalSettings.discord_invite_url"
          target="_blank"
          class="justify-center hidden basis-1/4 hover:rainbow md:flex hover:scale-110 transition ease-in-out duration-500"
        >
          <div class="flex flex-col">
            <p class="font-bold text-right uppercase text-primary">
              {{ discordUsersCount }} {{ __("Online") }}
            </p>
            <span
              class="font-semibold text-sm mt-1 uppercase text-white"
            >
              {{ __("Click to Join") }}
            </span>
          </div>
          <svg
            class="w-11 h-11 text-primary shrink-0 ml-3"
            viewBox="0 0 24 24"
            fill="currentColor"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z" />
          </svg>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import { useClipboard } from "@vueuse/core";
import axios from "axios";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
const { copy, copied, isSupported } = useClipboard({ legacy: true });

const props = defineProps({
    settings: Object, // This is Theme Settings
    server: Object,
});

const serverInfo = ref({});
const loading = ref(true);
const error = ref(null);
const particleOptions = ref({});
const interval = ref(null);

const bgImageUrl = window.colorMode === "dark"
    ? props.settings.home_hero_bg_image_path_dark
    : props.settings.home_hero_bg_image_path_light;

const fgImageUrl = window.colorMode === "dark"
    ? props.settings.home_hero_fg_image_path_dark
    : props.settings.home_hero_fg_image_path_light;

const isBgImageVideo = bgImageUrl.includes(".webm");

const joinBoxEnabled = computed(() => {
    if (!props.settings.show_join_box_in_home_hero) return false;

    return !!props.server || !!usePage().props.defaultQueryServer.server;
});

const fgImageBoxEnabled = computed(() => {
    return props.settings.show_fg_image_box_in_home_hero && fgImageUrl;
});


const getServerPing = () => {
    let serverToPing = props.server;
    if (!serverToPing) {
        serverToPing = usePage().props.defaultQueryServer.server;
    }
    axios.get(route("server.ping.get", serverToPing.id)).then((data) => {
        serverInfo.value = data.data;
        error.value = null;
    }).catch((err) => {
        error.value = err.response.data.message || err.message;
        serverInfo.value = null;
    }).finally(() => {
        loading.value = false;
    });
};


const discordBoxEnabled = computed(() => {
    return props.settings.show_discord_box_in_home_hero
        && usePage().props.generalSettings.discord_invite_url;
});

const discordUsersCount = ref("");
const getDiscordOnlinePlayers = () => {
    const serverId = usePage().props.generalSettings.discord_server_id;
    if (!serverId) return;

    // try to get discord player count using widget.json discord api
    fetch(`https://discord.com/api/guilds/${serverId}/widget.json`).then(data => {
        return data.json();
    }).then(data => {
        discordUsersCount.value = data.presence_count ?? "";
    }).catch(err => {
        console.warn("Failed to get discord users count", err);
    });
};

onBeforeUnmount(() => {
    if (joinBoxEnabled.value) {
        clearInterval(interval.value);
    }
});

onMounted(() => {
    if (joinBoxEnabled.value) {
        getServerPing();
        interval.value = setInterval(getServerPing, 10000);
    }

    if (discordBoxEnabled.value) {
        getDiscordOnlinePlayers();
    }

    if(props.settings.home_hero_bg_particles) {
        particleOptions.value = JSON.parse(props.settings.home_hero_bg_particles);
        particleOptions.value = {
            ...particleOptions.value,
            "fullScreen": {
                "enable": false,
                "zIndex": 1
            },
            "background": {},
        };
    }
});

</script>
