<template>
  <div
    :class="`mb-4 bg-white dark:bg-cool-gray-800 border-t-4 ${borderColor} rounded-b ${textColor} px-4 py-3 shadow`"
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
      <div>
        <div class="font-bold">
          <slot />
        </div>
        <div class="text-sm">
          <slot name="body" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
    borderColor: {
        type: String,
        default: 'border-green-500',
    },
    textColor: {
        type: String,
        default: 'text-green-500',
    },
});
</script>
