<script setup>

defineProps({
    href: String,
    as: String,
    btnClass: String,
});
</script>

<template>
  <div>
    <button
      v-if="as == 'button'"
      type="submit"
      class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-400 text-left hover:bg-cool-gray-100 dark:hover:bg-cool-gray-900 focus:outline-none focus:bg-cool-gray-100 dark:focus:bg-cool-gray-900 transition duration-150 ease-in-out"
      :class="btnClass"
    >
      <slot />
    </button>

    <InertiaLink
      v-else
      :href="href"
      class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-400 hover:bg-cool-gray-100 dark:hover:bg-cool-gray-900 focus:outline-none focus:bg-cool-gray-100 dark:focus:bg-cool-gray-900 transition duration-150 ease-in-out"
      :class="btnClass"
    >
      <slot />
    </InertiaLink>
  </div>
</template>
