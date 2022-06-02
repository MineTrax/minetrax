<template>
  <div v-show="message">
    <p class="text-xs text-red-500">
      {{ message }}
    </p>
  </div>
</template>

<script>
export default {
    props: ['message']
};
</script>
