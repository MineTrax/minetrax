<template>
  <div class="bg-white p-4 rounded flex space-x-4 justify-between dark:bg-surface-800 shadow">
    <div id="left">
      <h3
        class="text-foreground text-sm font-bold"
        :class="color"
      >
        {{ title }}
      </h3>
      <p class="text-2xl font-bold text-foreground dark:text-foreground">
        {{ value }}
      </p>
      <p
        v-if="desc"
        class="text-xs text-foreground"
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
