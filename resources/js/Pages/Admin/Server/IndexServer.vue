<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import { useStorage } from '@vueuse/core';
import {
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';
import AlertCard from '@/Components/AlertCard.vue';
import { nextTick, reactive, watchEffect } from 'vue';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();
const showBungeeServerAlert = useStorage('show-bungee-server-missing-alert', true);

const props = defineProps({
    canCreateBungeeServer: Boolean,
    servers: Object,
    filters: Object,
});

const headerRow = [
    {
        key: 'id',
        label: __('ID'),
        sortable: true,
        class: 'text-center font-bold',
    },
    {
        key: 'name',
        sortable: true,
        label: __('Name'),
    },
    {
        key: 'ip_address',
        label: __('IP:Port'),
        sortable: true,
    },
    {
        key: 'type',
        label: __('Type'),
        sortable: true,
    },
    {
        key: 'status',
        sortable: false,
        label: __('Status'),
    },
    {
        key: 'minecraft_version',
        sortable: true,
        label: __('Version'),
    },
    {
        key: 'created_at',
        sortable: true,
        label: __('Added'),
    },
    {
        key: 'actions',
        label: __('Actions'),
        sortable: false,
        class: 'w-1/12 text-right',
    },
];

const serverStatus = reactive({});
const serverWebQueryStatus = reactive({});

watchEffect(() => {
    // Check ping for each server for online status
    props.servers.data.forEach((server) => {
        axios
            .get(route('server.ping.get', server.id))
            .then(() => {
                nextTick(() => (serverStatus[server.id] = 1));
            })
            .catch(() => {
                nextTick(() => (serverStatus[server.id] = -1));
            });

        // Only do webquery if server is not bungee.
        if (server.type.value !== 5 && server.webquery_port) {
            axios
                .get(route('server.webquery.get', server.id))
                .then(() => {
                    nextTick(() => (serverWebQueryStatus[server.id] = 1));
                })
                .catch(() => {
                    nextTick(() => (serverWebQueryStatus[server.id] = -1));
                });
        } else {
            serverWebQueryStatus[server.id] = 0;
        }
    });
});
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Manage Servers')" />

    <div class="px-10 py-8 mx-auto text-gray-400">
      <AlertCard
        v-if="canCreateBungeeServer && showBungeeServerAlert"
        :close-button="true"
        text-color="text-light-blue-800 dark:text-light-blue-500"
        border-color="border-light-blue-500"
        @close="showBungeeServerAlert = false"
      >
        {{ __("You don't have Bungee/Proxy Server Added!") }}
        <template #body>
          {{
            __(
              "Adding Proxy server is recommended if you are adding more than 1 server to MineTrax, so that it can be used to show whole network player count in homepage."
            )
          }}

          <p class="italic text-gray-400 dark:text-gray-500">
            {{
              __(
                "Note: This is not an error. You can safely ignore this message if you don't want to add proxy server."
              )
            }}
          </p>
        </template>
      </AlertCard>

      <div class="flex justify-between mb-8">
        <h1
          class="font-bold text-3xl text-gray-500 dark:text-gray-300 flex items-center"
        >
          {{ __("Servers") }}
          <InertiaLink
            v-if="can('create servers')"
            v-tippy
            as="button"
            :title="
              __(
                'MineTrax automatically sync player stats every hour or as per schedule define in .env file. Click here to force a sync now.'
              )
            "
            :href="route('admin.server.force-scan')"
            method="post"
            class="ml-2 inline-flex items-center px-4 py-2 border-2 border-red-600 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest focus:outline-none transition ease-in-out duration-150 dark:text-red-500 dark:border-red-700 hover:border-red-300 dark:hover:border-red-500  hover:bg-red-500 hover:text-white dark:hover:text-white"
          >
            <span>{{ __("Sync Player Statistics") }}</span>
          </InertiaLink>
        </h1>
        <div class="flex">
          <InertiaLink
            v-if="can('create servers')"
            :href="route('admin.server.create')"
            class="mr-1 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Add") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Server") }}</span>
          </InertiaLink>
          <InertiaLink
            v-if="can('create servers') && canCreateBungeeServer"
            :href="route('admin.server.create-bungee')"
            class="inline-flex items-center px-4 py-2 border-2 border-gray-800 rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest focus:outline-none focus:border-gray-900 transition ease-in-out duration-150 dark:text-gray-300 dark:border-gray-700 dark:hover:border-gray-500"
          >
            <span>{{ __("Add Proxy Server") }}</span>
          </InertiaLink>
        </div>
      </div>

      <DataTable
        class="bg-white rounded shadow dark:bg-gray-800"
        :header="headerRow"
        :data="servers"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm text-center text-gray-800 whitespace-nowrap dark:text-gray-200 font-bold"
          >
            {{ item.id }}
          </td>

          <DtRowItem>
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <img
                  class="h-10 w-10"
                  :src="item.country.photo_path"
                  alt="Country Flag"
                >
              </div>
              <div class="ml-4">
                <div
                  class="text-sm font-medium text-gray-900 dark:text-gray-300"
                >
                  {{ item.name }}
                </div>
                <div
                  class="text-sm text-gray-500 dark:text-gray-400"
                >
                  {{ item.hostname }}
                </div>
              </div>
            </div>
          </DtRowItem>

          <DtRowItem>
            <div class="text-sm text-gray-900 dark:text-gray-300">
              {{ item.ip_address }} : {{ item.join_port }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              {{
                __("WebQuery: :webquery_port", {
                  webquery_port:
                    item.webquery_port || __("not set"),
                })
              }}
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ item.type.key }}
          </DtRowItem>

          <td class="px-4 space-y-1 whitespace-nowrap">
            <div class="flex">
              <span
                v-if="serverStatus[item.id] === 1"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-opacity-25 dark:text-green-400"
              >{{ __("Server Online") }}</span>
              <span
                v-else-if="serverStatus[item.id] === -1"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-opacity-25 dark:text-red-400"
              >{{ __("Server Offline") }}</span>
              <span
                v-else
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-opacity-25 dark:text-gray-400"
              >{{ __("Loading") }}...</span>
            </div>
            <div class="flex">
              <span
                v-if="serverWebQueryStatus[item.id] === 1"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-opacity-25 dark:text-green-400"
              >{{ __("WebQuery Online") }}</span>
              <span
                v-else-if="serverWebQueryStatus[item.id] === -1"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-opacity-25 dark:text-red-400"
              >{{ __("WebQuery Offline") }}</span>
              <span
                v-else-if="serverWebQueryStatus[item.id] === 0"
              />
              <span
                v-else
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-opacity-25 dark:text-gray-400"
              >{{ __("Loading") }}...</span>
            </div>
          </td>

          <DtRowItem>
            {{ item.minecraft_version }}
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
              v-tippy
              as="a"
              :title="__('View Server Intel')"
              :href="route('admin.server.show', item.id)"
              class="inline-flex items-center justify-center text-blue-500 hover:text-blue-800"
            >
              <EyeIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('update servers')"
              v-tippy
              as="a"
              :href="route('admin.server.edit', item.id)"
              class="inline-flex items-center justify-center text-yellow-600 dark:text-yellow-500 hover:text-yellow-800 dark:hover:text-yellow-800"
              :title="__('Edit Server')"
            >
              <PencilSquareIcon class="inline-block w-5 h-5" />
            </InertiaLink>
            <InertiaLink
              v-if="can('delete servers')"
              v-confirm="{
                title: 'Delete Server?',
                message:
                  'Are you sure you want to delete this Server permanently? Deleting a Server will also delete all of its associated data including all of its Player & Server Intel data. This action cannot be undone.',
              }"
              v-tippy
              as="button"
              method="DELETE"
              :href="route('admin.server.delete', item.id)"
              class="inline-flex items-center justify-center text-red-600 hover:text-red-900 focus:outline-none"
              :title="__('Delete Server')"
            >
              <TrashIcon class="inline-block w-5 h-5" />
            </InertiaLink>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
