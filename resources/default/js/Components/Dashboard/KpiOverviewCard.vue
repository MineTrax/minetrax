<script setup>
defineProps({
    icon: {
        type: [Object, Function],
        required: true,
    },
    iconClass: {
        type: [Array, String],
        required: false,
        default: 'text-gray-500 bg-gray-100 dark:bg-gray-500 dark:text-white'
    },
    title: {
        type: [String, Number],
        required: true,
    },
    subValue: {
        type: [String, Number],
        required: false,
        default: null,
    },
    subValueClass: {
        type: [Array, String],
        required: false,
        default: 'text-gray-400',
    },
    description: {
        type: [String, Number],
        required: false,
        default: null,
    },
    descriptionClass: {
        type: [Array, String],
        required: false,
        default: 'text-gray-400',
    },
    value: {
        type: [String, Number],
        required: true,
    },
    change: {
        type: [String, Number],
        required: false,
        default: null,
    },
    changeClass: {
        type: [Array, String],
        required: false,
        default: 'text-gray-500 dark:text-gray-400 bg-gray-100',
    },
    changeDesc: {
        type: [String, Number],
        required: false,
        default: null,
    },
});
</script>

<template>
  <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
    <div class="flex">
      <component
        :is="icon"
        class="w-6 h-6 p-1 mr-1 rounded-full"
        :class="iconClass"
      />
      <h3 class="mb-3 font-bold text-gray-600 dark:text-gray-400 text-md">
        {{ __(title) }}
      </h3>
    </div>

    <div class="flex items-end justify-between space-x-4">
      <div>
        <p class="text-4xl font-bold dark:text-gray-300 text-gray-800">
          {{ value }}
          <span
            v-if="subValue"
            class="text-sm font-semibold"
            :class="subValueClass"
          >
            {{ subValue }}
          </span>
        </p>

        <p
          v-if="description"
          class="text-xs"
          :class="descriptionClass"
        >
          {{ __(description) }}
        </p>
      </div>
      <div
        v-if="change"
        class="text-end"
      >
        <span
          class="text-sm w-12 font-bold dark:bg-opacity-10 rounded-full px-2 py-0.5"
          :class="changeClass"
        >
          {{ change }}
        </span>
        <p class="text-xs text-gray-400 whitespace-nowrap">
          {{ __(changeDesc) }}
        </p>
      </div>
    </div>
  </div>
</template>
