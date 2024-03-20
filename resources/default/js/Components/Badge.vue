<script setup>
defineProps({
    colorClass: {
        type: String,
        default: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
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
