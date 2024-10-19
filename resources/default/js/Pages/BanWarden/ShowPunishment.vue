<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import {Link} from '@inertiajs/vue3';
import {useTranslations} from '@/Composables/useTranslations';
import {useHelpers} from '@/Composables/useHelpers';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import { EyeIcon } from '@heroicons/vue/24/outline';
import ArrayTable from '@/Components/DataTable/ArrayTable.vue';
import Chart from '@/Components/Dashboard/Chart.vue';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } = useHelpers();


defineProps({
    punishment: Object,
});

const punishmentHistoryHeaders = [
    {
        key: 'id',
        label: __('#'),
        sortable: true,
        class: 'text-left w-12',
    },
    {
        key: 'country_id',
        label: __(''),
        sortable: true,
        class: 'text-left w-12',
    },
    {
        key: 'type.value',
        sortable: true,
        label: __('Type'),
        class: 'text-center w-1/12',
    },
    {
        key: 'uuid',
        sortable: true,
        label: __('Player'),
    },
    {
        key: 'ip_address',
        sortable: true,
        label: __('IP Address'),
        class: 'text-center',
    },
    {
        key: 'creator_username',
        sortable: true,
        label: __('Staff'),
    },
    {
        key: 'reason',
        sortable: true,
        label: __('Reason'),
    },
    {
        key: 'start_at',
        sortable: true,
        label: __('Date'),
    },
    {
        key: 'end_at',
        sortable: true,
        label: __('Expires'),
    },
    {
        key: 'is_active',
        label: __('Status'),
        sortable: true,
        class: 'text-center',
    },
    {
        key: 'actions',
        label: __(''),
        sortable: false,
        class: 'w-1 text-right',
    },
];

const sessionHeaders = [
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
        key: 'ip_address',
        sortable: true,
        label: __('IP Address'),
        class: 'text-center',
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
        label: __(''),
        sortable: false,
        class: 'w-1 text-right',
    },
];

const altHeaders = [
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
        key: 'username',
        label: __('Player'),
        sortable: true,
    },
    {
        key: 'ip_address',
        sortable: true,
        label: __('Last IP Address'),
        class: 'text-center',
    },
    {
        key: 'last_seen_at',
        label: __('Last Seen'),
        sortable: true,
    },
    {
        key: 'first_seen_at',
        label: __('First Seen'),
        sortable: true,
    },
    {
        key: 'play_time',
        label: __('Play Time'),
        sortable: true,
        class: 'text-right',
    },
    {
        key: 'punishments_count',
        label: __('Punishments'),
        sortable: true,
        class: 'text-right',
    },
    {
        key: 'actions',
        label: __(''),
        sortable: false,
        class: 'w-1 text-right',
    },
];



</script>

