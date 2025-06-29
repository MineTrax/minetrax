<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: String,
    active: Boolean,
    openInNewTab: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    return props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary text-sm leading-5 text-foreground dark:text-foreground focus:outline-none focus:border-primary transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm leading-5 text-foreground dark:text-foreground hover:text-foreground dark:hover:text-foreground hover:border-foreground focus:outline-none focus:text-foreground dark:focus:text-foreground focus:border-foreground transition duration-150 ease-in-out';
});
</script>

<template>
  <Link
    v-if="!openInNewTab"
    :href="href"
    :class="classes"
    prefetch
  >
    <slot />
</Link>
  <a
    v-else
    target="_blank"
    :href="href"
    :class="classes"
  >
    <slot />
  </a>
</template>
