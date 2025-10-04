<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppHead from '@/Components/AppHead.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';
import { useHelpers } from '@/Composables/useHelpers';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import { ArrowRightOnRectangleIcon, ExclamationTriangleIcon, EyeIcon, NoSymbolIcon, ScaleIcon, SpeakerXMarkIcon } from '@heroicons/vue/24/outline';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const props = defineProps({
    punishments: Object,
    countries: Array,
    filters: Object,
    metrics: Object,
});

const canShowMaskedIp = usePage().props.banwarden?.canShowMaskedIp;
const headerRow = [
    {
        key: 'id',
        label: __('#'),
        sortable: true,
        class: 'text-left w-12',
    },
    {
        key: 'country_id',
        label: '',
        sortable: true,
        class: 'text-left w-12',
        filterable: {
            key: 'country.name',
            type: 'multiselect',
            options: props.countries,
            searchable: true,
        }
    },
    {
        key: 'type',
        sortable: true,
        label: __('Type'),
        filterable: {
            type: 'multiselect',
            options: [
                'ban',
                'mute',
                'warn',
                'kick',
            ],
        },
        class: 'text-center w-1/12',
    },
    {
        key: 'uuid',
        sortable: true,
        label: __('Player'),
        filterable: {
            type: 'text',
            key: 'victimPlayer.username',
        }
    },
    ...(canShowMaskedIp ? [{
        key: 'ip_address',
        sortable: false,
        label: __('IP Address'),
        class: 'text-center',
    }] : []), // Only include ip if canShowMaskedIp is true
    {
        key: 'creator_username',
        sortable: false,
        label: __('Staff'),
        filterable: [
            {
                key: 'creator_username',
                label: __('Staff'),
                type: 'text',
            },
            {
                key: 'by_console',
                type: 'select',
                options: [
                    'true',
                    'false'
                ],
                searchable: false,
                label: __('By Console'),
            }
        ]
    },
    {
        key: 'reason',
        sortable: false,
        label: __('Reason'),
        filterable: {
            type: 'text',
        }
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
        key: 'status',
        label: __('Status'),
        sortable: false,
        class: 'text-center',
        filterable: [
            {
                key: 'status',
                label: __('Status'),
                type: 'select',
                options: [
                    'active',
                    'inactive',
                ]
            },
            {
                key: 'evidence_attached',
                type: 'select',
                options: [
                    'attached',
                    'missing'
                ],
                searchable: false,
                label: __('Evidence'),
            }
        ]
    },
    {
        key: 'actions',
        label: '',
        sortable: false,
        class: 'w-1 text-right',
    },
];
</script>

