<template>
  <Card v-if="enabled && players && players.length > 0">
    <CardContent class="p-3 space-y-2">
      <h3 class="font-extrabold text-card-foreground">
        {{ title }}
      </h3>
      <div class="flex flex-col space-y-2">
        <table class="">
          <thead class="bg-background">
            <tr>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider"
              >
                {{ __("#") }}
              </th>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider"
              >
                {{ __("Flag") }}
              </th>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider"
              >
                {{ __("Name") }}
              </th>
              <th
                scope="col"
                class="p-1 text-center text-xs font-bold uppercase tracking-wider hidden sm:table-cell"
              >
                {{ __("Rating") }}
              </th>
              <th
                scope="col"
                class="p-1 text-right text-xs font-bold uppercase tracking-wider hidden sm:table-cell"
              >
                {{ __("Last Seen") }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/50">
            <tr
              v-for="(player, index) in players"
              :key="player.uuid"
            >
              <td class="p-1 font-semibold text-foreground dark:text-foreground text-sm">
                {{ index + 1 }}
              </td>
              <td class="p-1">
                <img
                  v-tippy
                  :content="player.country?.name"
                  v-if="player.country"
                  class="h-6 w-6"
                  :src="player.country?.photo_path"
                  :alt="player.country?.name"
                >
              </td>
              <td class="p-1">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-6 w-6">
                    <img
                      class="h-6 w-6"
                      :src="player.avatar_url"
                      alt=""
                    >
                  </div>
                  <div class="ml-4">
                    <inertia-link
                      v-tippy
                      as="div"
                      :href="route('player.show', player.uuid)"
                      class="text-sm text-foreground focus:outline-none cursor-pointer hover:underline"
                      :content="player.uuid"
                    >
                      <span
                        v-if="player.username"
                        class="text-foreground dark:text-foreground font-bold text-sm truncate"
                      >{{ player.username }}</span>
                      <span
                        v-else
                        class="text-destructive italic"
                      >{{ __("Unknown") }}</span>
                    </inertia-link>
                  </div>
                </div>
              </td>
              <td class="p-1 text-sm text-foreground text-center hidden sm:table-cell">
                <span v-if="player.rating != null">
                  <icon
                    v-tippy
                    class="w-6 h-6 mx-auto focus:outline-none"
                    :name="`rating-${player.rating}`"
                    :content="player.rating"
                  />
                </span>
                <span
                  v-else
                  class="text-foreground dark:text-foreground italic"
                >{{ __("none") }}</span>
              </td>
              <td class="p-1 text-xs text-foreground dark:text-foreground text-right hidden sm:table-cell">
                <span
                  v-tippy
                  class="focus:outline-none"
                  :content="formatToDayDateString(player.last_seen_at)"
                >
                  {{
                    player.last_seen_at ? formatTimeAgoToNow(player.last_seen_at) : "unknown"
                  }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import Icon from '@/Components/Icon.vue'
import { useHelpers } from '@/Composables/useHelpers'
import { Card, CardContent } from '@/Components/ui/card'

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  players: {
    type: Array,
    default: () => [],
  },
  enabled: {
    type: Boolean,
    default: true,
  },
})

const { formatTimeAgoToNow, formatToDayDateString } = useHelpers()
</script>
