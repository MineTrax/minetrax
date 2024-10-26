<template>
  <div v-if="htmlData">
    <div class="bg-white dark:bg-cool-gray-800 rounded px-4 py-3 shadow">
      <div
        class="prose dark:prose-dark max-w-none text-center"
        v-html="htmlData"
      />
    </div>
  </div>
  <div v-if="bedrockIpPort" class="mt-4 text-center">
    <button
      @click="copyToClipboard(bedrockIpPort)"
      class="px-4 py-2 font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700"
    >
      Copy Bedrock IP:Port
    </button>
  </div>
</template>

<script>
export default {
  props: ['htmlData', 'bedrockIpPort'],
  methods: {
    copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard');
      });
    },
  },
};
</script>