<template>
    <AppLayout>
        <AppHead :title="__('Punishments')" />

        <div class="py-4 px-2 md:py-12 md:px-10 max-w-screen-2xl mx-auto">
            <div class="grid-cols-5 gap-4 mb-4 hidden md:grid">
                <div class="col-span-full  md:col-span-1">
                    <div class="flex flex-row bg-white dark:bg-surface-800 shadow rounded p-4">
                        <div
                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-primary dark:bg-opacity-10 text-primary">
                            <ScaleIcon class="w-6 h-6 stroke-2" />
                        </div>
                        <div class="flex flex-col flex-grow ml-4">
                            <div class="text-sm text-foreground dark:text-foreground">
                                {{ __("Punishments") }}
                            </div>
                            <div class="font-bold dark:text-foreground text-lg">
                                {{ metrics.total_active_bans + metrics.total_active_mutes + metrics.total_active_warns
                                }} /
                                {{ metrics.total_bans + metrics.total_mutes + metrics.total_warns + metrics.total_kicks
                                }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-full  md:col-span-1">
                    <div class="flex flex-row bg-white dark:bg-surface-800 shadow rounded p-4">
                        <div
                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-error-100 dark:bg-opacity-10 text-error-500">
                            <NoSymbolIcon class="w-6 h-6 stroke-2" />
                        </div>
                        <div class="flex flex-col flex-grow ml-4">
                            <div class="text-sm text-foreground dark:text-foreground">
                                {{ __("Bans") }}
                            </div>
                            <div class="font-bold text-lg dark:text-foreground">
                                {{ metrics.total_active_bans }} / {{ metrics.total_bans }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-full  md:col-span-1">
                    <div class="flex flex-row bg-white dark:bg-surface-800 shadow rounded p-4">
                        <div
                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-surface-100 dark:bg-opacity-10 text-foreground">
                            <SpeakerXMarkIcon class="w-6 h-6 stroke-2" />
                        </div>
                        <div class="flex flex-col flex-grow ml-4">
                            <div class="text-sm text-foreground dark:text-foreground">
                                {{ __("Mutes") }}
                            </div>
                            <div class="font-bold text-lg dark:text-foreground">
                                {{ metrics.total_active_mutes }} / {{ metrics.total_mutes }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-full  md:col-span-1">
                    <div class="flex flex-row bg-white dark:bg-surface-800 shadow rounded p-4">
                        <div
                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-warning-100 dark:bg-opacity-10 text-warning-500">
                            <ExclamationTriangleIcon class="w-6 h-6 stroke-2" />
                        </div>
                        <div class="flex flex-col flex-grow ml-4">
                            <div class="text-sm text-foreground dark:text-foreground">
                                {{ __("Warns") }}
                            </div>
                            <div class="font-bold text-lg dark:text-foreground">
                                {{ metrics.total_active_warns }} / {{ metrics.total_warns }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-full  md:col-span-1">
                    <div class="flex flex-row bg-white dark:bg-surface-800 shadow rounded p-4">
                        <div
                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-orange-100 dark:bg-opacity-10 text-orange-500">
                            <ArrowRightOnRectangleIcon class="w-6 h-6 stroke-2" />
                        </div>
                        <div class="flex flex-col flex-grow ml-4">
                            <div class="text-sm text-foreground dark:text-foreground">
                                {{ __("Kicks") }}
                            </div>
                            <div class="font-bold text-lg dark:text-foreground">
                                {{ metrics.total_kicks }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:space-x-4">
                <DataTable class="bg-card rounded-lg shadow w-full" :header="headerRow"
                    :data="punishments" :filters="filters">
                    <template #default="{ item }">
                        <DtRowItem>
                            <Link v-tippy as="a" :href="route('player.punishment.show', item.id)"
                                class="focus:outline-none cursor-pointer hover:underline"
                                :class="item.is_active ? 'font-bold dark:text-foreground' : ''"
                                :content="__('View details')">
                            {{ item.id }}
                            </Link>
                        </DtRowItem>

                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div v-tippy class="flex-shrink-0 h-8 w-8 focus:outline-none"
                                    :content="item.country.name">
                                    <img class="h-8 w-8" :src="item.country.photo_path" alt="">
                                </div>
                            </div>
                        </td>

                        <DtRowItem class="text-center">
                            <CommonStatusBadge :status="item.type.value" />
                        </DtRowItem>

                        <td class="px-4 py-3 whitespace-nowrap">
                            <div v-if="item.uuid && item.victim_player">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-7 w-7">
                                        <img class="h-7 w-7" :src="item.victim_player.avatar_url" alt="">
                                    </div>
                                    <div class="ml-2">
                                        <Link v-tippy as="a" :href="route('player.show', item.victim_player.uuid)"
                                            class="text-sm font-medium text-foreground dark:text-foreground focus:outline-none cursor-pointer hover:underline"
                                            :content="item.victim_player.uuid">
                                        <span v-if="item.victim_player.username"
                                            class="font-extrabold text-foreground dark:text-foreground">{{
                                                item.victim_player.username }}</span>
                                        <span v-else class="text-error-500 italic">{{ __("Unknown") }}</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="item.uuid">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-7 w-7">
                                        <img class="h-7 w-7" :src="route('player.avatar.get', item.uuid)" alt="">
                                    </div>
                                    <div class="ml-2">
                                        <div v-tippy class="text-sm font-medium text-foreground dark:text-foreground"
                                            :content="item.uuid">
                                            <span class="text-error-500 italic">{{ __("Unknown") }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="flex items-center space-x-2">
                                <img class="h-7 w-7" src="/images/pc_head.png" alt="">
                                <span class="text-foreground dark:text-foreground text-xs font-bold">{{ __("IP :punish",
                                    { punish: item.type.key }) }}
                                </span>
                            </div>
                        </td>

                        <DtRowItem v-if="canShowMaskedIp" class="text-center">
                            <span v-if="item.ip_address || item.masked_ip_address">
                                <a class="hover:underline" target="_blank"
                                    :href="`https://check-host.net/ip-info?host=${item.ip_address || item.masked_ip_address}`">{{
                                        item.ip_address || item.masked_ip_address }}
                                </a>
                            </span>
                            <span v-else class="text-foreground italic">
                                {{ __("None") }}
                            </span>
                        </DtRowItem>

                        <td class="px-4 py-3 whitespace-nowrap">
                            <div v-if="item.creator_uuid">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-7 w-7">
                                        <img class="h-7 w-7"
                                            :src="item.creator_player ? item.creator_player.avatar_url : route('player.avatar.get', item.creator_uuid)"
                                            alt="">
                                    </div>
                                    <div class="ml-2">
                                        <Link v-tippy as="a"
                                            :href="item.creator_player ? route('player.show', item.creator_player.uuid) : '#'"
                                            class="text-sm font-medium text-foreground dark:text-foreground focus:outline-none cursor-pointer hover:underline"
                                            :content="item.creator_uuid">
                                        <span v-if="item.creator_player?.username || item.creator_username"
                                            class="font-extrabold text-foreground dark:text-foreground">{{
                                                item.creator_player?.username || item.creator_username }}</span>
                                        <span v-else class="text-error-500 italic">{{ __("Unknown") }}</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="flex items-center space-x-2">
                                <img class="h-7 w-7" src="/images/console_head.png" alt="">
                                <span v-if="item.creator_username && item.creator_username !== 'Console'"
                                    class="text-foreground dark:text-foreground text-xs font-bold">{{ item.creator_username
                                    }}
                                </span>
                                <span v-else class="text-foreground dark:text-foreground text-xs font-bold">{{ __("CONSOLE")
                                    }}
                                </span>
                            </div>
                        </td>

                        <DtRowItem>
                            <p v-tippy :title="item.reason" class="truncate w-32">
                                {{ item.reason }}
                            </p>
                        </DtRowItem>

                        <DtRowItem class="whitespace-nowrap">
                            <span v-tippy :title="formatToDayDateString(item.start_at)">
                                {{ formatTimeAgoToNow(item.start_at) }}
                            </span>
                        </DtRowItem>

                        <DtRowItem class="whitespace-nowrap">
                            <span v-if="item.end_at" v-tippy :title="formatToDayDateString(item.end_at)">
                                {{ formatTimeAgoToNow(item.end_at) }}
                            </span>
                            <span v-else class="italic text-foreground">
                                {{ __("None") }}
                            </span>
                        </DtRowItem>


                        <DtRowItem class="text-center">
                            <CommonStatusBadge :status="item.is_active ? 'active' : 'inactive'" />
                        </DtRowItem>

                        <td class="px-4 py-3 space-x-2 text-sm font-medium text-right whitespace-nowrap">
                            <InertiaLink v-tippy :title="__('View details')" as="a" :href="route(
                                'player.punishment.show', item.id
                            )
                                " class="inline-flex items-center justify-center text-primary hover:text-primary">
                                <EyeIcon class="inline-block w-5 h-5" />
                            </InertiaLink>
                        </td>
                    </template>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
