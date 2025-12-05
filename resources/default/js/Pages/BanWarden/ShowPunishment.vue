<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import DtRowItem from "@/Components/DataTable/DtRowItem.vue";
import CommonStatusBadge from "@/Shared/CommonStatusBadge.vue";
import { EyeIcon, DocumentMagnifyingGlassIcon, PlusSmallIcon } from "@heroicons/vue/24/outline";
import ArrayTable from "@/Components/DataTable/ArrayTable.vue";
import Chart from "@/Components/Dashboard/Chart.vue";
import { ref } from "vue";
import { LinkIcon, PaperClipIcon, XCircleIcon } from "@heroicons/vue/24/solid";
import XInput from "@/Components/Form/XInput.vue";
import LoadingButton from "@/Components/LoadingButton.vue";
import { CardContent, Card } from "@/Components/ui/card";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { Loader2Icon } from "lucide-vue-next";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { truncate } from "lodash";

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } = useHelpers();

const props = defineProps({
    punishment: Object,
    permissions: Object,
});

const canShowMaskedIp = usePage().props.banwarden?.canShowMaskedIp;
const punishmentHistoryHeaders = [
    {
        key: "id",
        label: __("#"),
        sortable: true,
        class: "text-left w-12",
    },
    {
        key: "country_id",
        label: "",
        sortable: true,
        class: "text-left w-12",
    },
    {
        key: "type.value",
        sortable: true,
        label: __("Type"),
        class: "text-center w-1/12",
    },
    {
        key: "uuid",
        sortable: true,
        label: __("Player"),
    },
    ...(canShowMaskedIp
        ? [
              {
                  key: "ip_address",
                  sortable: true,
                  label: __("IP Address"),
                  class: "text-center",
              },
          ]
        : []),
    {
        key: "creator_username",
        sortable: true,
        label: __("Staff"),
    },
    {
        key: "reason",
        sortable: true,
        label: __("Reason"),
    },
    {
        key: "start_at",
        sortable: true,
        label: __("Date"),
    },
    {
        key: "end_at",
        sortable: true,
        label: __("Expires"),
    },
    {
        key: "is_active",
        label: __("Status"),
        sortable: true,
        class: "text-center",
    },
    {
        key: "actions",
        label: "",
        sortable: false,
        class: "w-1 text-right",
    },
];

const sessionHeaders = [
    {
        key: "id",
        label: __("ID"),
        sortable: true,
        class: "text-left w-12",
    },
    {
        key: "country_id",
        label: __("Flag"),
        sortable: true,
        class: "text-left w-12",
    },
    {
        key: "player_displayname",
        label: __("Display name"),
        sortable: true,
    },
    {
        key: "ip_address",
        sortable: true,
        label: __("IP Address"),
        class: "text-center",
    },
    {
        key: "session_started_at",
        label: __("Started"),
        sortable: true,
    },
    {
        key: "session_ended_at",
        label: __("Ended"),
        sortable: true,
    },
    {
        key: "server_id",
        label: __("Server"),
        sortable: true,
    },
    {
        key: "play_time",
        label: __("Play Time"),
        sortable: true,
        class: "text-right",
    },
    {
        key: "afk_time",
        label: __("Afk Time"),
        sortable: true,
        class: "text-right",
    },
    {
        key: "actions",
        label: "",
        sortable: false,
        class: "w-1 text-right",
    },
];

const altHeaders = [
    {
        key: "id",
        label: __("ID"),
        sortable: true,
        class: "text-left w-12",
    },
    {
        key: "country_id",
        label: __("Flag"),
        sortable: true,
        class: "text-left w-12",
    },
    {
        key: "username",
        label: __("Player"),
        sortable: true,
    },
    {
        key: "ip_address",
        sortable: true,
        label: __("Last IP Address"),
        class: "text-center",
    },
    {
        key: "last_seen_at",
        label: __("Last Seen"),
        sortable: true,
    },
    {
        key: "first_seen_at",
        label: __("First Seen"),
        sortable: true,
    },
    {
        key: "play_time",
        label: __("Play Time"),
        sortable: true,
        class: "text-right",
    },
    {
        key: "punishments_count",
        label: __("Punishments"),
        sortable: true,
        class: "text-right",
    },
    {
        key: "actions",
        label: "",
        sortable: false,
        class: "w-1 text-right",
    },
];

