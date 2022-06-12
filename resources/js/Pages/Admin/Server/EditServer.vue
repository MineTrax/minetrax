<template>
  <app-layout>
    <app-head :title="`Edit Server: ${server.name}`" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          Edit Server : {{ server.name }}
        </h1>
        <inertia-link
          :href="route('admin.server.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>Cancel</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
              <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-400">
                Server Information
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                Just enter FTP / SFTP Information and Check FETCH. Manually fill the part which are not able to be fetch.
                All sensitive information will be encrypted.
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="updateServer">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
                      <x-select
                        id="connection_type"
                        v-model="form.connection_type"
                        name="connection_type"
                        :error="form.errors.connection_type"
                        label="Connection Type"
                        :select-list="{sftp: 'SFTP - Secure File Transfer Protocol', ftp: 'FTP - File Transfer Protocol', local: 'Local - Locally stored at server location'}"
                      />
                    </div>

                    <div
                      v-show="form.connection_type !== 'local'"
                      class="col-span-6 sm:col-span-4"
                    >
                      <x-input
                        id="storage_server_host"
                        v-model="form.storage_server_host"
                        :label="`${form.connection_type.toUpperCase()} Server Host`"
                        :error="form.errors.storage_server_host"
                        type="text"
                        name="storage_server_host"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div
                      v-show="form.connection_type !== 'local'"
                      class="col-span-6 sm:col-span-2"
                    >
                      <x-input
                        id="storage_server_port"
                        v-model="form.storage_server_port"
                        :label="`${form.connection_type.toUpperCase()} Port`"
                        :error="form.errors.storage_server_port"
                        type="text"
                        name="storage_server_port"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div
                      v-show="form.connection_type !== 'local'"
                      class="col-span-6 sm:col-span-2"
                    >
                      <x-input
                        id="storage_server_username"
                        v-model="form.storage_server_username"
                        label="Username"
                        :error="form.errors.storage_server_username"
                        type="text"
                        name="storage_server_username"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div
                      v-show="form.connection_type !== 'local'"
                      class="col-span-6 sm:col-span-2"
                    >
                      <x-input
                        id="storage_server_password"
                        v-model="form.storage_server_password"
                        label="Password"
                        :error="form.errors.storage_server_password"
                        type="text"
                        name="storage_server_password"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div
                      class="col-span-6"
                      :class="form.connection_type === 'local' ? 'sm:col-span-6' : 'sm:col-span-2'"
                    >
                      <x-input
                        id="storage_server_root"
                        v-model="form.storage_server_root"
                        label="Server Root Path"
                        :error="form.errors.storage_server_root"
                        type="text"
                        name="storage_server_root"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <template>
                      <div class="col-span-6 sm:col-span-3">
                        <x-input
                          id="name"
                          v-model="form.name"
                          label="Server Name"
                          :error="form.errors.name"
                          type="text"
                          name="name"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-3">
                        <x-input
                          id="hostname"
                          v-model="form.hostname"
                          label="Hostname"
                          :error="form.errors.hostname"
                          type="text"
                          name="hostname"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-input
                          id="join_port"
                          v-model="form.join_port"
                          label="Join Port"
                          :error="form.errors.join_port"
                          type="text"
                          name="join_port"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-input
                          id="query_port"
                          v-model="form.query_port"
                          label="Query Port"
                          :error="form.errors.query_port"
                          type="text"
                          name="query_port"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-input
                          id="webquery_port"
                          v-model="form.webquery_port"
                          label="WebQuery Port"
                          :error="form.errors.webquery_port"
                          type="text"
                          name="webquery_port"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-input
                          id="level_name"
                          v-model="form.level_name"
                          label="Level/World Name"
                          :error="form.errors.level_name"
                          type="text"
                          name="level_name"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-select
                          id="type"
                          v-model="form.type"
                          name="type"
                          placeholder="Select server type"
                          :disable-null="true"
                          :required="true"
                          :error="form.errors.type"
                          label="Server Type"
                          :select-list="typeArray"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-select
                          id="minecraft_version"
                          v-model="form.minecraft_version"
                          name="minecraft_version"
                          :error="form.errors.minecraft_version"
                          label="Version"
                          :select-list="versionsArray"
                        />
                      </div>

                      <div class="flex items-center col-span-6 sm:col-span-4">
                        <x-checkbox
                          id="settings_plugin_essentials"
                          v-model="form.settings.plugins.essentials"
                          label="Plugin: Essentials"
                          help="Tick if there is Essentials/EssentialsX installed in your server."
                          name="settings_plugin_essentials"
                        />


                        <!--
                                                <fieldset>
                                                    <legend class="text-base font-medium text-gray-900">Plugins</legend>
                                                    <div class="mt-4 flex space-x-4">
                                                        <div class="flex items-start">
                                                            <div class="flex items-center h-5">
                                                                <input v-model="form.settings.plugins.essentials" id="plugin_essentials" name="plugin_essentials" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                            </div>
                                                            <div class="ml-3 text-sm">
                                                                <label for="plugin_essentials" class="font-medium text-gray-700">Essentials</label>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-start hidden">
                                                            <div class="flex items-center h-5">
                                                                <input v-model="form.settings.plugins.luckperms" id="plugin_luckperms" name="plugin_luckperms" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                            </div>
                                                            <div class="ml-3 text-sm">
                                                                <label for="plugin_luckperms" class="font-medium text-gray-700">LuckPerms</label>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-start hidden">
                                                            <div class="flex items-center h-5">
                                                                <input v-model="form.settings.plugins.litebans" id="plugin_litebans" name="plugin_litebans" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                            </div>
                                                            <div class="ml-3 text-sm">
                                                                <label for="plugin_litebans" class="font-medium text-gray-700">LiteBans</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <jet-input-error :message="form.errors.settings" class="mt-2" />
                                                </fieldset>
                                                -->
                      </div>


                      <div class="flex items-center col-span-6 sm:col-span-4">
                        <x-checkbox
                          id="is_stats_tracking_enabled"
                          v-model="form.is_stats_tracking_enabled"
                          label="Track User Stats of this Server"
                          help="Fetch user stats and use it for calculation of ranks and other stuffs. Uncheck if this you don't want to track this server."
                          name="is_stats_tracking_enabled"
                        />
                      </div>

                      <div class="flex items-center col-span-6 sm:col-span-4">
                        <x-checkbox
                          id="is_ingame_chat_enabled"
                          v-model="form.is_ingame_chat_enabled"
                          label="Enable In-Game Chat of this Server"
                          help="Let user from website see and send chat to server."
                          name="is_ingame_chat_enabled"
                        />
                      </div>

                      <div class="flex items-center col-span-6 sm:col-span-4">
                        <x-checkbox
                          id="is_online_players_query_enabled"
                          v-model="form.is_online_players_query_enabled"
                          label="Show online player list of this Server"
                          help="If enabled show list of online players on this server near ingamechat."
                          name="is_online_players_query_enabled"
                        />
                      </div>
                    </template>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-between">
                  <button
                    id="prefetch"
                    :disabled="fetchLoading"
                    type="button"
                    class="mr-2 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50"
                    @click="prefetch"
                  >
                    <svg
                      v-if="fetchLoading"
                      class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      />
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      />
                    </svg>
                    Test & Fetch Latest Server Information
                  </button>

                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    Update Server
                  </loading-button>
                </div>

                <div
                  v-if="prefetchError"
                  class="text-red-600 bg-red-100 px-4 rounded flex items-center"
                >
                  {{ prefetchError }}
                </div>
                <div
                  v-if="isPrefetchSuccessful && serverStatus"
                  class="text-green-600 bg-green-100 px-4 rounded flex items-center"
                >
                  {{ form.connection_type.toUpperCase() }} connection successful & Server is Online.
                </div>
                <div
                  v-if="isPrefetchSuccessful && !serverStatus"
                  class="text-yellow-600 bg-yellow-100 px-4 rounded flex items-center"
                >
                  {{ form.connection_type.toUpperCase() }} connection successful but Server is offline.
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import LoadingButton from '@/Components/LoadingButton';
import XInput from '@/Components/Form/XInput';
import XSelect from '@/Components/Form/XSelect';
import XCheckbox from '@/Components/Form/XCheckbox';

