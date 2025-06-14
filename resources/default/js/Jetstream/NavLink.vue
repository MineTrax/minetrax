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
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary-400 text-sm leading-5 text-secondary-900 dark:text-secondary-200 focus:outline-none focus:border-primary-700 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm leading-5 text-secondary-500 dark:text-secondary-400 hover:text-secondary-700 dark:hover:text-secondary-200 hover:border-secondary-300 focus:outline-none focus:text-secondary-700 dark:focus:text-secondary-300 focus:border-secondary-300 transition duration-150 ease-in-out';
});
</script>

<template>
  <Link
    v-if="!openInNewTab"
    :href="href"
    :class="classes"
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
