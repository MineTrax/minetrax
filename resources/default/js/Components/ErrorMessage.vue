<template>
  <div
    class="bg-error-50 border border-error-400 mt-2 p-1 rounded text-center text-error-400 dark:bg-error-600 dark:bg-opacity-10"
  >
    <slot />
  </div>
</template>

<script>
export default {
    name: 'ErrorMessage',
};
</script>
