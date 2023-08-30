<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PlayerSubMenu from '@/Shared/PlayerSubMenu.vue';
import { EyeIcon } from '@heroicons/vue/24/outline';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } = useHelpers();

defineProps({
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
        class: 'text-left',
    },
    {
        key: 'country_id',
        label: __('Flag'),
        sortable: true,
        class: 'text-left',
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
</script>

<template>
  <AppLayout>
    <AppHead
      :title="__(':username\'s sessions - PlayerIntel', {
        username: player.username,
      })"
    />

    <div class="px-2 py-4 md:py-12 md:px-10 max-w-7xl mx-auto space-y-4">
      <PlayerSubMenu
        :player="player"
        :can-show-player-intel="canShowPlayerIntel"
      />

      <div>
        <DataTable
          class="bg-white rounded shadow dark:bg-gray-800"
          :header="headerRow"
          :data="sessions"
          :filters="filters"
          :route-params="{ player: player.uuid }"
        >
          <template #default="{ item }">
            <td
              class="px-4 py-4 text-sm font-medium text-gray-800 whitespace-nowrap dark:text-gray-200"
            >
              {{ item.id }}
            </td>

            <td
              class="px-4 py-4 text-sm font-medium text-gray-800 whitespace-nowrap dark:text-gray-200"
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
              class="px-4 py-4 text-sm font-medium text-gray-800 whitespace-nowrap dark:text-gray-200"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <img
                    class="h-10 w-10"
                    :src="item.avatar_url"
                    alt=""
                  >
                </div>
                <div class="ml-4">
                  <InertiaLink
                    as="div"
                    :href="route('player.intel.session.show', {
                      player: item.player_uuid,
                      session: item.id,
                    })"
                    class="text-sm font-medium text-gray-900 dark:text-gray-200 focus:outline-none cursor-pointer hover:underline"
                  >
                    <span class="font-extrabold text-gray-700 dark:text-gray-300">
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
                class="text-gray-400"
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
                class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
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
