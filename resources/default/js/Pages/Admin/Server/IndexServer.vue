<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import DataTable from '@/Components/DataTable/DataTable.vue';
import DtRowItem from '@/Components/DataTable/DtRowItem.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import {
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowPathIcon,
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

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Servers'),
        current: true,
    }
];

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
        filterable: {
            type: 'text',
        }
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
            .then((data) => {
                nextTick(() => {
                    serverStatus[server.id] = {
                        online: 1,
                        players: data.data?.players?.online,
                        max: data.data?.players?.max
                    };
                });
            })
            .catch(() => {
                nextTick(() => (serverStatus[server.id] = { online: 0 }));
            });

        // Only do webquery if server has webquery port set
        if (server.webquery_port) {
            axios
                .get(route('server.webquery.ping', server.id))
                .then((data) => {
                    nextTick(() => {
                        serverWebQueryStatus[server.id] = {
                            online: 1,
                            players: data.data?.online_players,
                            max: data.data?.max_players
                        };
                    });
                })
                .catch(() => {
                    nextTick(() => (serverWebQueryStatus[server.id] = {
                        online: 0
                    }));
                });
        } else {
            serverWebQueryStatus[server.id] = {
                online: -1
            };
        }
    });
});

function getServerStatus(serverId) {
    return serverStatus[serverId] || null;
}

function getServerWebQueryStatus(serverId) {
    return serverWebQueryStatus[serverId] || null;
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Manage Servers')" />

    <div class="px-10 py-8 mx-auto text-foreground">
      <AlertCard
        v-if="canCreateBungeeServer && showBungeeServerAlert"
        :close-button="true"
        text-color="text-primary dark:text-primary"
        border-color="border-primary"
        @close="showBungeeServerAlert = false"
      >
        {{ __("You don't have Bungee/Proxy Server Added!") }}
        <template #body>
          {{
            __(
              "Adding Proxy server is recommended if you are adding more than 1 server to MineTrax, so that it can be used to show whole network player count in homepage."
            )
          }}

          <p class="italic text-foreground dark:text-foreground">
            {{
              __(
                "Note: This is not an error. You can safely ignore this message if you don't want to add proxy server."
              )
            }}
          </p>
        </template>
      </AlertCard>

      <div class="flex justify-between mb-4">
        <AppBreadcrumb class="mt-0" breadcrumb-class="max-w-none px-0 md:px-0" :items="breadcrumbItems" />
        <div class="flex gap-2">
          <Button
            v-if="can('create servers')"
            v-confirm="{
                title: 'Force Resync!',
              message:
                'MineTrax automatically sync player stats every 30 minutes or as per schedule defined in .env file. You are about to force resync player statistics for all servers. Are you sure you want to proceed?',
            }"
            variant="destructive"
            size="icon"
            as-child
          >
            <Link
              :href="route('admin.server.force-scan')"
              method="post"
              as="button"
            >
              <ArrowPathIcon class="w-5 h-5" />
            </Link>
          </Button>
          <Button
            v-if="can('create servers')"
            as-child
          >
            <Link :href="route('admin.server.create')">
              {{ __("Add Server") }}
            </Link>
          </Button>
          <Button
            v-if="can('create servers') && canCreateBungeeServer"
            variant="outline"
            as-child
          >
            <Link :href="route('admin.server.create-bungee')">
              {{ __("Add Proxy Server") }}
            </Link>
          </Button>
        </div>
      </div>

      <DataTable
        class="bg-card rounded-lg shadow"
        :header="headerRow"
        :data="servers"
        :filters="filters"
      >
        <template #default="{ item }">
          <td
            class="px-4 py-4 text-sm text-center text-foreground whitespace-nowrap dark:text-foreground font-bold"
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
                  class="text-sm font-medium text-foreground dark:text-foreground"
                >
                  {{ item.name }}
                </div>
                <div
                  class="text-sm text-foreground dark:text-foreground"
                >
                  {{ item.hostname }}
                </div>
              </div>
            </div>
          </DtRowItem>

          <DtRowItem>
            <div class="group">
              <div class="text-sm filter blur-sm text-foreground dark:text-foreground group-hover:blur-none duration-300 cursor-text">
                {{ item.masked_ip_address }} : {{ item.join_port }}
              </div>
              <div class="text-sm text-foreground dark:text-foreground filter blur-sm group-hover:blur-none duration-300 cursor-text">
                {{
                  __("WebQuery: :webquery_port", {
                    webquery_port:
                      item.webquery_port || __("not set"),
                  })
                }}
              </div>
            </div>
          </DtRowItem>

          <DtRowItem>
            {{ item.type.key == 'Bungee' ? __('Proxy') : item.type.key }}
          </DtRowItem>

          <td class="px-4 space-y-1 whitespace-nowrap">
            <div class="flex">
              <span
                v-if="!getServerStatus(item.id)"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-surface-100 text-foreground dark:bg-opacity-25 dark:text-foreground"
              >{{ __("Loading") }}...</span>
              <span
                v-else-if="getServerStatus(item.id).online === 1"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-success-100 text-success-800 dark:bg-opacity-25 dark:text-success-400"
              >
                {{ __("Server") }}
                🟢
                {{ getServerStatus(item.id).players + '/' + getServerStatus(item.id).max }}
              </span>
              <span
                v-else-if="getServerStatus(item.id).online === 0"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-error-100 text-error-800 dark:bg-opacity-25 dark:text-error-400"
              >
                {{ __("Server") }}
                🔴
              </span>
            </div>
            <div class="flex">
              <span
                v-if="!getServerWebQueryStatus(item.id)"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-surface-100 text-foreground dark:bg-opacity-25 dark:text-foreground"
              >{{ __("Loading") }}...</span>
              <span
                v-else-if="getServerWebQueryStatus(item.id).online === 1"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-success-100 text-success-800 dark:bg-opacity-25 dark:text-success-400"
              >{{ __("WebQuery") }}
                🟢
                {{ getServerWebQueryStatus(item.id).players + '/' + getServerWebQueryStatus(item.id).max }}
              </span>
              <span
                v-else-if="getServerWebQueryStatus(item.id).online === 0"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-error-100 text-error-800 dark:bg-opacity-25 dark:text-error-400"
              >
                {{ __("WebQuery") }}
                🔴
              </span>
              <span
                v-else-if="getServerWebQueryStatus(item.id).online === -1"
              />
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
            <div v-if="item?.settings?.is_deleting">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-error-100 text-error-800 dark:bg-opacity-25 dark:text-error-400"
              >
                {{ __("Deleting...") }}
              </span>
            </div>
            <div
              v-else
              class="flex space-x-2"
            >
              <Link
                v-tippy
                as="a"
                :title="__('View Server Intel')"
                :href="route('admin.server.show', item.id)"
                class="inline-flex items-center justify-center text-primary hover:text-primary"
              >
                <EyeIcon class="inline-block w-5 h-5" />
              </Link>
              <Link
                v-if="can('update servers')"
                v-tippy
                as="a"
                :href="route('admin.server.edit', item.id)"
                class="inline-flex items-center justify-center text-warning-600 dark:text-warning-500 hover:text-warning-800 dark:hover:text-warning-800"
                :title="__('Edit Server')"
              >
                <PencilSquareIcon class="inline-block w-5 h-5" />
              </Link>
              <Link
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
                class="inline-flex items-center justify-center text-error-600 hover:text-error-900 focus:outline-none"
                :title="__('Delete Server')"
              >
                <TrashIcon class="inline-block w-5 h-5" />
              </Link>
            </div>
          </td>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
