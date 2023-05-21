<template>
  <div class="flex items-center flex-shrink-0">
    <InertiaLink :href="route('home')">
      <JetApplicationMark class="block w-auto h-9" />
    </InertiaLink>
    <button
      v-if="canShowAdminSidebar"
      v-tippy
      :title="__('Administration Section')"
      aria-label="Open Menu"
      class="ml-2 focus:outline-none"
      @click="emit('open-admin-sidebar')"
    >
      <Icon
        name="cog"
        class="w-6 h-6 text-gray-400 dark:text-gray-500 hover:animate-spin"
      />
    </button>
  </div>
</template>

<script setup>
import JetApplicationMark from '@/Jetstream/ApplicationMark.vue';
import Icon from '@/Components/Icon.vue';

const emit = defineEmits(['open-admin-sidebar']);

defineProps({
    canShowAdminSidebar: {
        type: Boolean,
        default: false,
    },
});
</script>
