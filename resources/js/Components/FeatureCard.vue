<template>
  <div :class="`bg-white dark:bg-cool-gray-800 shadow rounded border-t-4 border-${color}-500 p-3`">
    <div :class="`inline px-2 py-1 bg-${color}-100 dark:bg-opacity-10 text-${color}-500 rounded text-sm font-semibold`">
      {{ capitalize(feature.type) }}
    </div>
    <a
      v-if="feature.img_url"
      :href="feature.img_url"
      target="_blank"
    >
      <img
        class="mt-2 object-cover w-full"
        :src="feature.img_url"
        alt="Image"
      >
    </a>
    <h4 class="font-bold text-black dark:text-gray-200 mt-2 text-lg">
      {{ feature.title }}
    </h4>
    <p class="text-sm text-gray-700 dark:text-gray-400">
      {{ feature.desc }}
    </p>
  </div>
</template>

<script>
export default {
    name: 'FeatureCard',
    props: ['feature', 'index'],
    data() {
        return {
            capitalize: _.capitalize
        };
    },
    computed: {
        color() {
            return (['red','light-blue','green','pink','orange','purple'])[ this.index % 6];
        }
    }
};
</script>
