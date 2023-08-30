<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    player: {
        type: Object,
        required: true,
    },
    canShowPlayerIntel: {
        type: Boolean,
        required: true,
    },
});

</script>

<template>
  <ul
    class="flex justify-between bg-white shadow dark:bg-gray-800 p-4 rounded font-semibold"
  >
    <div class="flex">
      <li>
        <Link
          class="text-gray-700 dark:text-gray-300 rounded px-2 py-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
          :class="{
            'bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-900':
              route().current('player.show'),
          }"
          :href="route('player.show', { player: player.uuid })"
        >
          {{ __("Overview") }}
        </Link>
      </li>
    </div>

    <div class="flex space-x-4">
      <li v-if="canShowPlayerIntel">
        <Link
          :href="
            route('player.intel.session.index', {
              player: player.uuid,
            })
          "
          :class="{
            'bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-900':
              route().current('player.intel.session.index'),
          }"
          class="text-gray-700 dark:text-gray-300 rounded px-2 py-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        >
          {{ __("Sessions") }}
        </Link>
      </li>

      <li v-if="route().current('player.intel.session.show')">
        <span
          class="text-gray-700 dark:text-gray-300 rounded px-2 py-1.5 hover:bg-gray-200 dark:hover:bg-gray-600 bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-900"
        >
          {{ __("Session Details") }}
        </span>
      </li>
    </div>
  </ul>
</template>
