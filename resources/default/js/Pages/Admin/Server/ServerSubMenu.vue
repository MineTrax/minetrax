<script setup>
import { useAuthorizable } from '@/Composables/useAuthorizable';

const { can } = useAuthorizable();

defineProps({
    id: {
        type: Number
    }
});
</script>

<template>
  <ul class="flex bg-white shadow dark:bg-surface-800 p-4 rounded font-semibold">
    <li class="mr-6">
      <inertia-link
        class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600"
        :class="{ 'bg-surface-200 text-foreground dark:bg-surface-900 dark:text-foreground dark:hover:bg-surface-900' : route().current('admin.server.show') }"
        :href="route('admin.server.show', id)"
      >
        {{ __("Overview") }}
      </inertia-link>
    </li>
    <li class="mr-6">
      <inertia-link
        :href="route('admin.server.show.stats', id)"
        class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600"
        :class="{ 'bg-surface-200 text-foreground dark:bg-surface-900 dark:text-foreground dark:hover:bg-surface-900' : route().current('admin.server.show.stats') }"
      >
        {{ __("Statistics") }}
      </inertia-link>
    </li>
    <li
      v-if="can('view server_intel')"
      class="mr-6"
    >
      <inertia-link
        :href="route('admin.intel.server.performance', {servers: [id]})"
        class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600"
      >
        {{ __("Performance") }}
      </inertia-link>
    </li>
    <li
      v-if="can('view server_intel')"
      class="mr-6"
    >
      <inertia-link
        :href="route('admin.intel.server.index', {servers: [id]})"
        class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600"
      >
        {{ __("Insights") }}
      </inertia-link>
    </li>
  </ul>
</template>
