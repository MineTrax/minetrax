<template>
  <div v-if="enabled && players && players.length > 0">
    <div class="p-3 bg-white dark:bg-surface-800 rounded shadow space-y-2">
      <h3 class="font-extrabold text-foreground dark:text-foreground">
        {{ title }}
      </h3>

      <div class="flex flex-col space-y-2">
        <table class="">
          <thead class="bg-surface-100 dark:bg-surface-900 dark:bg-opacity-50 text-foreground dark:text-foreground">
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
                class="p-1 text-left text-xs font-bold uppercase tracking-wider hidden sm:table-cell"
              >
                {{ __("Rating") }}
              </th>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider hidden sm:table-cell"
              >
                {{ __("Last Seen") }}
              </th>
            </tr>
          </thead>

          <tbody class="bg-white dark:bg-surface-800">
            <tr
              v-for="(player, index) in players"
              :key="index"
              :class="{'bg-surface-50 dark:bg-surface-600 dark:bg-opacity-10': index % 2 === 1}"
            >
              <td class="p-1 text-sm text-primary font-extrabold">
                <span
                  v-if="player.position"
                  class="border-2 rounded text-sm px-1 border-primary bg-primary dark:bg-surface-800"
                >
                  {{ player.position }}
                </span>
              </td>
              <td class="p-1">
                <div class="flex items-center">
                  <div
                    v-tippy
                    class="flex-shrink-0 h-6 w-6 focus:outline-none"
                    :content="player.country.name"
                  >
                    <img
                      class="h-6 w-6"
                      :src="player.country.photo_path"
                      :alt="player.country.name"
                    >
                  </div>
                </div>
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
                        class="text-error-500 dark:text-error-400 italic"
                      >{{ __("Unknown") }}</span>
                    </inertia-link>
                  </div>
                </div>
              </td>
              <td class="p-1 text-sm text-foreground hidden sm:table-cell">
                <span v-if="player.rating != null">
                  <icon
                    v-tippy
                    class="w-6 h-6 focus:outline-none"
                    :name="`rating-${player.rating}`"
                    :content="player.rating"
                  />
                </span>
                <span
                  v-else
                  class="text-foreground dark:text-foreground italic"
                >{{ __("none") }}</span>
              </td>
              <td class="p-1 text-xs text-foreground dark:text-foreground hidden sm:table-cell">
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
    </div>
  </div>
</template>

<script>
import Icon from '@/Components/Icon.vue';
import { useHelpers } from '@/Composables/useHelpers';

export default {
    components: {Icon},
    props: {
        title: String,
        players: Array,
        enabled: Boolean
    },
    setup() {
        const {formatTimeAgoToNow,formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow,formatToDayDateString};
    },
};
</script>
