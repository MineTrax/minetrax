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
        ? 'block pl-3 pr-4 py-2 border-l-4 border-primary text-base font-medium text-primary bg-primary dark:bg-surface-900 focus:outline-none focus:text-primary focus:bg-primary dark:focus:bg-surface-900 focus:border-primary transition duration-150 ease-in-out'
        : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-foreground dark:text-foreground hover:text-foreground dark:hover:text-foreground hover:bg-surface-50 dark:hover:bg-surface-700 hover:border-foreground focus:outline-none focus:text-foreground dark:focus:text-foreground focus:bg-surface-50 dark:focus:bg-surface-900 focus:border-foreground transition duration-150 ease-in-out';
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