const showingUploadDialog = ref(false);

const fileRef = ref(null);
const uploadEvidenceAsFileForm = useForm({
    type: "media",
    file: null,
});

const triggerEvidenceAsFileUploadInput = () => {
    fileRef.value.click();
};

const handleEvidenceAsFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    uploadEvidenceAsFileForm.clearErrors();
    uploadEvidenceAsUrlForm.clearErrors();

    uploadEvidenceAsFileForm.file = file;
    uploadEvidenceAsFileForm.post(route("player.punishment.evidence.store", props.punishment.id), {
        onSuccess: () => {
            showingUploadDialog.value = false;
            uploadEvidenceAsFileForm.reset();
        },
    });
};

const uploadEvidenceAsUrlForm = useForm({
    type: "url",
    url: null,
});

const handleEvidenceAsUrlUpload = () => {
    uploadEvidenceAsFileForm.clearErrors();
    uploadEvidenceAsUrlForm.clearErrors();

    uploadEvidenceAsUrlForm.post(route("player.punishment.evidence.store", props.punishment.id), {
        onSuccess: () => {
            showingUploadDialog.value = false;
            uploadEvidenceAsUrlForm.reset();
        },
    });
};

const showingPardonForm = ref(false);
const pardonForm = useForm({
    reason: "",
});

function reloadPageWithTimeout() {
    setTimeout(() => {
        location.reload();
    }, 5000);
}

// Breadcrumb data stored in JSON format
const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Punishments'),
        url: route('player.punishment.index'),
        current: false
    },
    {
        text: '#' + props.punishment.id.toString(),
        current: true
    }
];
</script>

