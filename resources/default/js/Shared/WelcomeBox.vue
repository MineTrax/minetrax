<template>
  <div v-if="htmlData">
    <div class="bg-white dark:bg-cool-gray-800 rounded px-4 py-3 shadow">
      <div
        class="prose dark:prose-dark max-w-none text-center"
        v-html="htmlData"
      />
    </div>
  </div>
</template>

<script>

export default {
    props: ['htmlData']
};
</script>
