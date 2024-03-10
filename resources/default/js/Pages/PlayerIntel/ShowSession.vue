<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AppLayout from '@/Layouts/AppLayout.vue';
import PlayerSubMenu from '@/Shared/PlayerSubMenu.vue';
import millify from 'millify';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } =
    useHelpers();

const props = defineProps({
    player: {
        type: Object,
        required: true,
    },
    session: {
        type: Object,
        required: true,
    },
    eventCounts: {
        type: Object,
        required: true,
    },
    showCriticalInfo: {
        type: Boolean,
        required: true,
    },
    criticalInfo: {
        type: Object,
        required: false,
    },
    canShowPlayerIntel: {
        type: Boolean,
        required: true,
    },
});

const parsedWorldLocation = props.criticalInfo?.world_location
    ? JSON.parse(props.criticalInfo.world_location)
    : null;
const worldLocation = parsedWorldLocation
    ? `x: ${Math.round(parsedWorldLocation.x)}, y: ${Math.round(
        parsedWorldLocation.y
    )}, z: ${Math.round(parsedWorldLocation.z)}`
    : '—';

let activeTime = props.session.play_time - props.session.afk_time;
activeTime = Math.max(activeTime, 0);
</script>

<template>
  <AppLayout>
    <AppHead
      :title="
        __(':username\'s session #:id - PlayerIntel', {
          username: player.username,
          id: session.id,
        })
      "
    />

    <div class="px-2 py-4 md:py-12 md:px-10 max-w-7xl mx-auto space-y-4">
      <PlayerSubMenu
        :player="player"
        :can-show-player-intel="canShowPlayerIntel"
      />
      <div
        class="flex flex-col shadow bg-white dark:bg-cool-gray-800 rounded p-3"
      >
        <h1 class="text-lg font-bold text-gray-700 dark:text-gray-300">
          {{ __("Session #:id", { id: session.id }) }},
          {{ player.username }}
          <span class="text-sm font-light">({{ player.uuid }})</span>
        </h1>

        <div
          class="grid grid-cols-1 md:grid-cols-2 grap-2 md:gap-16 w-full dark:text-gray-400 text-gray-600 mt-4"
        >
          <table>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Server") }}
              </td>
              <td class="text-right">
                {{ session.server.name }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Country") }}
              </td>
              <td class="text-right">
                {{ session.country.name }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Username") }}
              </td>
              <td class="text-right">
                {{ session.player_username }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Displayname") }}
              </td>
              <td class="text-right">
                {{ session.player_displayname }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Started At") }}
              </td>
              <td class="text-right">
                <span
                  v-tippy
                  class="whitespace-nowrap"
                  :title="
                    formatToDayDateString(
                      session.session_started_at
                    )
                  "
                >
                  {{
                    formatTimeAgoToNow(
                      session.session_started_at
                    )
                  }}
                </span>
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Ended At") }}
              </td>
              <td class="text-right">
                <span
                  v-if="session.session_ended_at"
                  v-tippy
                  class="whitespace-nowrap"
                  :title="
                    formatToDayDateString(
                      session.session_ended_at
                    )
                  "
                >
                  {{
                    formatTimeAgoToNow(
                      session.session_ended_at
                    )
                  }}
                </span>
                <span
                  v-else
                  class="text-gray-400"
                >—</span>
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Play Time") }}
              </td>
              <td class="text-right">
                {{ secondsToHMS(session.play_time, true) }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Active Time") }}
              </td>
              <td class="text-right">
                {{ secondsToHMS(activeTime, true) }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Afk Time") }}
              </td>
              <td class="text-right">
                {{ secondsToHMS(session.afk_time, true) }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Pvp Damage Given") }}
              </td>
              <td class="text-right">
                {{ eventCounts.pvp_damage_given }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Pvp Damage Taken") }}
              </td>
              <td class="text-right">
                {{ eventCounts.pvp_damage_taken }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Fish Caught") }}
              </td>
              <td class="text-right">
                {{ eventCounts.fish_caught }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Raids Won") }}
              </td>
              <td class="text-right">
                {{ eventCounts.raids_won }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Times Slept") }}
              </td>
              <td class="text-right">
                {{ eventCounts.times_slept_in_bed }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Enchanted") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_enchanted }}
              </td>
            </tr>
          </table>
          <table>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Player Kills") }}
              </td>
              <td class="text-right">
                {{ session.player_kills }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Mob Kills") }}
              </td>
              <td class="text-right">
                {{ session.mob_kills }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Deaths") }}
              </td>
              <td class="text-right">
                {{ session.deaths }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Used") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_used }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Mined") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_mined }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Pickedup") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_picked_up }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Dropped") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_dropped }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Consumed") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_consumed }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Broken") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_broken }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Crafted") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_crafted }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Items Placed") }}
              </td>
              <td class="text-right">
                {{ eventCounts.items_placed }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Distance Traveled") }}
              </td>
              <td class="text-right">
                {{ millify(eventCounts.distance_traveled) }}
                {{ __("meters") }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Distance Traveled on Land") }}
              </td>
              <td class="text-right">
                {{
                  millify(
                    eventCounts.distance_traveled_on_land
                  )
                }}
                {{ __("meters") }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Distance Traveled on Air") }}
              </td>
              <td class="text-right">
                {{
                  millify(
                    eventCounts.distance_traveled_on_air
                  )
                }}
                {{ __("meters") }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Distance Traveled on Water") }}
              </td>
              <td class="text-right">
                {{
                  millify(
                    eventCounts.distance_traveled_on_water
                  )
                }}
                {{ __("meters") }}
              </td>
            </tr>
          </table>
        </div>
      </div>

      <div
        v-if="showCriticalInfo && criticalInfo"
        class="flex flex-col shadow bg-white dark:bg-cool-gray-800 rounded p-3"
      >
        <h1 class="text-lg font-bold text-gray-700 dark:text-gray-300">
          {{ __("Critical Info") }}
          <span class="text-sm font-light">({{ __("Only visible to Staff with permission") }})</span>
        </h1>

        <div
          class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-16 w-full dark:text-gray-400 text-gray-600 mt-4"
        >
          <table>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("IP Address") }}
              </td>
              <td class="text-right">
                <a
                  class="hover:text-light-blue-400 hover:underline"
                  target="_blank"
                  :href="`https://whois.domaintools.com/${criticalInfo.player_ip_address}`"
                >
                  {{ criticalInfo.player_ip_address }}
                </a>
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Join Address") }}
              </td>
              <td class="text-right">
                {{ criticalInfo.join_address }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Minecraft Version") }}
              </td>
              <td class="text-right">
                {{ criticalInfo.minecraft_version }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Player Ping") }}
              </td>
              <td class="text-right">
                {{ criticalInfo.player_ping }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Vault Balance") }}
              </td>
              <td class="text-right">
                {{ millify(criticalInfo.vault_balance, {
                  precision: 2,
                }) }}
              </td>
            </tr>
          </table>
          <table>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Kicked") }}
              </td>
              <td class="text-right">
                {{ criticalInfo.is_kicked }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Banned") }}
              </td>
              <td class="text-right">
                {{ criticalInfo.is_banned }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Oped") }}
              </td>
              <td class="text-right">
                {{ criticalInfo.is_op }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("Vault Groups") }}
              </td>
              <td class="text-right">
                {{ criticalInfo.vault_groups }}
              </td>
            </tr>
            <tr>
              <td class="whitespace-nowrap">
                {{ __("World Location") }}
              </td>
              <td class="text-right">
                {{ worldLocation }}
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
