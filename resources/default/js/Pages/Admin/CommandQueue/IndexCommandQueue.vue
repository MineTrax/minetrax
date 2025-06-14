<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { TrashIcon, ArrowPathIcon, EyeIcon } from '@heroicons/vue/24/outline';
import CommonStatusBadge from '@/Shared/CommonStatusBadge.vue';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
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

        <div class="px-10 py-8 mx-auto text-secondary-400">
            <div class="flex justify-between mb-4">
                <h1 class="text-3xl font-bold text-secondary-500 dark:text-secondary-300">
                    {{ __("Command History") }}
                </h1>
                <div class="flex">
                    <InertiaLink v-if="can('create command_queues')"
                        v-confirm="{ message: 'Are you sure you wanna retry all failed & cancelled command queues? Please note that failed commands automatically get retried till they reach the max attempts.' }"
                        :href="route('admin.command-queue.retry')" method="post" as="button"
                        class="mr-2 inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-success-500 border border-transparent rounded-md hover:bg-success-700 active:bg-success-900 focus:outline-none focus:border-success-900 focus:shadow-outline-gray">
                        {{ __("Retry All Failed") }}
                    </InertiaLink>
                </div>
            </div>

            <DataTable class="bg-white rounded shadow dark:bg-surface-800" :header="headerRow" :data="commandQueues"
                :filters="filters">
                <template #default="{ item }">
                    <td class="px-4 py-4 text-sm font-medium text-secondary-800 whitespace-nowrap dark:text-secondary-200">
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
                        <div v-else class="text-secondary-500 italic">
                            {{ "not yet" }}
                        </div>
                        <div class="text-xs dark:text-secondary-500">
                            {{ __("Attempts: :attempts/:max_attempts", {
                                attempts: item.attempts,
                                max_attempts: item.max_attempts ?? 1,
                            }) }}
                        </div>
                    </DtRowItem>

                    <DtRowItem>
                        <div v-if="item.execute_at" v-tippy
                            class="text-sm font-medium text-secondary-900 dark:text-secondary-300 whitespace-nowrap"
                            :title="formatToDayDateString(item.execute_at)">
                            {{ formatTimeAgoToNow(item.execute_at) }}
                        </div>
                        <div v-else class="text-secondary-500 italic">
                            {{ "no delay" }}
                        </div>
                    </DtRowItem>

                    <DtRowItem class="text-left">
                        {{ item.tag ?? __("none") }}
                    </DtRowItem>

                    <DtRowItem class="text-center">
                        <inertia-link v-if="item.player_id" v-tippy as="a"
                            :href="route('player.show', item.player.uuid)"
                            class="text-sm font-medium text-secondary-900 dark:text-secondary-400 focus:outline-none cursor-pointer hover:underline"
                            :content="item.player.uuid">
                            <span v-if="item.player.username" class="text-secondary-600 dark:text-secondary-400">{{
                                item.player.username }}</span>
                            <span v-else class="text-error-500 italic">{{ __("Unknown") }}</span>
                        </inertia-link>
                        <div v-else class="italic text-secondary-500">
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
                        <div v-tippy class="text-sm font-medium text-secondary-900 dark:text-secondary-300 whitespace-nowrap"
                            :title="formatToDayDateString(item.created_at)">
                            {{ formatTimeAgoToNow(item.created_at) }}
                        </div>
                    </DtRowItem>

                    <td class="px-6 py-4 space-x-2 text-sm font-medium text-right whitespace-nowrap">
                        <button v-tippy @click="showDetails(item)"
                            class="inline-flex items-center justify-center text-primary-500 hover:text-primary-800"
                            :title="__('View Details')">
                            <EyeIcon class="inline-block w-5 h-5" />
                        </button>
                        <InertiaLink
                            v-if="can('create command_queues') && ['failed', 'cancelled'].includes(item.status.value)"
                            v-tippy as="button" method="post" :data="{ id: item.id }"
                            :href="route('admin.command-queue.retry')"
                            class="inline-flex items-center justify-center text-success-600 dark:text-success-500 hover:text-success-800 dark:hover:text-success-800"
                            :title="__('Retry Command')">
                            <ArrowPathIcon class="inline-block w-5 h-5" />
                        </InertiaLink>
                        <InertiaLink
                            v-if="can('delete command_queues') && ['pending', 'failed', 'cancelled', 'completed', 'deferred'].includes(item.status.value)"
                            v-confirm="{
                                message:
                                    'Are you sure you want to delete this command history?',
                            }" v-tippy as="button" method="DELETE" :data="{ id: item.id }"
                            :href="route('admin.command-queue.delete')"
                            class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
                            :title="__('Delete Command')">
                            <TrashIcon class="inline-block w-5 h-5" />
                        </InertiaLink>
                    </td>
                </template>
            </DataTable>
        </div>

        <JetDialogModal :show="showingDetailsModel" @close="showingDetailsModel = false">
            <template #title>
                <h3 class="text-lg font-bold">
                    {{ __("Run Details") }}
                </h3>
            </template>

            <template #content>
                <div class="w-full text-sm grid-cols-2 gap-6 leading-7 md:col-span-2 md:grid">
                    <div class="col-span-2">
                        <p class="font-semibold text-secondary-500">
                            {{ __("Command") }}
                        </p>
                        <code>
                            {{ selectedCommandQueue.parsed_command }}
                        </code>
                    </div>
                    <div class="col-span-2">
                        <p class="font-semibold text-secondary-500">
                            {{ __("Output") }}
                        </p>
                        <code v-if="selectedCommandQueue.output">
                            {{ selectedCommandQueue.output }}
                        </code>
                        <div v-else class="italic text-secondary-500">
                            {{ __("none") }}
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("ID") }}
                        </p>
                        <p class="">
                            {{ selectedCommandQueue.id }}
                        </p>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("Command ID") }}
                        </p>
                        <p v-if="selectedCommandQueue.command_id">
                            {{ selectedCommandQueue.command_id }}
                        </p>
                        <p v-else class="italic text-secondary-500">
                            {{ __("none") }}
                        </p>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("Server") }}
                        </p>
                        <p class="">
                            {{ selectedCommandQueue.server.name }}
                        </p>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("Status") }}
                        </p>
                        <CommonStatusBadge :status="selectedCommandQueue.status.value" />
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("Last Attempted") }}
                        </p>
                        <div class="flex items-center">
                            <div v-if="selectedCommandQueue.last_attempt_at" v-tippy class="whitespace-nowrap"
                                :title="formatToDayDateString(selectedCommandQueue.last_attempt_at)">
                                {{ formatTimeAgoToNow(selectedCommandQueue.last_attempt_at) }}
                            </div>
                            <div v-else class="text-secondary-500 italic">
                                {{ "not yet" }}
                            </div>
                            &nbsp;&#9679;&nbsp;
                            <div class="text-sm">
                                {{ __("Attempts: :attempts/:max_attempts", {
                                    attempts: selectedCommandQueue.attempts,
                                    max_attempts: selectedCommandQueue.max_attempts ?? 1,
                                }) }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("Execute At") }}
                        </p>
                        <div class="flex items-center">
                            <div v-if="selectedCommandQueue.execute_at" v-tippy
                                class="text-sm font-medium text-secondary-900 dark:text-secondary-300 whitespace-nowrap"
                                :title="formatToDayDateString(selectedCommandQueue.execute_at)">
                                {{ formatTimeAgoToNow(selectedCommandQueue.execute_at) }}
                            </div>
                            <div v-else class="text-secondary-500 italic">
                                {{ "no delay" }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("For Player") }}
                        </p>
                        <div>
                            <div v-if="selectedCommandQueue.player_id" class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8">
                                    <img class="w-8 h-8" :src="selectedCommandQueue.player.avatar_url" alt="">
                                </div>
                                <div class="ml-2">
                                    <Link v-tippy as="a" :href="route('player.show', selectedCommandQueue.player.uuid)"
                                        class="font-medium text-secondary-900 cursor-pointer dark:text-secondary-200 focus:outline-none hover:underline"
                                        :content="selectedCommandQueue.player.uuid">
                                    <span v-if="selectedCommandQueue.player.username"
                                        class="font-extrabold text-secondary-700 dark:text-secondary-300">{{
                                            selectedCommandQueue.player.username }}</span>
                                    <span v-else class="italic text-error-500">{{ __("Unknown") }}</span>
                                    </Link>
                                </div>
                            </div>
                            <div v-else class="italic text-secondary-500">
                                {{ __("none") }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("User") }}
                        </p>
                        <div>
                            <div v-if="selectedCommandQueue.user_id" class="flex items-center">
                                <Link :href="route('user.public.get', selectedCommandQueue.user.username)">
                                    <UserDisplayname :user="selectedCommandQueue.user" icon-class="w-4 h-4"
                                        text-class="text-sm" />
                                </Link>
                            </div>
                            <div v-else class="italic text-secondary-500">
                                {{ __("none") }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("Tags") }}
                        </p>
                        <p class="">
                            {{ selectedCommandQueue.tag ?? __("none") }}
                        </p>
                    </div>
                    <div>
                        <p class="font-semibold text-secondary-500">
                            {{ __("Created At") }}
                        </p>
                        <div class="flex items-center">
                            <div v-tippy class="text-sm font-medium text-secondary-900 dark:text-secondary-300 whitespace-nowrap"
                                :title="formatToDayDateString(selectedCommandQueue.created_at)">
                                {{ formatTimeAgoToNow(selectedCommandQueue.created_at) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <p class="font-semibold text-secondary-500">
                            {{ __("Config") }}
                        </p>
                        <code v-if="selectedCommandQueue.config">
                            {{ selectedCommandQueue.config }}
                        </code>
                        <div v-else class="italic text-secondary-500">
                            {{ __("none") }}
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <JetSecondaryButton @click="showingDetailsModel = false">
                    {{ __("Close") }}
                </JetSecondaryButton>
            </template>
        </JetDialogModal>
    </AdminLayout>
</template>
