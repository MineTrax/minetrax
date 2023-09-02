<template>
  <div
    :class="`mb-4 bg-white dark:bg-cool-gray-800 border-t-4 ${borderColor} rounded-b ${textColor} px-4 py-3 shadow relative`"
    role="alert"
  >
    <div class="flex">
      <div class="py-1">
        <slot name="icon">
          <svg
            :class="`fill-current h-6 w-6 ${textColor} mr-4`"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
          >
            <path
              d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"
            />
          </svg>
        </slot>
      </div>
      <div :class="titleClass">
        <div
          class="font-bold"
        >
          <slot />
        </div>
        <div class="text-sm">
          <slot name="body" />
        </div>
      </div>
    </div>
    <button
      v-if="closeButton"
      class="absolute rounded-full bg-white dark:bg-gray-800 border dark:border-gray-900 dark:hover:bg-gray-700 hover:bg-gray-100 p-1 -top-5 -right-3"
      @click="$emit('close')"
    >
      <XMarkIcon class="h-5 w-5" />
    </button>
  </div>
</template>

<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
    borderColor: {
        type: String,
        default: 'border-green-500',
    },
    textColor: {
        type: String,
        default: 'text-green-500',
    },
    titleClass: {
        type: String,
        default: '',
    },
    closeButton: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);
</script>
