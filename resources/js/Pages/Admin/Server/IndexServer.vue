<template>
  <app-layout>
    <app-head :title="__('Servers Administration')" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div
        v-if="canCreateBungeeServer"
        class="mb-4 bg-white dark:bg-cool-gray-800 border-t-4 border-orange-500 rounded-b text-orange-900 dark:text-orange-500 px-4 py-3 shadow"
        role="alert"
      >
        <div class="flex">
          <div class="py-1">
            <svg
              class="fill-current h-6 w-6 text-orange-500 mr-4"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path
                d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"
              />
            </svg>
          </div>
          <div>
            <p class="font-bold">
              {{ __("You don't have Bungee/Proxy Server Added!") }}
            </p>
            <p class="text-sm">
              {{ __("When a bungee server is not added. Player List Box and Player Status Box (if enabled from settings), use first added server as default query server.") }}
            </p>
          </div>
        </div>
      </div>

      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300 flex items-center">
          {{ __("Servers") }}
          <inertia-link
            v-if="can('create servers')"
            v-tippy
            as="button"
            title="MineTrax automatically scan all servers every hour for new players and statistics updates. Click here to force a scan now. Use it rarely."
            :href="route('admin.server.force-scan')"
            method="post"
            class="ml-2 inline-flex items-center px-4 py-2 border border-2 border-red-600 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest focus:outline-none focus:border-red-800 transition ease-in-out duration-150 dark:text-red-500 dark:border-red-700 dark:hover:border-red-500"
          >
            <span>{{ __("Rescan all servers") }}</span>
          </inertia-link>
        </h1>
        <div class="flex">
          <inertia-link
            v-if="can('create servers') && canCreateBungeeServer"
            :href="route('admin.server.create-bungee')"
            class="mr-1 inline-flex items-center px-4 py-2 border border-2 border-gray-800 rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest focus:outline-none focus:border-gray-900 transition ease-in-out duration-150 dark:text-gray-300 dark:border-gray-700 dark:hover:border-gray-500"
          >
            <span>{{ __("Add Bungee Server") }}</span>
          </inertia-link>

          <inertia-link
            v-if="can('create servers')"
            :href="route('admin.server.create')"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Add") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Server") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 dark:border-none sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-cool-gray-800 text-gray-500 dark:text-gray-300">
                  <tr>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("#") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Name") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("IP:Port") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Type") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Status") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Version") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Last Scanned") }}
                    </th>
                    <th
                      scope="col"
                      class="relative px-6 py-3"
                    >
                      <span class="sr-only">{{ __("Edit") }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-cool-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr
                    v-for="server in servers.data"
                    :key="server.id"
                  >
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ server.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <img
                            class="h-10 w-10"
                            :src="server.country.photo_path"
                            alt="Country Flag"
                          >
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                            {{ server.name }}
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ server.hostname }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900 dark:text-gray-300">
                        {{ server.ip_address }} : {{
                          server.join_port
                        }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __("WebQuery: :webquery_port", { webquery_port: server.webquery_port || __("not set") }) }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ server.type.key }}
                    </td>
                    <td class="px-6 py-4 space-y-1 whitespace-nowrap">
                      <div class="flex">
                        <span
                          v-if="serverStatus[server.id] === 1"
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-opacity-25 dark:text-green-400"
                        >{{ __("Server Online") }}</span>
                        <span
                          v-else-if="serverStatus[server.id] === -1"
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-opacity-25 dark:text-red-400"
                        >{{ __("Server Offline") }}</span>
                        <span
                          v-else
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-opacity-25 dark:text-gray-400"
                        >{{ __("Loading") }}...</span>
                      </div>
                      <div class="flex">
                        <span
                          v-if="serverWebQueryStatus[server.id] === 1"
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-opacity-25 dark:text-green-400"
                        >{{ __("WebQuery Online") }}</span>
                        <span
                          v-else-if="serverWebQueryStatus[server.id] === -1"
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-opacity-25 dark:text-red-400"
                        >{{ __("WebQuery Offline") }}</span>
                        <span
                          v-else-if="serverWebQueryStatus[server.id] === 0"
                        />
                        <span
                          v-else
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-opacity-25 dark:text-gray-400"
                        >{{ __("Loading") }}...</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ server.minecraft_version }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <span
                        v-if="server.type.value === 5"
                        class="italic"
                      >
                        {{ __("not applicable") }}
                      </span>
                      <span v-else>
                        {{
                          server.last_scanned_at ? formatDistanceToNowStrict(new Date(server.last_scanned_at), {addSuffix: true}) : "not scanned yet"
                        }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium dark:text-gray-300">
                      <inertia-link
                        as="a"
                        :href="route('admin.server.show', server.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        {{ __("View Intel") }}
                      </inertia-link>
                      /
                      <inertia-link
                        v-if="can('update servers')"
                        as="a"
                        :href="route('admin.server.edit', server.id)"
                        class="text-yellow-600 hover:text-yellow-900"
                      >
                        {{ __("Edit") }}
                      </inertia-link>
                      /
                      <button
                        v-if="can('delete servers')"
                        class="text-red-600 hover:text-red-900 focus:outline-none"
                        @click="confirmServerDeletion(server.id)"
                      >
                        {{ __("Delete") }}
                      </button>
                    </td>
                  </tr>

                  <tr v-if="servers.data.length === 0">
                    <td
                      class="border-t px-6 py-4 text-center dark:text-gray-400 dark:border-gray-700"
                      colspan="8"
                    >
                      {{ __("No servers found.") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="servers.links" />
    </div>

    <jet-confirmation-modal
      :show="serverBeingDeleted"
      @close="serverBeingDeleted = null"
    >
      <template #title>
        {{ __("Delete Server") }}
      </template>

      <template #content>
        {{ __("Are you sure you would like to delete this Server? All data related to this server will be deleted too.") }}
      </template>

      <template #footer>
        <jet-secondary-button @click.native="serverBeingDeleted = null">
          {{ __("Nevermind") }}
        </jet-secondary-button>

        <jet-danger-button
          class="ml-2"
          :class="{ 'opacity-25': deleteServerForm.processing }"
          :disabled="deleteServerForm.processing"
          @click.native="deleteServer"
        >
          {{ __("Delete Server") }}
        </jet-danger-button>
      </template>
    </jet-confirmation-modal>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import Pagination from '@/Components/Pagination';
import {formatDistanceToNowStrict} from 'date-fns';
import JetConfirmationModal from '@/Jetstream/ConfirmationModal';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import JetDangerButton from '@/Jetstream/DangerButton';

export default {

    components: {
        AppLayout,
        Pagination,
        JetConfirmationModal,
        JetSecondaryButton,
        JetDangerButton
    },
    props: {
        servers: Object,
        canCreateBungeeServer: Boolean
    },
    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict,
            deleteServerForm: this.$inertia.form(),
            serverBeingDeleted: null,
            serverStatus: {},
            serverWebQueryStatus: {}
        };
    },

    mounted() {
        // Check ping for each server for online status
        this.servers.data.forEach(server => {
            this.$set(this.serverStatus, server.id, null);
            this.$set(this.serverWebQueryStatus, server.id, null);

            axios.get(route('server.ping.get', server.id))
                .then(() => {
                    this.$nextTick(() => this.serverStatus[server.id] = 1);
                })
                .catch(() => {
                    this.$nextTick(() => this.serverStatus[server.id] = -1);
                });

            // Only do webquery if server is not bungee.
            if (server.type.value !== 5) {
                axios.get(route('server.webquery.get', server.id))
                    .then(() => {
                        this.$nextTick(() => this.serverWebQueryStatus[server.id] = 1);
                    })
                    .catch(() => {
                        this.$nextTick(() => this.serverWebQueryStatus[server.id] = -1);
                    });
            } else {
                this.serverWebQueryStatus[server.id] = 0;
            }

        });
    },
    methods: {
        confirmServerDeletion(id) {
            this.serverBeingDeleted = id;
        },

        deleteServer() {
            this.deleteServerForm.delete(route('admin.server.delete', this.serverBeingDeleted), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => (this.serverBeingDeleted = null),
            });
        },
    },
};
</script>
