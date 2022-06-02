<template>
  <div v-if="enabled && players && players.length > 0">
    <div class="p-3 bg-white dark:bg-cool-gray-800 rounded shadow space-y-2">
      <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
        {{ title }}
      </h3>

      <div class="flex flex-col space-y-2">
        <table class="">
          <thead class="bg-gray-100 dark:bg-cool-gray-900 dark:bg-opacity-50 text-gray-700 dark:text-gray-300">
            <tr>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold text-center uppercase tracking-wider"
              >
                #
              </th>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider"
              >
                Flag
              </th>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider"
              >
                Rank
              </th>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider"
              >
                Name
              </th>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider hidden sm:table-cell"
              >
                Rating
              </th>
              <th
                scope="col"
                class="p-1 text-left text-xs font-bold uppercase tracking-wider hidden sm:table-cell"
              >
                Last Seen
              </th>
            </tr>
          </thead>

          <tbody class="bg-white dark:bg-cool-gray-800">
            <tr
              v-for="(player, index) in players"
              :key="index"
              :class="{'bg-gray-50 dark:bg-cool-gray-600 dark:bg-opacity-10': index % 2 === 1}"
            >
              <td class="p-1 text-sm text-light-blue-400 font-extrabold">
                <span
                  v-if="player.position"
                  class="border-2 rounded text-sm px-1 border-light-blue-300 bg-light-blue-50 dark:bg-cool-gray-800"
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
                <div
                  v-if="player.rank"
                  class="flex items-center"
                >
                  <div
                    v-tippy
                    class="flex-shrink-0 h-6 w-6 focus:outline-none"
                    :content="player.rank.name"
                  >
                    <img
                      class="h-6 w-6"
                      :src="player.rank.photo_url"
                      alt=""
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
                      class="text-sm text-gray-900 focus:outline-none cursor-pointer hover:underline"
                      :content="player.uuid"
                    >
                      <span
                        v-if="player.username"
                        class="text-gray-700 dark:text-gray-300 font-bold text-sm truncate"
                      >{{ player.username }}</span>
                      <span
                        v-else
                        class="text-red-500 dark:text-red-400 italic"
                      >Unknown</span>
                    </inertia-link>
                  </div>
                </div>
              </td>
              <td class="p-1 text-sm text-gray-700 hidden sm:table-cell">
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
                  class="text-gray-700 dark:text-gray-500 italic"
                >none</span>
              </td>
              <td class="p-1 text-xs text-gray-700 dark:text-gray-300 hidden sm:table-cell">
                <span
                  v-tippy
                  class="focus:outline-none"
                  :content="format(new Date(player.last_seen_at), 'E, do MMM yyyy, h:mm aaa')"
                >
                  {{
                    player.last_seen_at ? formatDistanceToNowStrict(new Date(player.last_seen_at), {addSuffix: true}) : "unknown"
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
import { formatDistanceToNowStrict, format } from 'date-fns';
import Icon from '@/Components/Icon';

export default {
    components: {Icon},
    props: {
        title: String,
        players: Array,
        enabled: Boolean
    },
    data() {
        return {
            format: format,
            formatDistanceToNowStrict: formatDistanceToNowStrict
        };
    }
};
</script>
