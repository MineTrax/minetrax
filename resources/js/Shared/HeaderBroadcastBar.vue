<template>
  <div
    v-if="enabled"
    class="z-50 flex items-center h-12 font-semibold text-white shadow-sm bg-light-blue-500"
  >
    <a
      class="w-full mt-2 cursor-pointer"
      target="_blank"
      :href="broadcastUrl"
    >
      <marquee
        onmouseover="this.stop();"
        onmouseout="this.start();"
      >
        {{ broadcastText }}
      </marquee>
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
