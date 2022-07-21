<template>
  <div class="bg-white p-4 rounded flex space-x-4 justify-between dark:bg-cool-gray-800 shadow">
    <div id="left">
      <h3
        class="text-gray-400 text-sm font-bold"
        :class="color"
      >
        {{ title }}
      </h3>
      <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">
        {{ value }}
      </p>
      <p
        v-if="desc"
        class="text-xs text-gray-400"
      >
        {{ desc }}
      </p>
    </div>
    <div
      id="right"
      class="flex justify-center items-center"
    >
      <icon
        v-if="icon"
        class="w-16"
        :class="color"
        :name="icon"
      />
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';
export default {
    name: 'OverviewCard',
    components: {Icon},
    props: {
        title: String,
        value: String,
        desc: String,
        color: String,
        icon: String,
    }
};
</script>

<style scoped>

</style>