<template>
    <AppLayout>
        <AppHead :title="__('Punishments')" />

        <AppBreadcrumb class="max-w-7xl mx-auto" :items="breadcrumbItems" />

        <div class="px-2 py-4 mx-auto md:py-4 md:px-10 max-w-7xl">
            <div class="flex flex-col md:flex-row justify-between mb-8">
                <div class="flex items-center">
                    <h1 class="text-lg font-bold md:text-3xl">
                        {{
                            __("Punishment #:id", {
                                id: punishment.id,
                                punish: punishment.type.value,
                            })
                        }}
                    </h1>
                    <span class="ml-3 text-lg">
                        <CommonStatusBadge :status="punishment.type.value" />
                        <CommonStatusBadge :status="punishment.is_active ? 'active' : 'inactive'" />
                        <CommonStatusBadge v-if="punishment.is_active && punishment.end_at == null" status="permanent" />
                    </span>
                </div>

                <div class="flex space-x-2">
                    <button
                        v-if="permissions['canPardon'] && punishment.is_active"
                        class="inline-flex items-center px-4 py-2 bg-success-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-success-600 active:bg-success-700 focus:outline-none focus:border-success-800 focus:shadow-outline-green transition ease-in-out duration-150"
                        @click="showingPardonForm = true"
                    >
                        <span>{{ __("Pardon") }}</span>
                    </button>
                </div>
            </div>
            <div class="flex flex-col space-y-8">
                <!-- Details Start -->
                <div class="grid w-full gap-2 md:grid-cols-3">
                    <Card class="md:col-span-2">
                        <CardContent class="grid-cols-3 gap-6 leading-8 md:grid">
                            <h3 class="col-span-3 -mb-4 mt-4 text-lg font-bold">
                                {{ __("Punishment Details") }}
                            </h3>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Type") }}
                                </p>
                                <p class="font-bold">
                                    <CommonStatusBadge :status="punishment.type.value" />
                                </p>
                            </div>

                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("ID") }}
                                </p>
                                <p class="font-bold">
                                    {{ punishment.id }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Status") }}
                                </p>
                                <p class="font-bold">
                                    <CommonStatusBadge :status="punishment.is_active ? 'active' : 'inactive'" />
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Player") }}
                                </p>
                                <div v-if="punishment.uuid && punishment.victim_player">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8">
                                            <img class="w-8 h-8" :src="punishment.victim_player.avatar_url" alt="" />
                                        </div>
                                        <div class="ml-2">
                                            <Link
                                                v-tippy
                                                as="a"
                                                :href="route('player.show', punishment.victim_player.uuid)"
                                                class="font-medium text-foreground cursor-pointer focus:outline-none hover:underline"
                                                :content="punishment.victim_player.uuid"
                                            >
                                                <span v-if="punishment.victim_player.username" class="font-extrabold text-foreground">{{ punishment.victim_player.username }}</span>
                                                <span v-else class="italic text-error-500">{{ __("Unknown") }}</span>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                                <div v-else-if="punishment.uuid">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8">
                                            <img class="w-8 h-8" :src="route('player.avatar.get', punishment.uuid)" alt="" />
                                        </div>
                                        <div class="ml-2">
                                            <div v-tippy class="font-medium text-foreground" :content="punishment.uuid">
                                                <span class="italic text-error-500">{{ __("Unknown") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="flex items-center space-x-2">
                                    <img class="w-8 h-8" src="/images/pc_head.png" alt="" />
                                    <span class="text-sm font-bold text-foreground">{{ __("IP :punish", { punish: punishment.type.key }) }} </span>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{
                                        __("IP :punish", {
                                            punish: punishment.type.value,
                                        })
                                    }}
                                </p>
                                <p class="font-bold">
                                    {{ punishment.is_ipban ? __("Yes") : __("No") }}
                                </p>
                            </div>
                            <div v-if="canShowMaskedIp">
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("IP Address") }}
                                </p>
                                <p class="font-bold">
                                    <span v-if="punishment.ip_address || punishment.masked_ip_address">
                                        <a class="hover:underline" target="_blank" :href="`https://check-host.net/ip-info?host=${punishment.ip_address || punishment.masked_ip_address}`">{{
                                            punishment.ip_address || punishment.masked_ip_address
                                        }}</a>
                                    </span>
                                    <span v-else>{{ __("None") }}</span>
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Country") }}
                                </p>
                                <div class="flex items-center font-bold">
                                    <img :src="punishment.country.photo_path" :alt="punishment.country.iso_code" class="w-8 h-8 mr-2" />
                                    {{ punishment.country.name }}
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Date") }}
                                </p>
                                <div class="flex flex-col font-bold">
                                    <span>
                                        {{ formatTimeAgoToNow(punishment.start_at) }}
                                    </span>
                                    <span class="text-xs text-foreground">
                                        {{ formatToDayDateString(punishment.start_at) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Expires") }}
                                </p>
                                <div v-if="punishment.end_at" class="flex flex-col font-bold">
                                    <span>
                                        {{ formatTimeAgoToNow(punishment.end_at) }}
                                    </span>
                                    <span class="text-xs text-foreground">
                                        {{ formatToDayDateString(punishment.end_at) }}
                                    </span>
                                </div>
                                <div v-else class="font-bold">
                                    {{ __("Never") }}
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Reason") }}
                                </p>
                                <p class="mt-1 font-bold leading-normal">
                                    {{ punishment.reason }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Notes") }}
                                </p>
                                <p class="mt-1 font-bold leading-normal">
                                    {{ punishment.notes ?? __("-") }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Server Scope") }}
                                </p>
                                <p class="font-bold">
                                    {{ punishment.server_scope }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Punished by") }}
                                </p>
                                <div>
                                    <div v-if="punishment.creator_uuid">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-8 h-8">
                                                <img class="w-8 h-8" :src="punishment.creator_player ? punishment.creator_player.avatar_url : route('player.avatar.get', punishment.creator_uuid)" alt="" />
                                            </div>
                                            <div class="ml-2">
                                                <Link
                                                    v-tippy
                                                    as="a"
                                                    :href="punishment.creator_player ? route('player.show', punishment.creator_player.uuid) : '#'"
                                                    class="font-medium text-foreground cursor-pointer focus:outline-none hover:underline"
                                                    :content="punishment.creator_uuid"
                                                >
                                                    <span v-if="punishment.creator_player?.username || punishment.creator_username" class="font-extrabold text-foreground">{{
                                                        punishment.creator_player?.username || punishment.creator_username
                                                    }}</span>
                                                    <span v-else class="italic text-error-500">{{ __("Unknown") }}</span>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center space-x-2">
                                        <img class="w-8 h-8" src="/images/console_head.png" alt="" />
                                        <span v-if="punishment.creator_username && punishment.creator_username !== 'Console'" class="text-sm font-bold text-foreground">
                                            {{ punishment.creator_username }}
                                        </span>
                                        <span v-else class="text-sm font-bold text-foreground">{{ __("CONSOLE") }} </span>
                                    </div>
                                </div>
                            </div>

                            <template v-if="punishment.removed_at && (punishment.remover_uuid || punishment.remover_username)">
                                <div>
                                    <p class="font-semibold text-muted-foreground">
                                        {{ __("Pardon by") }}
                                    </p>
                                    <div>
                                        <div v-if="punishment.remover_uuid">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-8 h-8">
                                                    <img class="w-8 h-8" :src="punishment.remover_player ? punishment.remover_player.avatar_url : route('player.avatar.get', punishment.remover_uuid)" alt="" />
                                                </div>
                                                <div class="ml-2">
                                                    <Link
                                                        v-tippy
                                                        as="a"
                                                        :href="punishment.remover_player ? route('player.show', punishment.remover_player.uuid) : '#'"
                                                        class="font-medium text-foreground cursor-pointer focus:outline-none hover:underline"
                                                        :content="punishment.remover_uuid"
                                                    >
                                                        <span v-if="punishment.remover_player?.username || punishment.creator_username" class="font-extrabold text-foreground">{{
                                                            punishment.remover_player?.username || punishment.creator_username
                                                        }}</span>
                                                        <span v-else class="italic text-error-500">{{ __("Unknown") }}</span>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center space-x-2">
                                            <img class="w-8 h-8" src="/images/console_head.png" alt="" />
                                            <span v-if="punishment.remover_username && punishment.remover_username !== 'Console'" class="text-sm font-bold text-foreground">
                                                {{ punishment.remover_username }}
                                            </span>
                                            <span v-else class="text-sm font-bold text-foreground">{{ __("CONSOLE") }} </span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-semibold text-muted-foreground">
                                        {{ __("Pardon at") }}
                                    </p>
                                    <div class="flex flex-col font-bold">
                                        <span>
                                            {{ formatTimeAgoToNow(punishment.removed_at) }}
                                        </span>
                                        <span class="text-xs text-foreground">
                                            {{ formatToDayDateString(punishment.removed_at) }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-semibold text-muted-foreground">
                                        {{ __("Pardon Reason") }}
                                    </p>
                                    <p class="mt-1 font-bold leading-normal">
                                        {{ punishment.removed_reason ?? __("-") }}
                                    </p>
                                </div>
                            </template>

                            <div v-if="punishment.plugin_punishment_id">
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Plugin Punishment ID") }}
                                </p>
                                <p class="mt-1 font-bold leading-normal">
                                    {{ punishment.plugin_punishment_id }}
                                </p>
                            </div>

                            <div v-if="punishment.origin_server_name">
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Origin Server") }}
                                </p>
                                <p class="font-bold mt-0.5 leading-normal">
                                    {{ punishment.origin_server_name }}
                                </p>
                            </div>

                            <!-- Evidence -->
                            <div v-if="permissions['canViewEvidence']">
                                <p class="font-semibold text-muted-foreground">
                                    {{ __("Attached Evidence") }}
                                </p>
                                <p v-if="punishment.evidences <= 0 && !permissions['canCreateEvidence']">
                                    {{ "-" }}
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="evidence in punishment.evidences" :key="evidence.data.id" class="p-1.5 relative bg-background rounded group">
                                        <a
                                            v-tippy
                                            target="_blank"
                                            :title="
                                                __('View :file', {
                                                    file: evidence.data?.file_name || evidence.data?.url,
                                                })
                                            "
                                            :href="
                                                evidence.type === 'media'
                                                    ? route('player.punishment.evidence.show', {
                                                          playerPunishment: punishment.id,
                                                          evidence: evidence.data.id,
                                                          type: evidence.type,
                                                      })
                                                    : evidence.data.url
                                            "
                                        >
                                            <DocumentMagnifyingGlassIcon v-if="evidence.type === 'media'" class="w-6 h-6 p-0.5" />
                                            <LinkIcon v-else class="w-6 h-6 p-0.5" />
                                        </a>

                                        <Link
                                            v-if="permissions['canDeleteEvidence']"
                                            v-confirm="{
                                                message: 'Are you sure you want to delete this evidence?',
                                            }"
                                            v-tippy
                                            as="button"
                                            class="block"
                                            method="DELETE"
                                            :href="
                                                route('player.punishment.evidence.delete', {
                                                    playerPunishment: punishment.id,
                                                    evidence: evidence.data.id,
                                                    type: evidence.type,
                                                })
                                            "
                                        >
                                            <XCircleIcon
                                                class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:text-destructive absolute -top-2 -right-2 w-5 h-5 p-0.5 text-foreground cursor-pointer"
                                            />
                                        </Link>
                                    </div>
                                    <button
                                        v-if="permissions['canCreateEvidence']"
                                        :disabled="uploadEvidenceAsFileForm.processing || uploadEvidenceAsUrlForm.processing"
                                        class="p-1.5 bg-background rounded"
                                        @click="showingUploadDialog = true"
                                    >
                                        <PlusSmallIcon class="w-6 h-6 p-0.5" />
                                    </button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card class="md:col-span-1">
                        <CardContent>
                            <h3 class="text-lg font-bold mt-4">
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
                                                        [0.5, '#58D9F9'],
                                                        [0.75, '#FDDD60'],
                                                        [1, '#FF6E76'],
                                                    ],
                                                },
                                            },
                                            pointer: {
                                                icon: 'path://M12.8,0.7l12,40.1H0.7L12.8,0.7z',
                                                length: '12%',
                                                width: 20,
                                                offsetCenter: [0, '-60%'],
                                                itemStyle: {
                                                    color: 'auto',
                                                },
                                            },
                                            axisTick: {
                                                length: 12,
                                                lineStyle: {
                                                    color: 'auto',
                                                    width: 2,
                                                },
                                            },
                                            splitLine: {
                                                length: 20,
                                                lineStyle: {
                                                    color: 'auto',
                                                    width: 5,
                                                },
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
                                                },
                                            },
                                            title: {
                                                offsetCenter: [0, '-10%'],
                                                fontSize: 20,
                                            },
                                            detail: {
                                                fontSize: 30,
                                                offsetCenter: [0, '-35%'],
                                                valueAnimation: true,
                                                formatter: function (value) {
                                                    return Math.round(value * 100) + '';
                                                },
                                                color: 'inherit',
                                            },
                                            data: [
                                                {
                                                    value: punishment.insights?.score / 100 ?? 0,
                                                    name: __('Score'),
                                                },
                                            ],
                                        },
                                    ],
                                }"
                                height="250px"
                                :autoresize="true"
                            />

                            <div v-if="punishment.insights && punishment.insights.insights" class="flex items-center justify-center">
                                <ul class="text-sm leading-9 list-disc list-inside">
                                    <li v-for="insight in punishment.insights?.insights" :key="insight">
                                        {{ insight }}
                                    </li>
                                </ul>
                            </div>

                            <div v-if="!punishment.insights" class="flex items-center justify-center w-full min-h-[250px] text-sm italic text-center">
                                {{ __("No insights available! Check back later.") }}
                            </div>

                            <div v-if="punishment.insights && punishment.insights.status === 'generating'" class="flex items-center justify-center w-full min-h-[250px] text-sm italic text-center">
                                {{ __("Generating insights. Hang tight!") }} <br />
                                {{ __("Please refresh the page after few seconds.") }}
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <!-- Details End -->

                <!-- Punishment History Start -->
                <div>
                    <h3 class="mb-2 text-2xl font-bold">
                        {{ __("Last 5 Punishments") }}
                    </h3>
                    <Card class="w-full overflow-hidden">
                        <ArrayTable :url="route('player.punishment.show.history', punishment.id)" :header="punishmentHistoryHeaders" class="w-full">
                            <template #default="{ item }">
                                <DtRowItem>
                                    <Link
                                        v-tippy
                                        as="a"
                                        :href="route('player.punishment.show', item.id)"
                                        class="cursor-pointer focus:outline-none hover:underline"
                                        :class="item.is_active ? 'font-bold' : ''"
                                        :content="__('View details')"
                                    >
                                        {{ item.id }}
                                    </Link>
                                </DtRowItem>

                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div v-tippy class="flex-shrink-0 w-8 h-8 focus:outline-none" :content="item.country.name">
                                            <img class="w-8 h-8" :src="item.country.photo_path" alt="" />
                                        </div>
                                    </div>
                                </td>

                                <DtRowItem class="text-center">
                                    <CommonStatusBadge :status="item.type.value" />
                                </DtRowItem>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="item.uuid && item.victim_player">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-7 w-7">
                                                <img class="h-7 w-7" :src="item.victim_player.avatar_url" alt="" />
                                            </div>
                                            <div class="ml-2">
                                                <Link
                                                    v-tippy
                                                    as="a"
                                                    :href="route('player.show', item.victim_player.uuid)"
                                                    class="text-sm font-medium text-foreground cursor-pointer focus:outline-none hover:underline"
                                                    :content="item.victim_player.uuid"
                                                >
                                                    <span v-if="item.victim_player.username" class="font-extrabold text-foreground">{{ item.victim_player.username }}</span>
                                                    <span v-else class="italic text-error-500">{{ __("Unknown") }}</span>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else-if="item.uuid">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-7 w-7">
                                                <img class="h-7 w-7" :src="route('player.avatar.get', item.uuid)" alt="" />
                                            </div>
                                            <div class="ml-2">
                                                <div v-tippy class="text-sm font-medium text-foreground" :content="item.uuid">
                                                    <span class="italic text-error-500">{{ __("Unknown") }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center space-x-2">
                                        <img class="h-7 w-7" src="/images/pc_head.png" alt="" />
                                        <span class="text-xs font-bold text-foreground">{{ __("IP :punish", { punish: item.type.key }) }} </span>
                                    </div>
                                </td>

                                <DtRowItem v-if="canShowMaskedIp" class="text-center">
                                    <span v-if="item.ip_address || item.masked_ip_address">
                                        <a class="hover:underline" target="_blank" :href="`https://check-host.net/ip-info?host=${item.ip_address || item.masked_ip_address}`">{{
                                            item.ip_address || item.masked_ip_address
                                        }}</a>
                                    </span>
                                    <span v-else class="italic text-foreground">
                                        {{ __("None") }}
                                    </span>
                                </DtRowItem>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="item.creator_uuid">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-7 w-7">
                                                <img class="h-7 w-7" :src="item.creator_player ? item.creator_player.avatar_url : route('player.avatar.get', item.creator_uuid)" alt="" />
                                            </div>
                                            <div class="ml-2">
                                                <Link
                                                    v-tippy
                                                    as="a"
                                                    :href="item.creator_player ? route('player.show', item.creator_player.uuid) : '#'"
                                                    class="text-sm font-medium text-foreground cursor-pointer focus:outline-none hover:underline"
                                                    :content="item.creator_uuid"
                                                >
                                                    <span v-if="item.creator_player?.username || item.creator_username" class="font-extrabold text-foreground">{{
                                                        item.creator_player?.username || item.creator_username
                                                    }}</span>
                                                    <span v-else class="italic text-error-500">{{ __("Unknown") }}</span>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center space-x-2">
                                        <img class="h-7 w-7" src="/images/console_head.png" alt="" />
                                        <span v-if="item.creator_username && item.creator_username !== 'Console'" class="text-sm font-bold text-foreground">
                                            {{ item.creator_username }}
                                        </span>
                                        <span v-else class="text-sm font-bold text-foreground">{{ __("CONSOLE") }} </span>
                                    </div>
                                </td>

                                <DtRowItem>
                                    <p v-tippy :title="item.reason" class="w-20 truncate">
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

                                <td class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap">
                                    <Link v-tippy :title="__('View details')" as="a" :href="route('player.punishment.show', item.id)" class="inline-flex items-center justify-center text-primary hover:text-primary/75">
                                        <EyeIcon class="inline-block w-5 h-5" />
                                    </Link>
                                </td>
                            </template>
                        </ArrayTable>
                    </Card>
                </div>
                <!-- Punishment History End -->

                <!-- Past Sessions Start -->
                <div v-if="permissions['canViewSessions']">
                    <h3 class="mb-2 text-2xl font-bold">
                        {{ __("Last 5 Sessions") }}
                        <span class="text-sm">
                            {{ __("(before this punishment)") }}
                        </span>
                    </h3>
                    <Card class="w-full overflow-hidden">
                        <ArrayTable :url="route('player.punishment.show.session', punishment.id)" :header="sessionHeaders" class="w-full">
                            <template #default="{ item }">
                                <td class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap">
                                    {{ item.id }}
                                </td>

                                <td class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div v-tippy class="flex-shrink-0 w-8 h-8 focus:outline-none" :content="item.country.name">
                                            <img class="w-8 h-8" :src="item.country.photo_path" alt="" />
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap">
                                    <div class="flex items-center">
                                        <InertiaLink
                                            as="a"
                                            :href="
                                                route('player.intel.session.show', {
                                                    player: item.player_uuid,
                                                    session: item.id,
                                                })
                                            "
                                            class="text-sm font-medium text-foreground cursor-pointer focus:outline-none hover:underline"
                                        >
                                            <span class="font-extrabold text-foreground"> {{ item.player_displayname }} ({{ item.player_username }}) </span>
                                        </InertiaLink>
                                    </div>
                                </td>

                                <DtRowItem class="text-center">
                                    <span v-tippy class="whitespace-nowrap">
                                        <a v-if="item.player_ip_address" class="hover:underline" target="_blank" :href="`https://check-host.net/ip-info?host=${item.player_ip_address}`">{{ item.player_ip_address }}</a>
                                        <span v-else>-</span>
                                    </span>
                                </DtRowItem>

                                <DtRowItem>
                                    <span v-tippy class="whitespace-nowrap" :title="formatToDayDateString(item.session_started_at)">
                                        {{ formatTimeAgoToNow(item.session_started_at) }}
                                    </span>
                                </DtRowItem>

                                <DtRowItem>
                                    <span v-if="item.session_ended_at" v-tippy class="whitespace-nowrap" :title="formatToDayDateString(item.session_ended_at)">
                                        {{ formatTimeAgoToNow(item.session_ended_at) }}
                                    </span>
                                    <span v-else class="text-foreground">—</span>
                                </DtRowItem>

                                <DtRowItem>
                                    <span v-tippy class="whitespace-nowrap">
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
                                        :href="
                                            route('player.intel.session.show', {
                                                player: item.player_uuid,
                                                session: item.id,
                                            })
                                        "
                                        class="inline-flex items-center justify-center text-primary hover:text-primary/75"
                                        :title="__('View Session Details')"
                                    >
                                        <EyeIcon class="inline-block w-5 h-5" />
                                    </InertiaLink>
                                </td>
                            </template>
                        </ArrayTable>
                    </Card>
                </div>
                <!-- Past Sessions End -->

                <!-- Possible Alts Start -->
                <div v-if="permissions['canViewAlts']">
                    <h3 class="mb-2 text-2xl font-bold">
                        {{ __("Possible Alts") }}
                        <span class="text-sm">
                            {{ __("(players with similar ip address)") }}
                        </span>
                    </h3>
                    <Card class="w-full overflow-hidden">
                        <ArrayTable :url="route('player.punishment.show.alt', punishment.id)" :header="altHeaders" class="w-full">
                            <template #default="{ item }">
                                <td class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap">
                                    {{ item.id }}
                                </td>

                                <td class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div v-tippy class="flex-shrink-0 w-8 h-8 focus:outline-none" :content="item.country.name">
                                            <img class="w-8 h-8" :src="item.country.photo_path" alt="" />
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-7 w-7">
                                            <img class="h-7 w-7" :src="item.avatar_url" alt="" />
                                        </div>
                                        <div class="ml-2">
                                            <Link v-tippy as="a" :href="route('player.show', item.uuid)" class="text-sm font-medium text-foreground cursor-pointer focus:outline-none hover:underline" :content="item.uuid">
                                                <span v-if="item.username" class="font-extrabold text-foreground">{{ item.username }}</span>
                                                <span v-else class="italic text-error-500">{{ __("Unknown") }}</span>
                                            </Link>
                                        </div>
                                    </div>
                                </td>

                                <DtRowItem class="text-center">
                                    <span v-tippy class="whitespace-nowrap">
                                        <a v-if="item.ip_address" class="hover:underline" target="_blank" :href="`https://check-host.net/ip-info?host=${item.ip_address}`">{{ item.ip_address }}</a>
                                        <span v-else>-</span>
                                    </span>
                                </DtRowItem>

                                <DtRowItem>
                                    <span v-tippy class="whitespace-nowrap" :title="formatToDayDateString(item.last_seen_at)">
                                        {{ formatTimeAgoToNow(item.last_seen_at) }}
                                    </span>
                                </DtRowItem>

                                <DtRowItem>
                                    <span v-tippy class="whitespace-nowrap" :title="formatToDayDateString(item.first_seen_at)">
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
                                        :href="
                                            route('player.show', {
                                                player: item.uuid,
                                            })
                                        "
                                        class="inline-flex items-center justify-center text-primary hover:text-primary/75"
                                        :title="__('View Details')"
                                    >
                                        <EyeIcon class="inline-block w-5 h-5" />
                                    </InertiaLink>
                                </td>
                            </template>
                        </ArrayTable>
                    </Card>
                </div>
                <!-- Possible Alts End -->
            </div>
        </div>

        <Dialog :open="showingPardonForm" @update:open="showingPardonForm = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ __("Pardon") }}</DialogTitle>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <XInput id="reason" name="reason" v-model="pardonForm.reason" :label="__('Reason for Pardon')" :error="pardonForm.errors.reason" />
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showingPardonForm = false">
                        {{ __("Cancel") }}
                    </Button>
                    <LoadingButton
                        :loading="pardonForm.processing"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-success-500 border border-transparent rounded-md hover:bg-success-700 active:bg-success-900 focus:outline-none focus:border-success-900 focus:shadow-outline-gray"
                        @click="
                            () => {
                                pardonForm.delete(
                                    route('player.punishment.pardon', {
                                        playerPunishment: punishment.id,
                                    }),
                                    {
                                        onSuccess: () => {
                                            showingPardonForm = false;
                                            reloadPageWithTimeout();
                                        },
                                    }
                                );
                            }
                        "
                    >
                        {{ __("Pardon") }}
                    </LoadingButton>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="showingUploadDialog" @update:open="showingUploadDialog = $event">
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>{{ __("Attach Evidence") }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-6">
                    <!-- File Upload Section -->
                    <div>
                        <h4 class="mb-2 font-medium">{{ __("Attach as File") }}</h4>
                        <div class="flex items-center justify-center space-x-2">
                            <input v-if="permissions['canCreateEvidence']" ref="fileRef" type="file" class="hidden" @input="handleEvidenceAsFileUpload" />
                            <Button v-if="permissions['canCreateEvidence']" :disabled="uploadEvidenceAsFileForm.processing" size="lg" @click="triggerEvidenceAsFileUploadInput">
                                <PaperClipIcon v-if="!uploadEvidenceAsFileForm.processing" class="w-5 h-5" />
                                <Loader2Icon v-if="uploadEvidenceAsFileForm.processing" class="w-4 h-4 animate-spin" />
                                <span>{{ __("Upload File") }}</span>
                            </Button>
                        </div>
                        <p v-if="uploadEvidenceAsFileForm?.errors?.file" class="mt-1 text-xs text-destructive">
                            {{ uploadEvidenceAsFileForm?.errors?.file }}
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 text-muted-foreground bg-popover">
                                {{ __("OR") }}
                            </span>
                        </div>
                    </div>

                    <!-- URL Upload Section -->
                    <div>
                        <h4 class="mb-2 font-medium">{{ __("Attach as URL") }}</h4>
                        <div class="flex space-x-2">
                            <XInput
                                id="url"
                                name="url"
                                class="w-full"
                                :error="uploadEvidenceAsUrlForm.errors.url"
                                :placeholder="__('Enter URL')"
                                v-model="uploadEvidenceAsUrlForm.url"
                                :disabled="uploadEvidenceAsUrlForm.processing"
                                type="text"
                                @keyup.enter="handleEvidenceAsUrlUpload"
                            />
                            <LoadingButton :loading="uploadEvidenceAsUrlForm.processing" @click="handleEvidenceAsUrlUpload">
                                {{ __("Submit") }}
                            </LoadingButton>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showingUploadDialog = false">
                        {{ __("Close") }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
