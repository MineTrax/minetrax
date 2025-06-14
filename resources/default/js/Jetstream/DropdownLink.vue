<script setup>

defineProps({
    href: String,
    as: String,
    btnClass: String,
    openInNewTab: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
  <div>
    <button
      v-if="as == 'button'"
      type="submit"
      class="block w-full px-4 py-2 text-sm leading-5 text-secondary-700 dark:text-secondary-400 text-left hover:bg-surface-100 dark:hover:bg-surface-900 focus:outline-none focus:bg-surface-100 dark:focus:bg-surface-900 transition duration-150 ease-in-out"
      :class="btnClass"
    >
      <slot />
    </button>

    <InertiaLink
      v-else-if="as != 'button' && !openInNewTab"
      :href="href"
      class="block px-4 py-2 text-sm leading-5 text-secondary-700 dark:text-secondary-400 hover:bg-surface-100 dark:hover:bg-surface-900 focus:outline-none focus:bg-surface-100 dark:focus:bg-surface-900 transition duration-150 ease-in-out"
      :class="btnClass"
    >
      <slot />
    </InertiaLink>
    <a
      v-else
      target="_blank"
      :href="href"
      class="block px-4 py-2 text-sm leading-5 text-secondary-700 dark:text-secondary-400 hover:bg-surface-100 dark:hover:bg-surface-900 focus:outline-none focus:bg-surface-100 dark:focus:bg-surface-900 transition duration-150 ease-in-out"
      :class="btnClass"
    >
      <slot />
    </a>
  </div>
</template>
