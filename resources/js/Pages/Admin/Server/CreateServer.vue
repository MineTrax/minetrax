<template>
  <app-layout>
    <app-head
      :title="__('Add New Server')"
    />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __("Add Server") }}
        </h1>
        <inertia-link
          :href="route('admin.server.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
              <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-400">
                {{ __("Overview") }}
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                {{ __("Just enter FTP / SFTP Information and Check FETCH. Manually fill the part which are not able to be fetch.  All sensitive information will be encrypted.") }}
              </p>
            </div>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="addServer">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
                      <x-select
                        id="connection_type"
                        v-model="form.connection_type"
                        name="connection_type"
                        :error="form.errors.connection_type"
                        :label="__('Connection Type')"
                        :select-list="{sftp: __('SFTP - Secure File Transfer Protocol'), ftp: __('FTP - File Transfer Protocol'), local: __('Local - Locally stored at server location')}"
                      />
                    </div>

                    <div
                      v-show="form.connection_type !== 'local'"
                      class="col-span-6 sm:col-span-4"
                    >
                      <x-input
                        id="storage_server_host"
                        v-model="form.storage_server_host"
                        :label="__(':connection_type Server Host', {connection_type: form.connection_type.toUpperCase()})"
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
                        :label="__(':connection_type Port', {connection_type: form.connection_type.toUpperCase()})"
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
                        :label="__('Username')"
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
                        :label="__('Password')"
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
                        :label="__('Server Root Path')"
                        :error="form.errors.storage_server_root"
                        type="text"
                        name="storage_server_root"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <template v-if="isPrefetchSuccessful">
                      <div class="col-span-6 sm:col-span-3">
                        <x-input
                          id="name"
                          v-model="form.name"
                          :label="__('Server Name')"
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
                          :label="__('Hostname')"
                          :error="form.errors.hostname"
                          type="text"
                          name="hostname"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-input
                          id="ip_address"
                          v-model="form.ip_address"
                          :label="__('IP Address')"
                          :error="form.errors.ip_address"
                          autocomplete="ip_address"
                          type="text"
                          name="ip_address"
                          help-error-flex="flex-col"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-input
                          id="join_port"
                          v-model="form.join_port"
                          :label="__('Join Port')"
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
                          :label="__('Query Port')"
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
                          :label="__('Webquery Port')"
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
                          :label="__('Level/World Name')"
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
                          :placeholder="__('Select server type')"
                          :disable-null="true"
                          :required="true"
                          name="type"
                          :error="form.errors.type"
                          :label="__('Server Type')"
                          :select-list="typeArray"
                        />
                      </div>

                      <div class="col-span-6 sm:col-span-2">
                        <x-select
                          id="minecraft_version"
                          v-model="form.minecraft_version"
                          name="minecraft_version"
                          :error="form.errors.minecraft_version"
                          :label="__('Version')"
                          :select-list="versionsArray"
                        />
                      </div>

                      <div class="flex items-center col-span-6 sm:col-span-4">
                        <x-checkbox
                          id="settings_plugin_essentials"
                          v-model="form.settings.plugins.essentials"
                          :label="__('Plugin: Essentials')"
                          :help="__('Tick if there is Essentials/EssentialsX installed in your server.')"
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
                          :label="__('Track Player Stats of this Server')"
                          :help="__('Fetch user stats and use it for calculation of ranks and other stuffs. Uncheck if this you dont want to track this server.')"
                          name="is_stats_tracking_enabled"
                        />
                      </div>

                      <div class="flex items-center col-span-6 sm:col-span-4">
                        <x-checkbox
                          id="is_ingame_chat_enabled"
                          v-model="form.is_ingame_chat_enabled"
                          :label="__('Enable In-Game Chat of this Server')"
                          :help="__('Let user from website see and send chat to server.')"
                          name="is_ingame_chat_enabled"
                        />
                      </div>

                      <div class="flex items-center col-span-6 sm:col-span-4">
                        <x-checkbox
                          id="is_online_players_query_enabled"
                          v-model="form.is_online_players_query_enabled"
                          :label="__('Show online player list of this Server')"
                          :help="__('If enabled show list of online players on this server near ingamechat.')"
                          name="is_online_players_query_enabled"
                        />
                      </div>
                    </template>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-between">
                  <div
                    v-if="isPrefetchSuccessful && serverStatus"
                    class="text-green-600 bg-green-100 px-4 rounded flex items-center"
                  >
                    {{ __(":connection_type connection successful & Server is Online.", {connection_type: form.connection_type.toUpperCase()}) }}
                  </div>
                  <div
                    v-if="isPrefetchSuccessful && !serverStatus"
                    class="text-yellow-600 bg-yellow-100 px-4 rounded flex items-center"
                  >
                    {{ __(":connection_type connection successful but Server is offline.", {connection_type: form.connection_type.toUpperCase()}) }}
                  </div>

                  <button
                    v-show="!isPrefetchSuccessful"
                    id="prefetch"
                    :disabled="fetchLoading"
                    type="button"
                    class="mr-2 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
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
                    {{ __("Test Connection") }}
                  </button>
                  <div
                    v-if="prefetchError"
                    class="text-red-600 bg-red-100 px-4 rounded flex items-center"
                  >
                    {{ prefetchError }}
                  </div>


                  <loading-button
                    v-show="isPrefetchSuccessful"
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Add Server") }}
                  </loading-button>
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
import AppLayout from '@/Layouts/AppLayout.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';

export default {
    components: {
        XCheckbox,
        XSelect,
        AppLayout,
        LoadingButton,
        XInput
    },
    props: {
        versionsArray: Array
    },
    data() {
        return {
            form: this.$inertia.form({
                connection_type: 'sftp',
                storage_server_host: '',
                storage_server_port: '',
                storage_server_username: '',
                storage_server_password: '',
                storage_server_root: '',
                storage_server_key: '',
                name: null,
                join_port: null,
                query_port: null,
                webquery_port: null,
                level_name: null,
                minecraft_version: null,
                type: null,
                hostname: null,
                ip_address: null,
                is_stats_tracking_enabled: true,
                is_ingame_chat_enabled: true,
                is_online_players_query_enabled: true,
                settings: {
                    plugins: {
                        essentials: false,
                        // luckperms: false,
                        // litebans: false
                    }
                }
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
            axios.post(route('admin.server.prefetch'), this.form)
                .then(res => {
                    this.form.name = res.data.data['motd'];
                    this.form.join_port = res.data.data['server-port'];
                    this.form.query_port = res.data.data['query.port'];
                    this.form.level_name = res.data.data['level-name'];
                    this.form.hostname = res.data.data['server-ip']+':'+res.data.data['server-port'];
                    this.form.ip_address = res.data.data['server-ip'];
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

        addServer() {
            if (!this.isPrefetchSuccessful) {
                return;
            }
            this.form.post(route('admin.server.store'), {
                preserveScroll: true
            });
        }
    }
};
</script>
