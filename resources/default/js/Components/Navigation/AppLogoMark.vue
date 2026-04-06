<template>
  <div class="flex items-center shrink-0">
    <InertiaLink :href="route('home')">
      <JetApplicationMark class="block w-auto h-9" />
    </InertiaLink>
    <InertiaLink
      v-if="canShowAdminSidebar && !route().current('admin.*')"
      v-tippy
      :title="__('Administration Section')"
      aria-label="Open Menu"
      class="ml-2 focus:outline-hidden"
      :href="route('admin.dashboard')"
    >
      <Icon
        name="cog"
        class="w-6 h-6 text-foreground dark:text-foreground hover:animate-spin"
      />
    </InertiaLink>
  </div>
</template>

<script setup>
import JetApplicationMark from "@/Jetstream/ApplicationMark.vue";
import Icon from "@/Components/Icon.vue";

defineProps({
    canShowAdminSidebar: {
        type: Boolean,
        default: false,
    },
});
</script>
