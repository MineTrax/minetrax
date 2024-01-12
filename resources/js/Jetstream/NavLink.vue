<script setup>
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
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-light-blue-400 text-sm leading-5 text-gray-900 dark:text-gray-200 focus:outline-none focus:border-light-blue-700 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 transition duration-150 ease-in-out';
});
</script>

<template>
  <InertiaLink
    v-if="!openInNewTab"
    :href="href"
    :class="classes"
  >
    <slot />
  </InertiaLink>
  <a
    v-else
    target="_blank"
    :href="href"
    :class="classes"
  >
    <slot />
  </a>
</template>
