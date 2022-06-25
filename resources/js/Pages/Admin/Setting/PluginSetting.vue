<template>
  <app-layout>
    <app-head title="Plugin Settings" />

    <div class="py-12 px-10 max-w-6xl mx-auto flex">
      <div class="w-64 flex-shrink-0 pr-10">
        <div class="flex flex-col">
          <div class="uppercase mb-2 text-xs tracking-wide text-gray-600 dark:text-gray-400 font-bold">
            SETTINGS
          </div>

          <setting-link
            :href="route('admin.setting.general.show')"
            :active="route().current('admin.setting.general.show')"
          >
            General
          </setting-link>
          <setting-link
            :href="route('admin.setting.theme.show')"
            :active="route().current('admin.setting.theme.show')"
          >
            Theme
          </setting-link>
          <setting-link
            :href="route('admin.setting.plugin.show')"
            :active="route().current('admin.setting.plugin.show')"
          >
            Plugin
          </setting-link>
          <setting-link
            :href="route('admin.setting.player.show')"
            :active="route().current('admin.setting.player.show')"
          >
            Player
          </setting-link>
        </div>
      </div>

      <div class="flex-1">
        <div class="flex flex-col w-full">
          <div class="bg-white dark:bg-cool-gray-800 shadow w-full">
            <div class="px-6 py-4 border-b dark:border-gray-700 dark:text-gray-300 font-bold">
              Plugin Settings
            </div>

            <div class="mt-10 sm:mt-0">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="mt-5 md:mt-0 md:col-span-3">
                  <div class="flex items-center justify-end col-span-6 sm:col-span-2 px-6 py-2">
                    <inertia-link
                      v-confirm="{message: 'Are you sure? This will regenerate new API Key and Secret and you will have to change in your Plugin config.yml'}"
                      v-tippy
                      title="Warning: This will Regenerate your API and Secret Key."
                      as="button"
                      method="post"
                      :href="route('admin.setting.plugin.keygen')"
                      class="inline-flex justify-center py-2 px-4 border border-2 border-red-500 shadow-sm text-sm font-bold rounded-md text-red-500 hover:bg-red-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                    >
                      Regenerate API Credentials
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
                              label="Enable Plugin"
                              help="Enable this if you are having MineTrax plugin installed in your server."
                              name="enable_api"
                              :error="form.errors.enable_api"
                            />
                          </div>

                          <div class="col-span-6 sm:col-span-6">
                            <copy-to-clipboard v-slot="props">
                              <x-input
                                id="plugin_api_key"
                                v-model="props.status === 'copied' ? 'Copied to Clipboard!' : settings.plugin_api_key"
                                label="API Key"
                                type="text"
                                name="plugin_api_key"
                                input-class="disabled:opacity-100 hover:cursor-pointer"
                                @click.native="props.copy(settings.plugin_api_key)"
                              />
                            </copy-to-clipboard>
                          </div>

                          <div class="col-span-6 sm:col-span-6">
                            <copy-to-clipboard v-slot="props">
                              <x-input
                                id="plugin_api_secret"
                                v-model="props.status === 'copied' ? 'Copied to Clipboard!' : settings.plugin_api_secret"
                                label="API Secret"
                                type="text"
                                name="plugin_api_secret"
                                :disabled="true"
                                input-class="disabled:opacity-100 hover:cursor-pointer"
                                @click.native="props.copy(settings.plugin_api_secret)"
                              />
                            </copy-to-clipboard>
                          </div>

                          <div class="flex items-center col-span-6 sm:col-span-2">
                            <x-checkbox
                              id="enable_account_link"
                              v-model="form.enable_account_link"
                              label="Enable Account Link"
                              help="Enable user to link their players to account"
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
                              label="Max Players Per Account"
                              help="Number of players that can be linked to one account in website."
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
                              label="Account Link Success Command"
                              help="Use this to reward players when they link account: Eg: give {PLAYER} diamond 1"
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
                              label="Account Link Success Broadcast"
                              help="If set then this will be broadcast to server when player link is successful: Eg: {PLAYER} has successfully linked his account and won a ultra key."
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
                              label="Enable Player Rank Sync"
                              help="Enable this if you want to sync your player rank from server instead of website calculated rank. You must create a rank for each group you have in server making sure rank's shortname matches the name of your player group in server."
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
                              label="Rank Sync From Server"
                              placeholder="Select a server from which we should sync the rank.."
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
                          Save Plugin Settings
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
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import JetSectionBorder from '@/Jetstream/SectionBorder';
import JetInputError from '@/Jetstream/InputError';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import LoadingButton from '@/Components/LoadingButton';
import CopyToClipboard from '@/Components/CopyToClipboard';
import XInput from '@/Components/Form/XInput';
import Icon from '@/Components/Icon';
import SettingLink from '@/Jetstream/SettingLink';
import XCheckbox from '@/Components/Form/XCheckbox';
import XSelect from '@/Components/Form/XSelect';

export default {
    components: {
        XCheckbox,
        SettingLink,
        AppLayout,
        JetSectionBorder,
        JetInputError,
        LoadingButton,
        JetSecondaryButton,
        Icon,
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
            form: this.$inertia.form({
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
