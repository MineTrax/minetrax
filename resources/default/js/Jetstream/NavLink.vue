<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    openInNewTab: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
  <Link
    v-if="!openInNewTab"
    :href="href"
    prefetch
  >
    <slot />
</Link>
  <a
    v-else
    target="_blank"
    :href="href"
  >
    <slot />
  </a>
</template>
