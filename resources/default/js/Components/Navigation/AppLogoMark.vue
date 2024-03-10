<template>
  <div class="flex items-center flex-shrink-0">
    <InertiaLink :href="route('home')">
      <JetApplicationMark class="block w-auto h-9" />
    </InertiaLink>
    <InertiaLink
      v-if="canShowAdminSidebar && !route().current('admin.*')"
      v-tippy
      :title="__('Administration Section')"
      aria-label="Open Menu"
      class="ml-2 focus:outline-none"
      :href="route('admin.dashboard')"
    >
      <Icon
        name="cog"
        class="w-6 h-6 text-gray-400 dark:text-gray-500 hover:animate-spin"
      />
    </InertiaLink>
  </div>
</template>

<script setup>
import JetApplicationMark from '@/Jetstream/ApplicationMark.vue';
import Icon from '@/Components/Icon.vue';

defineProps({
    canShowAdminSidebar: {
        type: Boolean,
        default: false,
    },
});
</script>
