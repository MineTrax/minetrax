<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import millify from 'millify';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } =
    useHelpers();

defineProps({
    serverList: {
        type: Object,
    },
    filters: {
        type: Object,
    },
    data: {
        type: Object,
    },
});

const headerRow = [
    {
        key: 'country_id',
        label: __('Flag'),
        sortable: true,
        class: 'text-left',
    },
    {
        key: 'player_username',
        label: __('Username'),
        sortable: true,
        class: 'text-left',
    },
    {
        key: 'server_play_count',
        label: __('Servers Played'),
        sortable: true,
    },
    {
        key: 'play_time',
        label: __('Play Time'),
        sortable: true,
    },
    {
        key: 'afk_time',
        label: __('Afk Time'),
        sortable: true,
    },
    {
        key: 'vault_balance',
        label: __('Vault Money'),
        sortable: true,
    },
    {
        key: 'last_join_address',
        label: __('Join Address'),
        sortable: true,
    },
    {
        key: 'last_minecraft_version',
        label: __('MC Version'),
        sortable: true,
    },
    {
        key: 'first_seen_at',
        label: __('First Seen'),
        sortable: true,
    },
    {
        key: 'last_seen_at',
        label: __('Last Seen'),
        sortable: true,
    },
];
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Players - PlayerIntel')" />

    <div class="p-4 mx-auto space-y-4 px-10">
      <ServerIntelServerSelector
        :title="__('Players')"
        :server-list="serverList"
        :filters="filters"
      />

      <div>
        <DataTable
          class="bg-white rounded shadow dark:bg-gray-800"
          :header="headerRow"
          :data="data"
          :filters="filters"
        >
          <template #default="{ item }">
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
                    :src="item.player.avatar_url"
                    alt=""
                  >
                </div>
                <div class="ml-4">
                  <inertia-link
                    v-tippy
                    as="div"
                    :href="route('player.show', item.player.uuid)"
                    class="text-sm font-medium text-gray-900 dark:text-gray-200 focus:outline-none cursor-pointer hover:underline"
                    :content="item.player.uuid"
                  >
                    <span
                      v-if="item.player_username"
                      class="font-extrabold text-gray-700 dark:text-gray-300"
                    >{{ item.player_username }}</span>
                    <span
                      v-else
                      class="text-red-500 italic"
                    >{{ __("Unknown") }}</span>
                  </inertia-link>
                </div>
              </div>
            </td>

            <DtRowItem>
              {{ item.server_play_count }} {{ __('servers') }}
            </DtRowItem>

            <DtRowItem>
              <span v-if="item.play_time">
                {{ secondsToHMS(item.play_time, true) }}
              </span>
              <span v-else>
                --
              </span>
            </DtRowItem>

            <DtRowItem>
              <span v-if="item.afk_time">
                {{ secondsToHMS(item.afk_time, true) }}
              </span>
              <span v-else>
                --
              </span>
            </DtRowItem>

            <DtRowItem>
              <span v-if="item.vault_balance">
                {{ millify(item.vault_balance, {
                  precision: 2,
                }) }}
              </span>
              <span v-else>
                --
              </span>
            </DtRowItem>

            <DtRowItem>
              {{ item.last_join_address || __('Unknown') }}
            </DtRowItem>

            <DtRowItem>
              {{ item.last_minecraft_version || __('Unknown') }}
            </DtRowItem>

            <DtRowItem
              v-tippy
              :content="formatToDayDateString(item.first_seen_at)"
            >
              {{ formatTimeAgoToNow(item.first_seen_at) }}
            </DtRowItem>

            <DtRowItem
              v-tippy
              :content="formatToDayDateString(item.last_seen_at)"
            >
              {{ formatTimeAgoToNow(item.last_seen_at) }}
            </DtRowItem>
          </template>
        </DataTable>
      </div>
    </div>
  </AdminLayout>
</template>
