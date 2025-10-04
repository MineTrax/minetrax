<script setup>
defineProps({
    colorClass: {
        type: String,
        default: 'bg-slate-100 text-foreground dark:bg-slate-900 dark:text-foreground'
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
