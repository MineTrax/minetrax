<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ServerIntelServerSelector from '@/Shared/ServerIntelServerSelector.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import millify from 'millify';
import { LockClosedIcon, PaintBrushIcon, TrashIcon } from '@heroicons/vue/24/outline';

const { __ } = useTranslations();
const { can } = useAuthorizable();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } =
    useHelpers();

const props = defineProps({
    serverList: {
        type: Object,
    },
    countries: {
        type: Array,
    },
    filters: {
        type: Object,
    },
    data: {
        type: Object,
    },
    canResetAnyPlayerPassword: {
        type: Boolean,
    },
    canChangeAnyPlayerSkin: {
        type: Boolean,
    },
});

const headerRow = [
    {
        key: 'country_id',
        label: __('Flag'),
        sortable: true,
        class: 'text-left',
        filterable: {
            key: 'country.name',
            type: 'multiselect',
            options: props.countries,
            searchable: true,
        }
    },
    {
        key: 'player_username',
        label: __('Username'),
        sortable: true,
        class: 'text-left',
        filterable: {
            type: 'text',
        }
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
        filterable: {
            type: 'text',
        }
    },
    {
        key: 'last_minecraft_version',
        label: __('MC Version'),
        sortable: true,
        filterable: {
            type: 'multiselect',
            options: [
                '1.20',
                '1.19',
                '1.18',
                '1.17',
                '1.16',
                '1.15',
                '1.14',
                '1.13',
                '1.12',
                '1.11',
                '1.10',
                '1.9',
                '1.8',
            ]
        }
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
    {
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'w-1/12 text-right',
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
                    as="a"
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

            <td
              class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
            >
              <InertiaLink
                v-if="canChangeAnyPlayerSkin"
                v-tippy
                as="a"
                :href="route('change-player-skin.show', {
                  player_uuid: item.player.uuid,
                })"
                class="inline-flex items-center justify-center text-sky-400 hover:text-sky-700 focus:outline-none"
                :title="__('Change Skin of this player.')"
              >
                <PaintBrushIcon class="w-5 h-5" />
              </InertiaLink>

              <InertiaLink
                v-if="canResetAnyPlayerPassword"
                v-tippy
                as="a"
                :href="route('reset-player-password.show', {
                  player_uuid: item.player.uuid,
                })"
                class="inline-flex items-center justify-center text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100 focus:outline-none"
                :title="__('Change Password of this player.')"
              >
                <LockClosedIcon class="w-5 h-5" />
              </InertiaLink>

              <InertiaLink
                v-if="can('delete players')"
                v-confirm="{
                  message:
                    'This action will delete this player stats and unlink account if linked. Are you sure?',
                }"
                v-tippy
                as="button"
                method="DELETE"
                :href="route('admin.intel.player.delete', item.player.uuid)"
                class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
                :title="__('Delete Player')"
              >
                <TrashIcon class="inline-block w-5 h-5" />
              </InertiaLink>
            </td>
          </template>
        </DataTable>
      </div>
    </div>
  </AdminLayout>
</template>
