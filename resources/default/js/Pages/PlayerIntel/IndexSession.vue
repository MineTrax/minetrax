<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PlayerSubMenu from '@/Shared/PlayerSubMenu.vue';
import { EyeIcon } from '@heroicons/vue/24/outline';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } = useHelpers();

const props = defineProps({
    player: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
    },
    sessions: {
        type: Object,
    },
    canShowPlayerIntel: {
        type: Boolean,
        required: true,
    },
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'text-left w-12',
    },
    {
        key: 'country_id',
        label: __('Flag'),
        sortable: true,
        class: 'text-left w-12',
    },
    {
        key: 'player_displayname',
        label: __('Display name'),
        sortable: true,
    },
    {
        key: 'session_started_at',
        label: __('Started'),
        sortable: true,
    },
    {
        key: 'session_ended_at',
        label: __('Ended'),
        sortable: true,
    },
    {
        key: 'server_id',
        label: __('Server'),
        sortable: true,
    },
    {
        key: 'play_time',
        label: __('Play Time'),
        sortable: true,
        class: 'text-right',
    },
    {
        key: 'afk_time',
        label: __('Afk Time'),
        sortable: true,
        class: 'text-right',
    },
    {
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'text-right',
    },
];

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
        current: true,
    },
];
</script>

<template>
  <AppLayout>
    <AppHead
      :title="__(':username\'s sessions - PlayerIntel', {
        username: player.username,
      })"
    />

    <AppBreadcrumb :items="breadcrumbItems" />

    <div class="px-2 py-4 md:px-10 max-w-screen-2xl mx-auto space-y-4">
      <PlayerSubMenu
        :player="player"
        :can-show-player-intel="canShowPlayerIntel"
      />

      <div>
        <DataTable
          class="bg-card border rounded-lg shadow w-full"
          :header="headerRow"
          :data="sessions"
          :filters="filters"
          :route-params="{ player: player.uuid }"
        >
          <template #default="{ item }">
            <td
              class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap"
            >
              {{ item.id }}
            </td>

            <td
              class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap"
            >
              <div class="flex items-center">
                <div
                  v-tippy
                  class="flex-shrink-0 h-10 w-10 focus:outline-none"
                  :content="item.country.name"
                >
                  <img
                    class="h-10 w-10"
                    :src="item.country.photo_path"
                    alt=""
                  >
                </div>
              </div>
            </td>

            <td
              class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <img
                    class="h-10 w-10"
                    :src="player?.avatar_url"
                    alt=""
                  >
                </div>
                <div class="ml-4">
                  <InertiaLink
                    as="a"
                    :href="route('player.intel.session.show', {
                      player: item.player_uuid,
                      session: item.id,
                    })"
                    class="text-sm font-medium text-foreground focus:outline-none cursor-pointer hover:underline"
                  >
                    <span class="font-extrabold text-foreground">
                      {{ item.player_displayname }} ({{ item.player_username }})
                    </span>
                  </InertiaLink>
                </div>
              </div>
            </td>

            <DtRowItem>
              <span
                v-tippy
                class="whitespace-nowrap"
                :title="formatToDayDateString(item.session_started_at)"
              >
                {{ formatTimeAgoToNow(item.session_started_at) }}
              </span>
            </DtRowItem>

            <DtRowItem>
              <span
                v-if="item.session_ended_at"
                v-tippy
                class="whitespace-nowrap"
                :title="formatToDayDateString(item.session_ended_at)"
              >
                {{ formatTimeAgoToNow(item.session_ended_at) }}
              </span>
              <span
                v-else
                class="text-foreground"
              >—</span>
            </DtRowItem>

            <DtRowItem>
              <span
                v-tippy
                class="whitespace-nowrap"
              >
                {{ item.server.name }}
              </span>
            </DtRowItem>

            <DtRowItem class="text-right">
              {{ secondsToHMS(item.play_time, true) }}
            </DtRowItem>

            <DtRowItem class="text-right">
              {{ secondsToHMS(item.afk_time, true) }}
            </DtRowItem>

            <td class="px-4 py-3 text-sm font-medium text-center whitespace-nowrap">
              <InertiaLink
                v-tippy
                as="a"
                :href="route('player.intel.session.show', {
                  player: item.player_uuid,
                  session: item.id,
                })"
                class="inline-flex items-center justify-center text-primary hover:text-primary/75"
                :title="__('View Session Details')"
              >
                <EyeIcon class="inline-block w-5 h-5" />
              </InertiaLink>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>
