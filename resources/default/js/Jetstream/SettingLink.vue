<template>
  <inertia-link
    :href="href"
    :class="classes"
  >
    <span class="">
      <slot name="icon" />
    </span>
    <span>
      <slot />
    </span>
  </inertia-link>
</template>

<script>
export default {
    props: ['href', 'active'],

    computed: {
        classes() {
            return this.active
                ? 'cursor-pointer h-10 mb-1 p-2 text-gray-600 bg-cool-gray-300 dark:bg-cool-gray-800 dark:text-gray-300 rounded transition duration-150 ease-in-out'
                : 'cursor-pointer h-10 mb-1 p-2 text-gray-600 rounded hover:bg-cool-gray-300 dark:hover:bg-cool-gray-800 dark:hover:text-gray-300 transition duration-150 ease-in-out';
        }
    }
};
</script>
