<template>
  <AdminLayout>
    <app-head
      :title="__('Plugin Settings')"
    />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full rounded">
            <div class="px-6 py-4 border-b dark:border-gray-700 dark:text-gray-300 font-bold">
              {{ __("Plugin Settings") }}
            </div>

            <div class="mt-10 sm:mt-0">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="mt-5 md:mt-0 md:col-span-3">
                  <div class="flex items-center justify-end col-span-6 sm:col-span-2 px-6 py-2">
                    <inertia-link
                      v-confirm="{message: __('Are you sure? This will regenerate new API Key and Secret and you will have to change in your Plugin config.yml')}"
                      v-tippy
                      :title="__('Warning: This will Regenerate your API and Secret Key.')"
                      as="button"
                      method="post"
                      :href="route('admin.setting.plugin.keygen')"
                      class="inline-flex justify-center py-2 px-4 border border-2 border-red-500 shadow-sm text-sm font-bold rounded-md text-red-500 hover:bg-red-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                    >
                      {{ __("Regenerate API Credentials") }}
                    </inertia-link>
                  </div>

                  <form
                    autocomplete="off"
                    @submit.prevent="savePluginSetting"
                  >
                    <div class="shadow overflow-hidden sm:rounded-md">
                      <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                          <div class="flex items-center col-span-6 sm:col-span-4">
                            <x-checkbox
                              id="enable_api"
                              v-model="form.enable_api"
                              :label="__('Enable Plugin')"
                              :help="__('Enable this if you are having MineTrax plugin installed in your server.')"
                              name="enable_api"
                              :error="form.errors.enable_api"
                            />
                          </div>

                          <div class="col-span-6 sm:col-span-6">
                            <copy-to-clipboard v-slot="props">
                              <x-input
                                id="plugin_api_key"
                                v-tippy
                                :title="__('Click to Copy')"
                                :model-value="props.status === 'copied' ? __('Copied to Clipboard!') : settings.plugin_api_key"
                                :label="__('API Key')"
                                type="text"
                                name="plugin_api_key"
                                input-class="disabled:opacity-100 hover:cursor-pointer"
                                @click="props.copy(settings.plugin_api_key)"
                              />
                            </copy-to-clipboard>
                          </div>

                          <div class="col-span-6 sm:col-span-6">
                            <copy-to-clipboard v-slot="props">
                              <x-input
                                id="plugin_api_secret"
                                v-tippy
                                :title="__('Click to Copy')"
                                :model-value="props.status === 'copied' ? __('Copied to Clipboard!') : settings.plugin_api_secret"
                                :label="__('API Secret')"
                                type="text"
                                name="plugin_api_secret"
                                input-class="disabled:opacity-100 hover:cursor-pointer"
                                @click="props.copy(settings.plugin_api_secret)"
                              />
                            </copy-to-clipboard>
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-2">
                            <x-checkbox
                              id="enable_account_link"
                              v-model="form.enable_account_link"
                              :label="__('Enable Account Link')"
                              :help="__('Enable user to link their players to account')"
                              name="enable_account_link"
                              :error="form.errors.enable_account_link"
                            />
                          </div>

                          <div
                            v-show="form.enable_account_link"
                            class="col-span-6 sm:col-span-4"
                          >
                            <x-input
                              id="max_players_link_per_account"
                              v-model="form.max_players_link_per_account"
                              :label="__('Max Players Per Account')"
                              :help="__('Number of players that can be linked to one account in website.')"
                              type="number"
                              name="max_players_link_per_account"
                              :error="form.errors.max_players_link_per_account"
                              help-error-flex="flex-col"
                            />
                          </div>

                          <div
                            v-show="form.enable_account_link"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="account_link_after_success_command"
                              v-model="form.account_link_after_success_command"
                              :label="__('Account Link Success Command')"
                              :help="__('Use this to reward players when they link account: Eg: give {PLAYER} diamond 1')"
                              type="text"
                              name="account_link_after_success_command"
                              :error="form.errors.account_link_after_success_command"
                              help-error-flex="flex-col"
                            />
                          </div>

                          <div
                            v-show="form.enable_account_link"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-input
                              id="account_link_after_success_broadcast_message"
                              v-model="form.account_link_after_success_broadcast_message"
                              :label="__('Account Link Success Broadcast')"
                              :help="__('If set then this will be broadcast to server when player link is successful: Eg: {PLAYER} has successfully linked his account and won a ultra key.')"
                              type="text"
                              name="account_link_after_success_broadcast_message"
                              :error="form.errors.account_link_after_success_broadcast_message"
                              help-error-flex="flex-col"
                            />
                          </div>


                          <div class="flex items-center col-span-6 sm:col-span-6">
                            <x-checkbox
                              id="enable_sync_player_ranks_from_server"
                              v-model="form.enable_sync_player_ranks_from_server"
                              :label="__('Enable Player Rank Sync')"
                              :help="__('Enable this if you want to sync your player rank from server instead of website calculated rank. you must create a rank for each group you have in server making sure rank shortname matches the name of your player group in server.')"
                              name="enable_sync_player_ranks_from_server"
                              :error="form.errors.enable_sync_player_ranks_from_server"
                            />
                          </div>

                          <div
                            v-show="form.enable_sync_player_ranks_from_server"
                            class="col-span-6 sm:col-span-6"
                          >
                            <x-select
                              id="sync_player_ranks_from_server_id"
                              v-model="form.sync_player_ranks_from_server_id"
                              name="sync_player_ranks_from_server_id"
                              :error="form.errors.sync_player_ranks_from_server_id"
                              :label="__('Rank Sync From Server')"
                              :placeholder="__('Choose a server from which we should sync the rank..')"
                              :disable-null="false"
                              :select-list="servers"
                            />
                          </div>
                        </div>
                      </div>
                      <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                        <loading-button
                          :loading="form.processing"
                          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50 dark:bg-cool-gray-700 dark:hover:bg-cool-gray-600"
                          type="submit"
                        >
                          {{ __("Save Plugin Settings") }}
                        </loading-button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import LoadingButton from '@/Components/LoadingButton.vue';
import CopyToClipboard from '@/Components/CopyToClipboard.vue';
import XInput from '@/Components/Form/XInput.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XCheckbox,
        LoadingButton,
        XInput,
        CopyToClipboard,
        XSelect
    },
    props: {
        settings: Object,
        servers: Object
    },
    data() {
        return {
            form: useForm({
                enable_api: this.settings.enable_api,
                enable_account_link: this.settings.enable_account_link,
                max_players_link_per_account: this.settings.max_players_link_per_account,
                account_link_after_success_command: this.settings.account_link_after_success_command,
                account_link_after_success_broadcast_message: this.settings.account_link_after_success_broadcast_message,

                enable_sync_player_ranks_from_server: this.settings.enable_sync_player_ranks_from_server,
                sync_player_ranks_from_server_id: this.settings.sync_player_ranks_from_server_id
            }),
        };
    },
    methods: {
        savePluginSetting() {
            this.form.post(route('admin.setting.plugin.update'), {
                preserveScroll: true,
            });
        }
    }
};
</script>
