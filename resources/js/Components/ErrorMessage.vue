<template>
  <div
    class="bg-red-50 border border-red-400 mt-2 p-1 rounded text-center text-red-400 dark:bg-red-600 dark:bg-opacity-10"
  >
    <slot />
  </div>
</template>

<script>
export default {
    name: 'ErrorMessage',
};
</script>
