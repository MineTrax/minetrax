<template>
  <div class="flex items-center p-3">
    <inertia-link
      :href="href"
      :class="classes"
    >
      <span class="mr-2">
        <slot name="icon" />
      </span>
      <span>
        <slot />
      </span>
    </inertia-link>
  </div>
</template>

<script>
export default {
    props: ['href', 'active'],

    computed: {
        classes() {
            return this.active
                ? 'bg-light-blue-400 -ml-3 cursor-pointer flex items-center px-6 py-2 rounded-r-full text-white w-full transition duration-150 ease-in-out'
                : 'cursor-pointer -ml-3 flex items-center px-6 py-2 rounded-r-full text-gray-700 dark:text-gray-400 w-full transition duration-150 ease-in-out hover:bg-light-blue-50 dark:hover:bg-cool-gray-700';
        }
    }
};
</script>
