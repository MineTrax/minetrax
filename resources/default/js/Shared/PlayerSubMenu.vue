<script setup>
import { Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/Components/ui/card';

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
  <Card>
    <CardContent class="p-3">
      <ul class="flex flex-wrap justify-between items-center gap-2 font-semibold">
        <div class="flex">
          <li>
            <Link
              class="text-muted-foreground hover:text-foreground rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-muted"
              :class="{
                'bg-secondary text-secondary-foreground hover:bg-secondary/80':
                  route().current('player.show'),
              }"
              :href="route('player.show', { player: player.uuid })"
            >
              {{ __("Overview") }}
            </Link>
          </li>
        </div>

        <div class="flex flex-wrap gap-2">
          <li v-if="canChangePlayerPassword">
            <Link
              :href="
                route('reset-player-password.show', {
                  player_uuid: player.uuid,
                })
              "
              class="text-muted-foreground hover:text-foreground rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-muted"
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
                'bg-secondary text-secondary-foreground hover:bg-secondary/80':
                  route().current('player.intel.session.index'),
              }"
              class="text-muted-foreground hover:text-foreground rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-muted"
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
                'bg-secondary text-secondary-foreground hover:bg-secondary/80':
                  route().current('player.intel.session.index'),
              }"
              class="text-muted-foreground hover:text-foreground rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-muted"
            >
              {{ __("Sessions") }}
            </Link>
          </li>

          <li v-if="route().current('player.intel.session.show')">
            <span
              class="rounded-md px-3 py-2 text-sm font-medium bg-secondary text-secondary-foreground"
            >
              {{ __("Session Details") }}
            </span>
          </li>
        </div>
      </ul>
    </CardContent>
  </Card>
</template>
