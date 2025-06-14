<script setup>
defineProps({
    colorClass: {
        type: String,
        default: 'bg-surface-100 text-secondary-800 dark:bg-surface-900 dark:text-secondary-300'
    }
});
</script>

<template>
  <span
    class="font-medium me-2 px-2.5 py-0.5 rounded-full"
    :class="colorClass"
  >
    <slot />
  </span>
</template>
