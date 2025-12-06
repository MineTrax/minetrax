<script setup>
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { Link } from '@inertiajs/vue3';
import { Card } from '@/Components/ui/card';

const { can } = useAuthorizable();

defineProps({
    id: {
        type: Number
    }
});
</script>

<template>
  <Card class="p-4">
    <ul class="flex font-semibold gap-3">
      <li>
        <Link
          class="text-foreground rounded px-2 py-1.5 hover:bg-muted"
          :class="{ 'bg-muted text-foreground' : route().current('admin.server.show') }"
          :href="route('admin.server.show', id)"
        >
          {{ __("Overview") }}
        </Link>
      </li>
      <li>
        <Link
          :href="route('admin.server.show.stats', id)"
          class="text-foreground rounded px-2 py-1.5 hover:bg-muted"
          :class="{ 'bg-muted text-foreground' : route().current('admin.server.show.stats') }"
        >
          {{ __("Statistics") }}
        </Link>
      </li>
      <li
        v-if="can('view server_intel')"
      >
        <Link
          :href="route('admin.intel.server.performance', {servers: [id]})"
          class="text-foreground rounded px-2 py-1.5 hover:bg-muted"
        >
          {{ __("Performance") }}
        </Link>
      </li>
      <li
        v-if="can('view server_intel')"
      >
        <Link
          :href="route('admin.intel.server.index', {servers: [id]})"
          class="text-foreground rounded px-2 py-1.5 hover:bg-muted"
        >
          {{ __("Insights") }}
        </Link>
      </li>
    </ul>
  </Card>
</template>
