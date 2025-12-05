<template>
  <div
    class="bg-destructive/5 border border-destructive rounded mt-2 p-1 text-center text-destructive"
  >
    <slot />
  </div>
</template>

<script>
export default {
    name: 'ErrorMessage',
};
</script>
