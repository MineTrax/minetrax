<script setup>
defineProps({
    colorClass: {
        type: String,
        default: 'bg-surface-100 text-foreground dark:bg-surface-900 dark:text-foreground'
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
