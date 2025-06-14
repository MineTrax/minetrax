<script setup>
import { computed } from 'vue';

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
    openInNewTab: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    return props.active
        ? 'block pl-3 pr-4 py-2 border-l-4 border-primary-400 text-base font-medium text-primary-700 bg-primary-50 dark:bg-surface-900 focus:outline-none focus:text-primary-800 focus:bg-primary-100 dark:focus:bg-surface-900 focus:border-primary-700 transition duration-150 ease-in-out'
        : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-secondary-600 dark:text-secondary-400 hover:text-secondary-800 dark:hover:text-secondary-200 hover:bg-surface-50 dark:hover:bg-surface-700 hover:border-secondary-300 focus:outline-none focus:text-secondary-800 dark:focus:text-secondary-200 focus:bg-surface-50 dark:focus:bg-surface-900 focus:border-secondary-300 transition duration-150 ease-in-out';
});
</script>

<template>
  <div>
    <button
      v-if="as == 'button'"
      :class="classes"
      class="w-full text-left"
    >
      <slot />
    </button>

    <InertiaLink
      v-else-if="as != 'button' && !openInNewTab"
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
  </div>
</template>
