<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import AlertCard from '@/Components/AlertCard.vue';
import { useStorage } from '@vueuse/core';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();
const showPlayerRankSyncAlert = useStorage('show-player-rank-sync-alert', true);

defineProps({
    ranks: Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
    },
    {
        key: 'image',
        sortable: false,
        label: __('Image'),
    },
    {
        key: 'name',
        sortable: true,
        label: __('Name'),
        class: 'w-2/12',
    },
    {
        key: 'total_play_time_needed',
        label: __('Play Time Needed'),
        sortable: true,
        class: 'text-right',
    },
    {
        key: 'total_score_needed',
        label: __('Score Needed'),
        sortable: true,
        class: 'text-right',
    },
    {
        key: 'players_count',
        label: __('Player Count'),
        sortable: false,
        class: 'text-right',
    },
    {
        key: 'created_at',
        sortable: true,
        label: __('Created'),
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
    <app-head :title="__('Manage Player Ranks')" />

    <div class="px-10 py-8 mx-auto text-secondary-400">
      <AlertCard
        v-if="showPlayerRankSyncAlert"
        :close-button="true"
        text-color="text-primary-800 dark:text-primary-500"
        border-color="border-primary-500"
        @close="showPlayerRankSyncAlert = false"
      >
        {{ __("Do you know! MineTrax can also sync rank from server instead of calculating using algorithm.") }}
        <template #body>
          <p class="text-secondary-600 dark:text-secondary-400">
            {{
              __(
                "If you want to sync player rank from your minecraft server, follow the steps below:"
              )
            }}
          </p>
          <ul class="text-secondary-600 list-disc list-inside dark:text-secondary-400">
            <li>
              {{

                __("Create a rank for each rank you have in your server and want to sync. Make sure Short Name matches the rank identifier in your server.")
              }}
            </li>
            <li>
              {{

                __("Enable the 'Sync Rank' option from Admin > Settings > Plugin > Enable Player Rank Sync.")
              }}
            </li>
            <li>
              {{

                __("Done! Wait for some time and your player ranks will be synced automatically.")
              }}
            </li>
          </ul>

          <p class="italic text-secondary-400 dark:text-secondary-500">
            {{
              __(
                "Note: This is optional feature. You can safely close this alert if you don't want to use it. You can always do it later."
              )
            }}
          </p>
        </template>
      </AlertCard>

      <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold text-secondary-500 dark:text-secondary-300">
          {{ __("Manage Player Ranks") }}
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('update ranks')"
            v-confirm="{message: 'Are you sure you want to Reset all Ranks? This will remove current rank of all players.'}"
            method="post"
            as="button"
            :href="route('admin.rank.reset')"
            class="inline-flex items-center px-4 py-2 mr-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-error-600 border border-transparent rounded-md hover:bg-error-700 active:bg-error-900 focus:outline-none focus:border-error-900 focus:shadow-outline-red"
          >
            {{ __("Reset to Default Ranks") }}
          </InertiaLink>
          <InertiaLink
            v-if="can('create ranks')"
            :href="route('admin.rank.create')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-surface-800 border border-transparent rounded-md hover:bg-surface-700 active:bg-surface-900 focus:outline-none focus:border-secondary-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Create New") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Rank") }}</span>
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-surface-800"
        :header="headerRow"
        :data="ranks"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm font-medium text-secondary-800 whitespace-nowrap dark:text-secondary-200"
          >
            {{ item.id }}
          </td>
          <td class="px-4">
            <div class="">
              <img
                class="max-h-12 max-w-12"
                :src="item.photo_url"
                :alt="__('Rank Image')"
              >
            </div>
          </td>

          <td class="px-4 whitespace-nowrap">
            <div>
              <div class="text-sm font-medium text-secondary-900 dark:text-secondary-300">
                {{ item.name }}
              </div>
              <div class="text-sm text-secondary-500 dark:text-secondary-400">
                {{ item.shortname }}
              </div>
            </div>
          </td>

          <DtRowItem class="text-right">
            {{ item.total_play_time_needed }}
          </DtRowItem>

          <DtRowItem class="text-right">
            {{ item.total_score_needed }}
          </DtRowItem>

          <DtRowItem class="text-right">
            {{ item.players_count }}
          </DtRowItem>

          <DtRowItem>
            <span
              v-tippy
              :title="formatToDayDateString(item.created_at)"
            >
              {{ formatTimeAgoToNow(item.created_at) }}
            </span>
          </DtRowItem>

          <td
            class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
          >
            <InertiaLink
              v-if="can('update ranks')"
              v-tippy
              as="a"
              :href="route('admin.rank.edit', item.id)"
              class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
              :title="__('Edit Rank')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete ranks')"
              v-confirm="{
                message:
                  'Are you sure you want to delete this Rank permanently?',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.rank.delete', item.id)"
              class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
              :title="__('Delete Rank')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
