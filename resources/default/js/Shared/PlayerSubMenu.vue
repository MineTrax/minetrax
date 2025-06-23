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
    canChangePlayerSkin: {
        type: Boolean,
        required: false,
        default: false,
    },
    canChangePlayerPassword: {
        type: Boolean,
        required: false,
        default: false,
    },
});

</script>

<template>
  <ul
    class="flex justify-between bg-white shadow dark:bg-surface-800 p-4 rounded font-semibold"
  >
    <div class="flex">
      <li>
        <Link
          class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600"
          :class="{
            'bg-surface-200 text-foreground dark:bg-surface-900 dark:text-foreground dark:hover:bg-surface-900':
              route().current('player.show'),
          }"
          :href="route('player.show', { player: player.uuid })"
        >
          {{ __("Overview") }}
        </Link>
      </li>
    </div>

    <div class="flex space-x-4">
      <li v-if="canChangePlayerPassword">
        <Link
          :href="
            route('reset-player-password.show', {
              player_uuid: player.uuid,
            })
          "
          class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600"
        >
          {{ __("Change Password") }}
        </Link>
      </li>
      <li v-if="canChangePlayerSkin">
        <Link
          :href="
            route('change-player-skin.show', {
              player_uuid: player.uuid,
            })
          "
          :class="{
            'bg-surface-200 text-foreground dark:bg-surface-900 dark:text-foreground dark:hover:bg-surface-900':
              route().current('player.intel.session.index'),
          }"
          class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600"
        >
          {{ __("Change Skin") }}
        </Link>
      </li>
      <li v-if="canShowPlayerIntel">
        <Link
          :href="
            route('player.intel.session.index', {
              player: player.uuid,
            })
          "
          :class="{
            'bg-surface-200 text-foreground dark:bg-surface-900 dark:text-foreground dark:hover:bg-surface-900':
              route().current('player.intel.session.index'),
          }"
          class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600"
        >
          {{ __("Sessions") }}
        </Link>
      </li>

      <li v-if="route().current('player.intel.session.show')">
        <span
          class="text-foreground dark:text-foreground rounded px-2 py-1.5 hover:bg-surface-200 dark:hover:bg-surface-600 bg-surface-200 text-foreground dark:bg-surface-900 dark:text-foreground dark:hover:bg-surface-900"
        >
          {{ __("Session Details") }}
        </span>
      </li>
    </div>
  </ul>
</template>
