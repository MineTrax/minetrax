<template>
  <div
    v-if="enabled"
    class="z-50 flex items-center h-12 font-semibold text-white shadow-sm bg-light-blue-500 marquee-container"
  >
    <a
      class="w-full mt-2 cursor-pointer marquee-link marquee-text"
      target="_blank"
      :href="broadcastUrl"
    >
      {{ broadcastText }}
    </a>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// get general settings from Inertia getProps
const { generalSettings } = usePage().props;

const broadcastText = generalSettings?.header_broadcast_text;
const broadcastUrl = generalSettings?.header_broadcast_url;

const enabled = computed(() => {
    return !!generalSettings?.header_broadcast_text;
});
</script>

<style scoped>
.marquee-container {
    overflow: hidden;
    position: relative;
}

.marquee-link {
    text-decoration: none;
}

.marquee-text {
    white-space: nowrap;
    position: absolute;
    animation: marquee-animation 40s linear infinite;
    width: 100%;
}

.marquee-container:hover .marquee-text {
    animation-play-state: paused;
}

@keyframes marquee-animation {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(-100%);
    }
}
</style>