<template>
  <AppLayout>
    <AppHead :title="__('Punishments')" />

    <div class="px-2 py-4 mx-auto md:py-12 md:px-10 max-w-7xl">
      <div class="flex justify-between mb-8">
        <div class="flex items-center">
          <h1 class="text-lg font-bold text-gray-500 md:text-3xl dark:text-gray-300">
            {{ __("Punishment #:id", {
              id: punishment.id,
              punish: punishment.type.value
            }) }}
          </h1>
          <span class="ml-3 text-lg">
            <CommonStatusBadge :status="punishment.type.value" />
            <CommonStatusBadge :status="punishment.is_active ? 'active' : 'inactive'" />
            <CommonStatusBadge
              v-if="punishment.is_active && punishment.end_at == null"
              status="permanent"
            />
          </span>
        </div>
      </div>
      <div class="flex flex-col space-y-8 text-gray-800 dark:text-gray-300">
        <!-- Details Start -->
        <div class="grid w-full gap-2 md:grid-cols-3">
          <div class="w-full grid-cols-3 gap-6 p-2 leading-8 bg-white rounded shadow dark:bg-cool-gray-800 md:p-5 md:col-span-2 md:grid">
            <h3 class="col-span-3 -mb-4 text-lg font-bold text-gray-500 dark:text-gray-400">
              {{ __("Punishment Details") }}
            </h3>
            <div>
              <p class="font-semibold text-gray-500 dark:text-gray-400">
                {{ __("Type") }}
              </p>
              <p class="font-bold">
                <CommonStatusBadge :status="punishment.type.value" />
              </p>
            </div>

            <div>
              <p class="font-semibold text-gray-500">
                {{ __("ID") }}
              </p>
              <p class="font-bold">
                {{ punishment.id }}
              </p>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Status") }}
              </p>
              <p class="font-bold">
                <CommonStatusBadge
                  :status="punishment.is_active ? 'active' : 'inactive'"
                />
              </p>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Player") }}
              </p>
              <div v-if="punishment.uuid && punishment.victim_player">
                <div
                  class="flex items-center"
                >
                  <div class="flex-shrink-0 w-8 h-8">
                    <img
                      class="w-8 h-8"
                      :src="punishment.victim_player.avatar_url"
                      alt=""
                    >
                  </div>
                  <div class="ml-2">
                    <Link
                      v-tippy
                      as="a"
                      :href="route('player.show', punishment.victim_player.uuid)"
                      class="font-medium text-gray-900 cursor-pointer dark:text-gray-200 focus:outline-none hover:underline"
                      :content="punishment.victim_player.uuid"
                    >
                      <span
                        v-if="punishment.victim_player.username"
                        class="font-extrabold text-gray-700 dark:text-gray-300"
                      >{{ punishment.victim_player.username }}</span>
                      <span
                        v-else
                        class="italic text-red-500"
                      >{{ __("Unknown") }}</span>
                    </Link>
                  </div>
                </div>
              </div>
              <div v-else-if="punishment.uuid">
                <div
                  class="flex items-center"
                >
                  <div class="flex-shrink-0 w-8 h-8">
                    <img
                      class="w-8 h-8"
                      :src="route('player.avatar.get', punishment.uuid)"
                      alt=""
                    >
                  </div>
                  <div class="ml-2">
                    <div
                      v-tippy
                      class="font-medium text-gray-900 dark:text-gray-200"
                      :content="punishment.uuid"
                    >
                      <span
                        class="italic text-red-500"
                      >{{ __("Unknown") }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div
                v-else
                class="flex items-center space-x-2"
              >
                <img
                  class="w-8 h-8"
                  src="/images/pc_head.png"
                  alt=""
                >
                <span
                  class="text-sm font-bold text-gray-500 dark:text-gray-400"
                >{{ __("IP :punish", {punish: punishment.type.key}) }}
                </span>
              </div>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("IP :punish", {
                  punish: punishment.type.value
                }) }}
              </p>
              <p class="font-bold">
                {{ punishment.is_ipban ? __("Yes") : __("No") }}
              </p>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("IP Address") }}
              </p>
              <p class="font-bold">
                {{ punishment.masked_ip_address || __("None") }}
              </p>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Country") }}
              </p>
              <div class="flex items-center font-bold">
                <img
                  :src="punishment.country.photo_path"
                  :alt="punishment.country.iso_code"
                  class="w-8 h-8 mr-2"
                >
                {{ punishment.country.name }}
              </div>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Date") }}
              </p>
              <div class="flex flex-col font-bold">
                <span>
                  {{ formatTimeAgoToNow(punishment.start_at) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatToDayDateString(punishment.start_at) }}
                </span>
              </div>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Expires") }}
              </p>
              <div
                v-if="punishment.end_at"
                class="flex flex-col font-bold"
              >
                <span>
                  {{ formatTimeAgoToNow(punishment.end_at) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatToDayDateString(punishment.end_at) }}
                </span>
              </div>
              <div
                v-else
                class="font-bold"
              >
                {{ __("Never") }}
              </div>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Reason") }}
              </p>
              <p class="mt-1 font-bold leading-normal">
                {{ punishment.reason }}
              </p>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Notes") }}
              </p>
              <p class="mt-1 font-bold leading-normal">
                {{ punishment.notes ?? __("None") }}
              </p>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Server Scope") }}
              </p>
              <p class="font-bold">
                {{ punishment.server_scope }}
              </p>
            </div>
            <div>
              <p class="font-semibold text-gray-500">
                {{ __("Punished by") }}
              </p>
              <div>
                <div v-if="punishment.creator_uuid">
                  <div
                    class="flex items-center"
                  >
                    <div class="flex-shrink-0 w-8 h-8">
                      <img
                        class="w-8 h-8"
                        :src="punishment.creator_player ? punishment.creator_player.avatar_url : route('player.avatar.get', punishment.creator_uuid)"
                        alt=""
                      >
                    </div>
                    <div class="ml-2">
                      <Link
                        v-tippy
                        as="a"
                        :href="punishment.creator_player ? route('player.show', punishment.creator_player.uuid): '#'"
                        class="font-medium text-gray-900 cursor-pointer dark:text-gray-200 focus:outline-none hover:underline"
                        :content="punishment.creator_uuid"
                      >
                        <span
                          v-if="punishment.creator_player?.username || punishment.creator_username"
                          class="font-extrabold text-gray-700 dark:text-gray-300"
                        >{{ punishment.creator_player?.username || punishment.creator_username }}</span>
                        <span
                          v-else
                          class="italic text-red-500"
                        >{{ __("Unknown") }}</span>
                      </Link>
                    </div>
                  </div>
                </div>
                <div
                  v-else
                  class="flex items-center space-x-2"
                >
                  <img
                    class="w-8 h-8"
                    src="/images/console_head.png"
                    alt=""
                  >
                  <span
                    class="text-sm font-bold text-gray-500 dark:text-gray-400"
                  >{{ __("CONSOLE") }}
                  </span>
                </div>
              </div>
            </div>

            <template v-if="punishment.removed_at && (punishment.remover_uuid || punishment.remover_username)">
              <div>
                <p class="font-semibold text-gray-500">
                  {{ __("Pardon by") }}
                </p>
                <div>
                  <div v-if="punishment.remover_uuid">
                    <div
                      class="flex items-center"
                    >
                      <div class="flex-shrink-0 w-8 h-8">
                        <img
                          class="w-8 h-8"
                          :src="punishment.remover_player ? punishment.remover_player.avatar_url : route('player.avatar.get', punishment.remover_uuid)"
                          alt=""
                        >
                      </div>
                      <div class="ml-2">
                        <Link
                          v-tippy
                          as="a"
                          :href="punishment.remover_player ? route('player.show', punishment.remover_player.uuid): '#'"
                          class="font-medium text-gray-900 cursor-pointer dark:text-gray-200 focus:outline-none hover:underline"
                          :content="punishment.remover_uuid"
                        >
                          <span
                            v-if="punishment.remover_player?.username || punishment.creator_username"
                            class="font-extrabold text-gray-700 dark:text-gray-300"
                          >{{ punishment.remover_player?.username || punishment.creator_username }}</span>
                          <span
                            v-else
                            class="italic text-red-500"
                          >{{ __("Unknown") }}</span>
                        </Link>
                      </div>
                    </div>
                  </div>
                  <div
                    v-else
                    class="flex items-center space-x-2"
                  >
                    <img
                      class="w-8 h-8"
                      src="/images/console_head.png"
                      alt=""
                    >
                    <span
                      class="text-sm font-bold text-gray-500 dark:text-gray-400"
                    >{{ __("CONSOLE") }}
                    </span>
                  </div>
                </div>
              </div>
              <div>
                <p class="font-semibold text-gray-500">
                  {{ __("Pardon at") }}
                </p>
                <div
                  class="flex flex-col font-bold"
                >
                  <span>
                    {{ formatTimeAgoToNow(punishment.removed_at) }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatToDayDateString(punishment.removed_at) }}
                  </span>
                </div>
              </div>
              <div>
                <p class="font-semibold text-gray-500">
                  {{ __("Pardon Reason") }}
                </p>
                <p class="mt-1 font-bold leading-normal">
                  {{ punishment.removed_reason ?? __("None") }}
                </p>
              </div>
            </template>
          </div>
          <div class="w-full p-2 bg-white rounded shadow dark:bg-cool-gray-800 md:p-5 md:col-span-1">
            <h3 class="col-span-3 text-lg font-bold text-gray-500 dark:text-gray-400">
              {{ __("AI Insights") }}
            </h3>
            <Chart
              v-if="punishment.insights && punishment.insights.score"
              :options="{
                series: [
                  {
                    type: 'gauge',
                    startAngle: 180,
                    endAngle: 0,
                    center: ['50%', '75%'],
                    radius: '90%',
                    min: 0,
                    max: 1,
                    splitNumber: 8,
                    axisLine: {
                      lineStyle: {
                        width: 6,
                        color: [
                          [0.25, '#7CFFB2'],
                          [0.50, '#58D9F9'],
                          [0.75, '#FDDD60'],
                          [1, '#FF6E76'],
                        ]
                      }
                    },
                    pointer: {
                      icon: 'path://M12.8,0.7l12,40.1H0.7L12.8,0.7z',
                      length: '12%',
                      width: 20,
                      offsetCenter: [0, '-60%'],
                      itemStyle: {
                        color: 'auto'
                      }
                    },
                    axisTick: {
                      length: 12,
                      lineStyle: {
                        color: 'auto',
                        width: 2
                      }
                    },
                    splitLine: {
                      length: 20,
                      lineStyle: {
                        color: 'auto',
                        width: 5
                      }
                    },
                    axisLabel: {
                      color: '#464646',
                      fontSize: 20,
                      distance: -60,
                      rotate: 'tangential',
                      formatter: function (value) {
                        if (value === 0.875) {
                          return '';
                        } else if (value === 0.625) {
                          return '';
                        } else if (value === 0.375) {
                          return '';
                        } else if (value === 0.125) {
                          return '';
                        }
                        return '';
                      }
                    },
                    title: {
                      offsetCenter: [0, '-10%'],
                      fontSize: 20
                    },
                    detail: {
                      fontSize: 30,
                      offsetCenter: [0, '-35%'],
                      valueAnimation: true,
                      formatter: function (value) {
                        return Math.round(value * 100) + '';
                      },
                      color: 'inherit'
                    },
                    data: [
                      {
                        value: punishment.insights?.score / 100 ?? 0,
                        name: 'Score'
                      }
                    ]
                  }
                ]
              }"
              height="250px"
              :autoresize="true"
            />

            <div
              v-if="punishment.insights && punishment.insights.insights"
              class="flex items-center justify-center"
            >
              <ul class="text-sm leading-9 list-disc">
                <li
                  v-for="insight in punishment.insights?.insights"
                  :key="insight"
                >
                  {{ insight }}
                </li>
              </ul>
            </div>

            <div
              v-if="!punishment.insights"
              class="flex items-center justify-center w-full h-full text-sm italic text-center"
            >
              {{ __("No insights available! Check back later.") }}
            </div>

            <div
              v-if="punishment.insights && punishment.insights.status === 'generating'"
              class="flex items-center justify-center w-full h-full text-sm italic text-center"
            >
              {{ __("Generating insights. Hang tight!") }} <br> {{ __("Please refresh the page after few seconds.") }}
            </div>
          </div>
        </div>
        <!-- Details End -->

        <!-- Punishment History Start -->
        <div>
          <h3 class="mb-2 text-2xl font-bold text-gray-500 dark:text-gray-300">
            {{ __("Last 5 Punishments") }}
          </h3>
          <div class="w-full bg-white rounded shadow dark:bg-cool-gray-800">
            <ArrayTable
              :url="route('player.punishment.show.history', punishment.id)"
              :header="punishmentHistoryHeaders"
              class="w-full bg-white rounded shadow dark:bg-gray-800"
            >
              <template #default="{ item }">
                <DtRowItem>
                  <Link
                    v-tippy
                    as="a"
                    :href="route('player.punishment.show', item.id)"
                    class="cursor-pointer focus:outline-none hover:underline"
                    :class="item.is_active ? 'font-bold dark:text-gray-300' : ''"
                    :content="__('View details')"
                  >
                    {{ item.id }}
                  </Link>
                </DtRowItem>

                <td class="px-3 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div
                      v-tippy
                      class="flex-shrink-0 w-8 h-8 focus:outline-none"
                      :content="item.country.name"
                    >
                      <img
                        class="w-8 h-8"
                        :src="item.country.photo_path"
                        alt=""
                      >
                    </div>
                  </div>
                </td>

                <DtRowItem class="text-center">
                  <CommonStatusBadge
                    :status="item.type.value"
                  />
                </DtRowItem>

                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="item.uuid && item.victim_player">
                    <div
                      class="flex items-center"
                    >
                      <div class="flex-shrink-0 h-7 w-7">
                        <img
                          class="h-7 w-7"
                          :src="item.victim_player.avatar_url"
                          alt=""
                        >
                      </div>
                      <div class="ml-2">
                        <Link
                          v-tippy
                          as="a"
                          :href="route('player.show', item.victim_player.uuid)"
                          class="text-sm font-medium text-gray-900 cursor-pointer dark:text-gray-200 focus:outline-none hover:underline"
                          :content="item.victim_player.uuid"
                        >
                          <span
                            v-if="item.victim_player.username"
                            class="font-extrabold text-gray-700 dark:text-gray-300"
                          >{{ item.victim_player.username }}</span>
                          <span
                            v-else
                            class="italic text-red-500"
                          >{{ __("Unknown") }}</span>
                        </Link>
                      </div>
                    </div>
                  </div>
                  <div v-else-if="item.uuid">
                    <div
                      class="flex items-center"
                    >
                      <div class="flex-shrink-0 h-7 w-7">
                        <img
                          class="h-7 w-7"
                          :src="route('player.avatar.get', item.uuid)"
                          alt=""
                        >
                      </div>
                      <div class="ml-2">
                        <div
                          v-tippy
                          class="text-sm font-medium text-gray-900 dark:text-gray-200"
                          :content="item.uuid"
                        >
                          <span
                            class="italic text-red-500"
                          >{{ __("Unknown") }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div
                    v-else
                    class="flex items-center space-x-2"
                  >
                    <img
                      class="h-7 w-7"
                      src="/images/pc_head.png"
                      alt=""
                    >
                    <span
                      class="text-xs font-bold text-gray-500 dark:text-gray-400"
                    >{{ __("IP :punish", {punish: item.type.key}) }}
                    </span>
                  </div>
                </td>

                <DtRowItem class="text-center">
                  <span v-if="item.masked_ip_address">
                    {{ item.masked_ip_address }}
                  </span>
                  <span
                    v-else
                    class="italic text-gray-400"
                  >
                    {{ __("None") }}
                  </span>
                </DtRowItem>

                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="item.creator_uuid">
                    <div
                      class="flex items-center"
                    >
                      <div class="flex-shrink-0 h-7 w-7">
                        <img
                          class="h-7 w-7"
                          :src="item.creator_player ? item.creator_player.avatar_url : route('player.avatar.get', item.creator_uuid)"
                          alt=""
                        >
                      </div>
                      <div class="ml-2">
                        <Link
                          v-tippy
                          as="a"
                          :href="item.creator_player ? route('player.show', item.creator_player.uuid): '#'"
                          class="text-sm font-medium text-gray-900 cursor-pointer dark:text-gray-200 focus:outline-none hover:underline"
                          :content="item.creator_uuid"
                        >
                          <span
                            v-if="item.creator_player?.username || item.creator_username"
                            class="font-extrabold text-gray-700 dark:text-gray-300"
                          >{{ item.creator_player?.username || item.creator_username }}</span>
                          <span
                            v-else
                            class="italic text-red-500"
                          >{{ __("Unknown") }}</span>
                        </Link>
                      </div>
                    </div>
                  </div>
                  <div
                    v-else
                    class="flex items-center space-x-2"
                  >
                    <img
                      class="h-7 w-7"
                      src="/images/console_head.png"
                      alt=""
                    >
                    <span
                      class="text-xs font-bold text-gray-500 dark:text-gray-400"
                    >{{ __("CONSOLE") }}
                    </span>
                  </div>
                </td>

                <DtRowItem>
                  <p
                    v-tippy
                    :title="item.reason"
                    class="w-20 truncate"
                  >
                    {{ item.reason }}
                  </p>
                </DtRowItem>

                <DtRowItem class="whitespace-nowrap">
                  <span
                    v-tippy
                    :title="formatToDayDateString(item.start_at)"
                  >
                    {{ formatTimeAgoToNow(item.start_at) }}
                  </span>
                </DtRowItem>

                <DtRowItem class="whitespace-nowrap">
                  <span
                    v-if="item.end_at"
                    v-tippy
                    :title="formatToDayDateString(item.end_at)"
                  >
                    {{ formatTimeAgoToNow(item.end_at) }}
                  </span>
                  <span
                    v-else
                    class="italic text-gray-400"
                  >
                    {{ __("None") }}
                  </span>
                </DtRowItem>


                <DtRowItem class="text-center">
                  <CommonStatusBadge
                    :status="item.is_active ? 'active' : 'inactive'"
                  />
                </DtRowItem>

                <td
                  class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap"
                >
                  <Link
                    v-tippy
                    :title="__('View details')"
                    as="a"
                    :href="
                      route(
                        'player.punishment.show', item.id
                      )
                    "
                    class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
                  >
                    <EyeIcon class="inline-block w-5 h-5" />
                  </Link>
                </td>
              </template>
            </ArrayTable>
          </div>
        </div>
        <!-- Punishment History End -->

        <!-- Past Sessions Start -->
        <div>
          <h3 class="mb-2 text-2xl font-bold text-gray-500 dark:text-gray-300">
            {{ __("Last 5 Sessions") }}
            <span class="text-sm">
              {{ __("(before this punishment)") }}
            </span>
          </h3>
          <div class="w-full bg-white rounded shadow dark:bg-cool-gray-800">
            <ArrayTable
              :url="route('player.punishment.show.session', punishment.id)"
              :header="sessionHeaders"
              class="w-full bg-white rounded shadow dark:bg-gray-800"
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
                      class="flex-shrink-0 w-8 h-8 focus:outline-none"
                      :content="item.country.name"
                    >
                      <img
                        class="w-8 h-8"
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
                    <InertiaLink
                      as="a"
                      :href="route('player.intel.session.show', {
                        player: item.player_uuid,
                        session: item.id,
                      })"
                      class="text-sm font-medium text-gray-900 cursor-pointer dark:text-gray-200 focus:outline-none hover:underline"
                    >
                      <span class="font-extrabold text-gray-700 dark:text-gray-300">
                        {{ item.player_displayname }} ({{ item.player_username }})
                      </span>
                    </InertiaLink>
                  </div>
                </td>

                <DtRowItem class="text-center">
                  <span
                    v-tippy
                    class="whitespace-nowrap"
                  >
                    {{ item.player_ip_address }}
                  </span>
                </DtRowItem>

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
            </ArrayTable>
          </div>
        </div>
        <!-- Past Sessions End -->

        <!-- Possible Alts Start -->
        <div>
          <h3 class="mb-2 text-2xl font-bold text-gray-500 dark:text-gray-300">
            {{ __("Possible Alts") }}
            <span class="text-sm">
              {{ __("(players with similar ip address)") }}
            </span>
          </h3>
          <div class="w-full bg-white rounded shadow dark:bg-cool-gray-800">
            <ArrayTable
              :url="route('player.punishment.show.alt', punishment.id)"
              :header="altHeaders"
              class="w-full bg-white rounded shadow dark:bg-gray-800"
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
                      class="flex-shrink-0 w-8 h-8 focus:outline-none"
                      :content="item.country.name"
                    >
                      <img
                        class="w-8 h-8"
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
                    <div class="flex-shrink-0 h-7 w-7">
                      <img
                        class="h-7 w-7"
                        :src="item.avatar_url"
                        alt=""
                      >
                    </div>
                    <div class="ml-2">
                      <Link
                        v-tippy
                        as="a"
                        :href="route('player.show', item.uuid)"
                        class="text-sm font-medium text-gray-900 cursor-pointer dark:text-gray-200 focus:outline-none hover:underline"
                        :content="item.uuid"
                      >
                        <span
                          v-if="item.username"
                          class="font-extrabold text-gray-700 dark:text-gray-300"
                        >{{ item.username }}</span>
                        <span
                          v-else
                          class="italic text-red-500"
                        >{{ __("Unknown") }}</span>
                      </Link>
                    </div>
                  </div>
                </td>

                <DtRowItem class="text-center">
                  <span
                    v-tippy
                    class="whitespace-nowrap"
                  >
                    {{ item.ip_address }}
                  </span>
                </DtRowItem>

                <DtRowItem>
                  <span
                    v-tippy
                    class="whitespace-nowrap"
                    :title="formatToDayDateString(item.last_seen_at)"
                  >
                    {{ formatTimeAgoToNow(item.last_seen_at) }}
                  </span>
                </DtRowItem>

                <DtRowItem>
                  <span
                    v-tippy
                    class="whitespace-nowrap"
                    :title="formatToDayDateString(item.first_seen_at)"
                  >
                    {{ formatTimeAgoToNow(item.first_seen_at) }}
                  </span>
                </DtRowItem>

                <DtRowItem class="text-right">
                  {{ secondsToHMS(item.play_time, true) }}
                </DtRowItem>

                <DtRowItem class="text-right">
                  {{ item.punishments_count }}
                </DtRowItem>

                <td class="px-4 py-3 text-sm font-medium text-center whitespace-nowrap">
                  <InertiaLink
                    v-tippy
                    as="a"
                    :href="route('player.show', {
                      player: item.uuid,
                    })"
                    class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
                    :title="__('View Details')"
                  >
                    <EyeIcon class="inline-block w-5 h-5" />
                  </InertiaLink>
                </td>
              </template>
            </ArrayTable>
          </div>
        </div>
        <!-- Possible Alts End -->
      </div>
    </div>
  </AppLayout>
</template>
