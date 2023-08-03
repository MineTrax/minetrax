<template>
  <AdminLayout>
    <app-head :title="__('Edit Server: :name', { name: server.name })" />

    <div class="py-12 px-10 max-w-5xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ __('Edit Server: :name', { name: server.name }) }}
        </h1>
        <inertia-link
          :href="route('admin.server.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="">
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="updateServer">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="name"
                        v-model="form.name"
                        :label="__('Server Name')"
                        :help="__('This name will help to identify this server. Eg: Survival, Skyblock, etc.')"
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
                        :help="__('Publicly visible join address of the server. Eg: play.example.com')"
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

                    <div class="col-span-6 sm:col-span-6">
                      <div class="grid grid-cols-2 gap-6">
                        <x-input
                          id="webquery_port"
                          v-model="form.webquery_port"
                          :label="__('Webquery Port')"
                          :error="form.errors.webquery_port"
                          type="text"
                          name="webquery_port"
                          help-error-flex="flex-col"
                        />
                        <div class="text-xs text-gray-400 flex items-center">
                          {{ __("WebQuery port is a new port which MineTrax plugin will open for secure connection between server and web. Enter a port value which is available and can be open. Eg: 25569") }}
                        </div>
                      </div>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
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

                    <div class="col-span-6 sm:col-span-3">
                      <x-select
                        id="minecraft_version"
                        v-model="form.minecraft_version"
                        name="minecraft_version"
                        :error="form.errors.minecraft_version"
                        :label="__('Version')"
                        :select-list="versionsArray"
                      />
                    </div>


                    <div class="flex items-center col-span-6 sm:col-span-6">
                      <x-checkbox
                        id="is_server_intel_enabled"
                        v-model="form.is_server_intel_enabled"
                        :label="__('Enable Server Intel / Analytics')"
                        :help="__('If enabled, server analytics data (performance metric, join activity etc) will be captured for this server via plugin.')"
                        name="is_server_intel_enabled"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-6">
                      <x-checkbox
                        id="is_player_intel_enabled"
                        v-model="form.is_player_intel_enabled"
                        :label="__('Enable Player Intel / Analytics')"
                        :help="__('If enabled, player intel & statistics data will be captured for this server via plugin.')"
                        name="is_player_intel_enabled"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-6">
                      <x-checkbox
                        id="is_ingame_chat_enabled"
                        v-model="form.is_ingame_chat_enabled"
                        :label="__('Enable In-Game Chat')"
                        :help="__('Enable in-game chat for this server, which allow users to view & chat to in-game players from website.')"
                        name="is_ingame_chat_enabled"
                      />
                    </div>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ __("Update Server") }}
                  </loading-button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XCheckbox,
        XSelect,
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
            form: useForm({
                name: this.server.name,
                join_port: this.server.join_port,
                query_port: this.server.query_port,
                webquery_port: this.server.webquery_port,
                minecraft_version: this.server.minecraft_version,
                type: this.server.type,
                hostname: this.server.hostname,
                ip_address: this.server.ip_address,
                is_server_intel_enabled: this.server.is_server_intel_enabled,
                is_player_intel_enabled: this.server.is_player_intel_enabled,
                is_ingame_chat_enabled: this.server.is_ingame_chat_enabled,
                settings: this.server.settings,
            }),
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
        updateServer() {
            this.form.put(route('admin.server.update', this.server.id), {
                preserveScroll: false
            });
        }
    }
};
</script>
