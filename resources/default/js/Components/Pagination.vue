<template>
  <div class="mt-6 -mb-1 flex flex-wrap">
    <template v-for="(link, key) in links" :key="key">
      <div
        v-if="link.url === null"
        class="mr-1 mb-1 px-4 py-3 text-sm border rounded text-gray-400"
        :class="{ 'ml-auto': link.label === 'Next &raquo;' }"
        v-html="link.label"
      />
      <inertia-link
        v-else
        class="mr-1 mb-1 px-4 py-3 text-sm border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
        :class="{ 'bg-white shadow': link.active, 'bg-gray-50': !link.active, 'ml-auto': link.label === 'Next &raquo;' }"
        :href="link.url"
        v-html="link.label"
      />
    </template>
  </div>
</template>

<script>
export default {
    props: {
        links: Array,
    },
};
</script>