export default {
    components: {
        XCheckbox,
        XSelect,
        AppLayout,
        LoadingButton,
        XInput
    },
    props: {
        versionsArray: Array,
        server: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            form: this.$inertia.form({
                connection_type: this.server.connection_type,
                storage_server_host: this.server.storage_server_host,
                storage_server_port: this.server.storage_server_port,
                storage_server_username: this.server.storage_server_username,
                storage_server_password: this.server.storage_server_password,
                storage_server_root: this.server.storage_server_root,
                storage_server_key: '',
                name: this.server.name,
                join_port: this.server.join_port,
                query_port: this.server.query_port,
                webquery_port: this.server.webquery_port,
                level_name: this.server.level_name,
                minecraft_version: this.server.minecraft_version,
                type: this.server.type,
                hostname: this.server.hostname,
                is_stats_tracking_enabled: this.server.is_stats_tracking_enabled,
                is_ingame_chat_enabled: this.server.is_ingame_chat_enabled,
                is_online_players_query_enabled: this.server.is_online_players_query_enabled,
                settings: this.server.settings,
            }),
            isPrefetchSuccessful: false,
            prefetchError: null,
            fetchLoading: false,
            serverStatus: null,
            typeArray: {
                0: 'Paper',
                1: 'Spigot',
                2: 'Forge',
                3: 'Bukkit',
                4: 'Vanilla',
            }
        };
    },
    methods: {
        prefetch() {
            this.prefetchError = null;
            this.fetchLoading = true;
            this.isPrefetchSuccessful = false;
            axios.post(route('admin.server.prefetch'), this.form)
                .then(res => {
                    this.form.name = res.data.data['motd'];
                    this.form.join_port = res.data.data['server-port'];
                    this.form.query_port = res.data.data['query.port'];
                    this.form.level_name = res.data.data['level-name'];
                    this.form.hostname = res.data.data['server-ip']+':'+res.data.data['server-port'];
                    this.isPrefetchSuccessful = true;
                    this.serverStatus = res.data.server_status;
                    // TODO: Fix this is not working
                    if (this.serverStatus) {
                        let index = 0;
                        this.versionsArray.forEach(version => {
                            if(res.data.server_status['version']['name'].indexOf(version) >= 0) {
                                this.form.minecraft_version = this.versionsArray[index];
                            }
                            index++;
                        });
                    }
                    this.fetchLoading = false;
                }, error => {
                    this.prefetchError = error.response.data.message;
                    this.fetchLoading = false;
                });
        },
        updateServer() {
            this.form.put(route('admin.server.update', this.server.id), {
                preserveScroll: true
            });
        }
    }
};
</script>
