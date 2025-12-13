<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { TrashIcon, ArrowPathIcon, EyeIcon } from '@heroicons/vue/24/outline';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import UserDisplayname from '@/Components/UserDisplayname.vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

defineProps({
    commandQueues: Object,
    filters: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Commands'),
        url: route('admin.command-queue.index'),
        current: true,
    }
];

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
    },
    {
        key: 'parsed_command',
        label: __('Command'),
        sortable: true,
        filterable: {
            type: 'text',
        },
    },
    {
        key: 'server_id',
        sortable: true,
        label: __('Server'),
        filterable: {
            key: 'server.name',
            type: 'text',
        }
    },
    {
        key: 'status',
        label: __('Status'),
        sortable: true,
        filterable: {
            type: 'multiselect',
            options: ['pending', 'running', 'failed', 'cancelled', 'deferred', 'completed'],
        },
    },
    {
        key: 'last_attempt_at',
        sortable: true,
        label: __('Last Attempted'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'execute_at',
        sortable: true,
        label: __('Execute At'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'tag',
        sortable: true,
        label: __('Tags'),
        filterable: {
            type: 'text',
        }
    },
    {
        key: 'player_id',
        sortable: true,
        label: __('For Player'),
        class: 'whitespace-nowrap',
        filterable: {
            key: 'player.username',
            type: 'text',
        }
    },
    {
        key: 'output',
        label: __('Output'),
        sortable: true,
        filterable: {
            type: 'text',
        }
    },
    {
        key: 'created_at',
        sortable: true,
        label: __('Created'),
        class: 'whitespace-nowrap',
    },
    {
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'w-1/12 text-right',
    },
];

let showingDetailsModel = ref(false);
let selectedCommandQueue = ref({});

const showDetails = (commandQueue) => {
    selectedCommandQueue.value = commandQueue;
    showingDetailsModel.value = true;
};
</script>

<template>
    <AdminLayout>
        <app-head :title="__('Command History')" />

        <div class="px-10 py-8 mx-auto text-foreground">
            <div class="flex justify-between mb-4">
                <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />
                <div class="flex">
                    <Button
                        v-if="can('create command_queues')"
                        as-child
                    >
                        <Link
                            v-confirm="{ message: 'Are you sure you wanna retry all failed & cancelled command queues? Please note that failed commands automatically get retried till they reach the max attempts.' }"
                            :href="route('admin.command-queue.retry')"
                            method="post"
                            as="button"
                        >
                            {{ __("Retry All Failed") }}
                        </Link>
                    </Button>
                </div>
            </div>

            <DataTable class="bg-card rounded-lg shadow" :header="headerRow" :data="commandQueues"
                :filters="filters">
                <template #default="{ item }">
                    <td class="px-4 py-4 text-sm font-medium text-foreground whitespace-nowrap dark:text-foreground">
                        {{ item.id }}
                    </td>

                    <DtRowItem class="text-left font-mono">
                        {{ item.parsed_command }}
                    </DtRowItem>

                    <DtRowItem class="text-left whitespace-nowrap">
                        {{ item.server.name }}
                    </DtRowItem>

                    <DtRowItem class="text-left">
                        <CommonStatusBadge :status="item.status.value" />
                    </DtRowItem>

                    <DtRowItem>
                        <div v-if="item.last_attempt_at" v-tippy class="whitespace-nowrap"
                            :title="formatToDayDateString(item.last_attempt_at)">
                            {{ formatTimeAgoToNow(item.last_attempt_at) }}
                        </div>
                        <div v-else class="text-foreground italic">
                            {{ "not yet" }}
                        </div>
                        <div class="text-xs dark:text-foreground">
                            {{ __("Attempts: :attempts/:max_attempts", {
                                attempts: item.attempts,
                                max_attempts: item.max_attempts ?? 1,
                            }) }}
                        </div>
                    </DtRowItem>

                    <DtRowItem>
                        <div v-if="item.execute_at" v-tippy
                            class="text-sm font-medium text-foreground dark:text-foreground whitespace-nowrap"
                            :title="formatToDayDateString(item.execute_at)">
                            {{ formatTimeAgoToNow(item.execute_at) }}
                        </div>
                        <div v-else class="text-foreground italic">
                            {{ "no delay" }}
                        </div>
                    </DtRowItem>

                    <DtRowItem class="text-left">
                        {{ item.tag ?? __("none") }}
                    </DtRowItem>

                    <DtRowItem class="text-center">
                        <Link v-if="item.player_id" v-tippy as="a"
                            :href="route('player.show', item.player.uuid)"
                            class="text-sm font-medium text-foreground dark:text-foreground focus:outline-none cursor-pointer hover:underline"
                            :content="item.player.uuid">
                            <span v-if="item.player.username" class="text-foreground dark:text-foreground">{{
                                item.player.username }}</span>
                            <span v-else class="text-error-500 italic">{{ __("Unknown") }}</span>
                        </Link>
                        <div v-else class="italic text-foreground">
                            {{ __("none") }}
                        </div>
                    </DtRowItem>

                    <DtRowItem class="text-left">
                        <span v-if="item.output" class="truncate" :title="item.output">
                            {{ item.output.length > 10 ? item.output.substring(0, 10) + '...' : item.output }}
                        </span>
                        <span v-else class="italic">
                            {{ __("none") }}
                        </span>
                    </DtRowItem>


                    <DtRowItem>
                        <div v-tippy class="text-sm font-medium text-foreground dark:text-foreground whitespace-nowrap"
                            :title="formatToDayDateString(item.created_at)">
                            {{ formatTimeAgoToNow(item.created_at) }}
                        </div>
                    </DtRowItem>

                    <td class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap">
                        <button v-tippy @click="showDetails(item)"
                            class="inline-flex items-center justify-center text-primary hover:text-primary"
                            :title="__('View Details')">
                            <EyeIcon class="inline-block w-5 h-5" />
                        </button>
                        <Link
                            v-if="can('create command_queues') && ['failed', 'cancelled'].includes(item.status.value)"
                            v-tippy as="button" method="post" :data="{ id: item.id }"
                            :href="route('admin.command-queue.retry')"
                            class="inline-flex items-center justify-center text-success-600 dark:text-success-500 hover:text-success-800 dark:hover:text-success-800"
                            :title="__('Retry Command')">
                            <ArrowPathIcon class="inline-block w-5 h-5" />
                        </Link>
                        <Link
                            v-if="can('delete command_queues') && ['pending', 'failed', 'cancelled', 'completed', 'deferred'].includes(item.status.value)"
                            v-confirm="{
                                message:
                                    'Are you sure you want to delete this command history?',
                            }" v-tippy as="button" method="DELETE" :data="{ id: item.id }"
                            :href="route('admin.command-queue.delete')"
                            class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
                            :title="__('Delete Command')">
                            <TrashIcon class="inline-block w-5 h-5" />
                        </Link>
                    </td>
                </template>
            </DataTable>
        </div>

        <Dialog :open="showingDetailsModel" @update:open="showingDetailsModel = $event">
            <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ __("Run Details") }}</DialogTitle>
                </DialogHeader>

                <div class="w-full text-sm grid grid-cols-2 gap-4 py-4">
                    <div class="col-span-2">
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Command") }}
                        </p>
                        <code class="block bg-muted px-3 py-2 rounded text-sm font-mono break-all">
                            {{ selectedCommandQueue.parsed_command }}
                        </code>
                    </div>
                    <div class="col-span-2">
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Output") }}
                        </p>
                        <code v-if="selectedCommandQueue.output" class="block bg-muted px-3 py-2 rounded text-sm font-mono break-all">
                            {{ selectedCommandQueue.output }}
                        </code>
                        <div v-else class="italic text-muted-foreground">
                            {{ __("none") }}
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("ID") }}
                        </p>
                        <p>{{ selectedCommandQueue.id }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Command ID") }}
                        </p>
                        <p v-if="selectedCommandQueue.command_id">
                            {{ selectedCommandQueue.command_id }}
                        </p>
                        <p v-else class="italic text-muted-foreground">
                            {{ __("none") }}
                        </p>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Server") }}
                        </p>
                        <p>{{ selectedCommandQueue.server.name }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Status") }}
                        </p>
                        <CommonStatusBadge :status="selectedCommandQueue.status.value" />
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Last Attempted") }}
                        </p>
                        <div class="flex items-center gap-2">
                            <span v-if="selectedCommandQueue.last_attempt_at" v-tippy class="whitespace-nowrap"
                                :title="formatToDayDateString(selectedCommandQueue.last_attempt_at)">
                                {{ formatTimeAgoToNow(selectedCommandQueue.last_attempt_at) }}
                            </span>
                            <span v-else class="text-muted-foreground italic">
                                {{ "not yet" }}
                            </span>
                            <span class="text-muted-foreground">•</span>
                            <span class="text-sm text-muted-foreground">
                                {{ __("Attempts: :attempts/:max_attempts", {
                                    attempts: selectedCommandQueue.attempts,
                                    max_attempts: selectedCommandQueue.max_attempts ?? 1,
                                }) }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Execute At") }}
                        </p>
                        <div>
                            <span v-if="selectedCommandQueue.execute_at" v-tippy
                                class="text-sm font-medium whitespace-nowrap"
                                :title="formatToDayDateString(selectedCommandQueue.execute_at)">
                                {{ formatTimeAgoToNow(selectedCommandQueue.execute_at) }}
                            </span>
                            <span v-else class="text-muted-foreground italic">
                                {{ "no delay" }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("For Player") }}
                        </p>
                        <div v-if="selectedCommandQueue.player_id" class="flex items-center gap-2">
                            <img class="w-8 h-8 rounded" :src="selectedCommandQueue.player.avatar_url" alt="">
                            <Link v-tippy as="a" :href="route('player.show', selectedCommandQueue.player.uuid)"
                                class="font-medium hover:underline"
                                :content="selectedCommandQueue.player.uuid">
                                <span v-if="selectedCommandQueue.player.username" class="font-semibold">
                                    {{ selectedCommandQueue.player.username }}
                                </span>
                                <span v-else class="italic text-destructive">{{ __("Unknown") }}</span>
                            </Link>
                        </div>
                        <div v-else class="italic text-muted-foreground">
                            {{ __("none") }}
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("User") }}
                        </p>
                        <div v-if="selectedCommandQueue.user_id">
                            <Link :href="route('user.public.get', selectedCommandQueue.user.username)">
                                <UserDisplayname :user="selectedCommandQueue.user" icon-class="w-4 h-4"
                                    text-class="text-sm" />
                            </Link>
                        </div>
                        <div v-else class="italic text-muted-foreground">
                            {{ __("none") }}
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Tags") }}
                        </p>
                        <p>{{ selectedCommandQueue.tag ?? __("none") }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Created At") }}
                        </p>
                        <span v-tippy class="text-sm font-medium whitespace-nowrap"
                            :title="formatToDayDateString(selectedCommandQueue.created_at)">
                            {{ formatTimeAgoToNow(selectedCommandQueue.created_at) }}
                        </span>
                    </div>
                    <div class="col-span-2">
                        <p class="font-semibold text-foreground mb-1">
                            {{ __("Config") }}
                        </p>
                        <code v-if="selectedCommandQueue.config" class="block bg-muted px-3 py-2 rounded text-sm font-mono break-all">
                            {{ selectedCommandQueue.config }}
                        </code>
                        <div v-else class="italic text-muted-foreground">
                            {{ __("none") }}
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showingDetailsModel = false">
                        {{ __("Close") }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
