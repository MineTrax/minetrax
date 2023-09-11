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
      :particles-init="particlesInit"
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
          class="flex justify-center cursor-pointer basis-1/4 hover:rainbow mb-4 md:mb-0"
          @click="isSupported && copy(server ? server.hostname : $page.props.defaultQueryServer?.server?.hostname)"
        >
          <img
            src="/images/header-creeper.png"
            alt="Discord"
            class="w-16 h-16"
          >
          <div class="flex flex-col">
            <p
              class="mt-2 font-bold text-left"
            >
              <span
                v-if="!loading && !error"
                class="uppercase text-light-blue-400"
              >{{ serverInfo?.players?.online }} {{ __("Players Online") }}
              </span>
              <span
                v-if="loading"
                class="text-gray-400 uppercase"
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
            <p class="font-semibold text-sm mt-1.5 uppercase text-white">
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

        <div
          v-if="discordBoxEnabled"
          class="justify-center hidden basis-1/4 hover:rainbow md:flex"
        >
          <div class="flex flex-col">
            <p class="mt-2 font-bold text-right uppercase text-light-blue-400">
              {{ discordUsersCount }} {{ __("Online") }}
            </p>
            <a
              :href="$page.props.generalSettings.discord_invite_url"
              target="_blank"
              class="font-semibold text-sm mt-1.5 uppercase text-white"
            >
              {{ __("Click to Join") }}
            </a>
          </div>
          <img
            src="/images/header-discord.png"
            alt="Discord"
            class="w-16 h-16"
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { useClipboard } from '@vueuse/core';
const { copy, copied, isSupported } = useClipboard({ legacy: true });
import { loadFull } from 'tsparticles';

const props = defineProps({
    settings: Object, // This is Theme Settings
    server: Object,
});

const particlesInit = async engine => {
    //await loadFull(engine);
    await loadFull(engine);
};

const serverInfo = ref({});
const loading = ref(true);
const error = ref(null);
const particleOptions = ref({});
const interval = ref(null);

const bgImageUrl = window.colorMode === 'dark'
    ? props.settings.home_hero_bg_image_path_dark
    : props.settings.home_hero_bg_image_path_light;

const fgImageUrl = window.colorMode === 'dark'
    ? props.settings.home_hero_fg_image_path_dark
    : props.settings.home_hero_fg_image_path_light;

const isBgImageVideo = bgImageUrl.includes('.webm');

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
    axios.get(route('server.ping.get', serverToPing.id)).then((data) => {
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

const discordUsersCount = ref('');
const getDiscordOnlinePlayers = () => {
    const serverId = usePage().props.generalSettings.discord_server_id;
    if (!serverId) return;

    // try to get discord player count using widget.json discord api
    fetch(`https://discord.com/api/guilds/${serverId}/widget.json`).then(data => {
        return data.json();
    }).then(data => {
        discordUsersCount.value = data.presence_count ?? '';
    }).catch(err => {
        console.warn('Failed to get discord users count', err);
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
            'fullScreen': {
                'enable': false,
                'zIndex': 1
            },
            'background': {},
        };
    }
});

</script>
