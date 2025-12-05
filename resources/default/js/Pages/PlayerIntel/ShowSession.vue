<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AppLayout from '@/Layouts/AppLayout.vue';
import PlayerSubMenu from '@/Shared/PlayerSubMenu.vue';
import millify from 'millify';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';

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

const activeTime = Math.max(props.session.play_time - props.session.afk_time, 0);

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
    },
    {
        text: __('Players'),
        url: route('player.index'),
    },
    {
        text: props.player.username,
        url: route('player.show', props.player.uuid),
    },
    {
        text: __('Sessions'),
        url: route('player.intel.session.index', props.player.uuid),
    },
    {
        text: __('Session #:id', { id: props.session.id }),
        current: true,
    },
];
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

    <AppBreadcrumb :items="breadcrumbItems" />

    <div class="px-2 py-4 md:px-10 max-w-screen-2xl mx-auto space-y-4">
      <PlayerSubMenu
        :player="player"
        :can-show-player-intel="canShowPlayerIntel"
      />
      <Card>
        <CardHeader>
          <CardTitle>
            {{ __("Session #:id", { id: session.id }) }},
            {{ player.username }}
            <span class="text-sm font-light text-muted-foreground">({{ player.uuid }})</span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div
            class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-16 w-full"
          >
            <table class="w-full text-sm">
              <tbody>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Server") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ session.server.name }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Country") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ session.country.name }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Username") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ session.player_username }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Displayname") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ session.player_displayname }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Started At") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    <span
                      v-tippy
                      class="whitespace-nowrap cursor-help"
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
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Ended At") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    <span
                      v-if="session.session_ended_at"
                      v-tippy
                      class="whitespace-nowrap cursor-help"
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
                      class="text-muted-foreground italic"
                    >—</span>
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Play Time") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ secondsToHMS(session.play_time, true) }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Active Time") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ secondsToHMS(activeTime, true) }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Afk Time") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ secondsToHMS(session.afk_time, true) }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Pvp Damage Given") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.pvp_damage_given }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Pvp Damage Taken") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.pvp_damage_taken }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Fish Caught") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.fish_caught }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Raids Won") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.raids_won }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Times Slept") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.times_slept_in_bed }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Enchanted") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_enchanted }}
                  </td>
                </tr>
              </tbody>
            </table>
            <table class="w-full text-sm">
              <tbody>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Player Kills") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ session.player_kills }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Mob Kills") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ session.mob_kills }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Deaths") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ session.deaths }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Used") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_used }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Mined") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_mined }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Pickedup") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_picked_up }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Dropped") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_dropped }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Consumed") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_consumed }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Broken") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_broken }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Crafted") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_crafted }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Items Placed") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ eventCounts.items_placed }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Distance Traveled") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ millify(eventCounts.distance_traveled) }}
                    {{ __("meters") }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Distance Traveled on Land") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{
                      millify(
                        eventCounts.distance_traveled_on_land
                      )
                    }}
                    {{ __("meters") }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Distance Traveled on Air") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{
                      millify(
                        eventCounts.distance_traveled_on_air
                      )
                    }}
                    {{ __("meters") }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Distance Traveled on Water") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{
                      millify(
                        eventCounts.distance_traveled_on_water
                      )
                    }}
                    {{ __("meters") }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <Card v-if="showCriticalInfo && criticalInfo">
        <CardHeader>
          <CardTitle>
            {{ __("Critical Info") }}
            <span class="text-sm font-light text-muted-foreground">({{ __("Only visible to Staff with permission") }})</span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div
            class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-16 w-full"
          >
            <table class="w-full text-sm">
              <tbody>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("IP Address") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    <a
                      class="hover:text-primary hover:underline filter blur-sm hover:blur-none duration-300"
                      target="_blank"
                      :href="`https://check-host.net/ip-info?host=${criticalInfo.player_ip_address}`"
                    >
                      {{ criticalInfo.player_ip_address }}
                    </a>
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Join Address") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ criticalInfo.join_address }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Minecraft Version") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ criticalInfo.minecraft_version }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Player Ping") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ criticalInfo.player_ping }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Vault Balance") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ millify(criticalInfo.vault_balance, {
                      precision: 2,
                    }) }}
                  </td>
                </tr>
              </tbody>
            </table>
            <table class="w-full text-sm">
              <tbody>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Kicked") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ criticalInfo.is_kicked }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Banned") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ criticalInfo.is_banned }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Oped") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ criticalInfo.is_op }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("Vault Groups") }}
                  </td>
                  <td class="py-2 text-right font-bold">
                    {{ criticalInfo.vault_groups }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2 text-muted-foreground font-medium whitespace-nowrap">
                    {{ __("World Location") }}
                  </td>
                  <td class="py-2 text-right font-bold filter blur-sm hover:blur-none duration-300">
                    {{ worldLocation }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
