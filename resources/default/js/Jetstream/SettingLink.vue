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
                ? 'cursor-pointer h-10 mb-1 p-2 text-secondary-600 bg-surface-300 dark:bg-surface-800 dark:text-secondary-300 rounded transition duration-150 ease-in-out'
                : 'cursor-pointer h-10 mb-1 p-2 text-secondary-600 rounded hover:bg-surface-300 dark:hover:bg-surface-800 dark:hover:text-secondary-300 transition duration-150 ease-in-out';
        }
    }
};
</script>
