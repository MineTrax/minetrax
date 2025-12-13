<script setup>
import { useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import CopyToClipboard from '@/Components/CopyToClipboard.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import { useTranslations } from '@/Composables/useTranslations';
import Icon from '@/Components/Icon.vue';
import Multiselect from 'vue-multiselect';

const { __ } = useTranslations();

const props = defineProps({
    settings: Object,
    settingsAccountLinkAfterSuccessCommands: Array,
    settingsAccountUnlinkAfterSuccessCommands: Array,
    settingsPlayerPasswordResetCommands: Array,
    serversForRankSync: Object,
    serversWithWebquery: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Settings'),
        current: false,
    },
    {
        text: __('Plugin Settings'),
        current: true,
    }
];

const form = useForm({
    enable_api: props.settings.enable_api,
    enable_account_link: props.settings.enable_account_link,
    max_players_link_per_account: props.settings.max_players_link_per_account,
    enable_sync_player_ranks_from_server: props.settings.enable_sync_player_ranks_from_server,
    sync_player_ranks_from_server_id: props.settings.sync_player_ranks_from_server_id,
    account_link_after_success_commands: props.settingsAccountLinkAfterSuccessCommands,
    account_unlink_after_success_commands: props.settingsAccountUnlinkAfterSuccessCommands,
    enable_player_password_reset: props.settings.enable_player_password_reset,
    player_password_reset_cooldown_in_seconds: props.settings.player_password_reset_cooldown_in_seconds,
    player_password_reset_commands: props.settingsPlayerPasswordResetCommands,
});

function addAccountLinkCommand() {
    form.account_link_after_success_commands.push({
        command: '',
        servers: [],
        config: {
            is_player_online_required: false,
            is_run_only_first_link: false,
        }
    });
}

function removeAccountLinkCommand(index) {
    form.account_link_after_success_commands.splice(index, 1);
}

function serversWithWebqueryCustomLabel({ name, hostname }) {
    return `${name} - ${hostname}`;
}

function addAccountUnlinkCommand() {
    form.account_unlink_after_success_commands.push({
        command: '',
        servers: [],
        config: {
            is_player_online_required: false,
            is_run_only_first_unlink: false,
        }
    });
}

function removeAccountUnlinkCommand(index) {
    form.account_unlink_after_success_commands.splice(index, 1);
}

function addPlayerPasswordResetCommand() {
    form.player_password_reset_commands.push({
        command: '',
        servers: [],
        config: {
            is_player_online_required: false,
        }
    });
}

function removePlayerPasswordResetCommand(index) {
    form.player_password_reset_commands.splice(index, 1);
}

function savePluginSetting() {
    form.post(route('admin.setting.plugin.update'), {
        preserveScroll: true,
        onSuccess: () => {
            router.get(route('admin.setting.plugin.show'), {}, {
                preserveState: false,
                preserveScroll: true,
                replace: true,
            });
        }
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Plugin Settings')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form
          autocomplete="off"
          @submit.prevent="savePluginSetting"
        >
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <!-- API Credentials -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="flex items-center justify-end mb-4">
                      <Link
                        v-confirm="{ message: __('Are you sure? This will regenerate new API Key and Secret and you will have to change in your Plugin config.yml') }"
                        v-tippy
                        :title="__('Warning: This will Regenerate your API and Secret Key.')"
                        as="button"
                        method="post"
                        :href="route('admin.setting.plugin.keygen')"
                        class="inline-flex justify-center py-2 px-4 border-2 border-destructive shadow-sm text-sm font-bold rounded-md text-destructive hover:bg-destructive hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-destructive disabled:opacity-50 transition-colors"
                      >
                        {{ __("Regenerate API Credentials") }}
                      </Link>
                    </div>

                    <div class="grid grid-cols-6 gap-4">
                      <div class="col-span-6">
                        <XSwitch
                          id="enable_api"
                          v-model="form.enable_api"
                          :label="__('Enable Plugin')"
                          :help="__('Enable this if you are having MineTrax plugin installed in your server.')"
                          name="enable_api"
                          :error="form.errors.enable_api"
                        />
                      </div>

                      <div class="col-span-6">
                        <CopyToClipboard v-slot="copyProps">
                          <XInput
                            id="plugin_api_key"
                            v-tippy
                            :title="__('Click to Copy')"
                            :model-value="copyProps.status === 'copied' ? __('Copied to Clipboard!') : settings.plugin_api_key"
                            :label="__('API Key')"
                            type="text"
                            name="plugin_api_key"
                            input-class="disabled:opacity-100 hover:cursor-pointer"
                            @click="copyProps.copy(settings.plugin_api_key)"
                          />
                        </CopyToClipboard>
                      </div>

                      <div class="col-span-6">
                        <CopyToClipboard v-slot="copyProps">
                          <XInput
                            id="plugin_api_secret"
                            v-tippy
                            :title="__('Click to Copy')"
                            :model-value="copyProps.status === 'copied' ? __('Copied to Clipboard!') : settings.plugin_api_secret"
                            :label="__('API Secret')"
                            type="text"
                            name="plugin_api_secret"
                            input-class="disabled:opacity-100 hover:cursor-pointer"
                            @click="copyProps.copy(settings.plugin_api_secret)"
                          />
                        </CopyToClipboard>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Account Link Settings -->
                <div class="col-span-6">
                  <fieldset>
                    <legend class="text-sm font-medium text-foreground mb-4">
                      {{ __("Account Link") }}
                    </legend>

                    <div class="space-y-6">
                      <div class="grid grid-cols-6 gap-4">
                        <div class="col-span-6 sm:col-span-2">
                          <XSwitch
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
                          <XInput
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
                      </div>

                      <!-- Account Link Success Commands -->
                      <div
                        v-show="form.enable_account_link"
                        class="space-y-3"
                      >
                        <div class="flex items-center justify-between">
                          <legend class="text-sm text-foreground font-semibold">
                            {{ __("Account Link Success Commands") }}
                            <span class="text-xs font-light text-muted-foreground">{{ __("(runs when a player is linked to account)") }}</span>
                          </legend>
                        </div>

                        <div class="bg-muted/30 rounded-lg p-4 space-y-4">
                          <div class="flex flex-col items-end">
                            <h3 class="text-foreground text-sm font-semibold">
                              {{ __("Available Placeholders") }}
                            </h3>
                            <ol class="text-sm text-right text-muted-foreground">
                              <li>
                                <code class="text-sm bg-muted p-1 rounded-md">{PLAYER_USERNAME}</code> - {{ __("Username of the player which is linked.") }}
                              </li>
                              <li>
                                <code class="text-sm bg-muted p-1 rounded-md">{PLAYER_UUID}</code> - {{ __("Unique Id of the player which is linked.") }}
                              </li>
                            </ol>
                          </div>

                          <div class="flex space-x-4">
                            <div class="w-5" />
                            <label class="flex-1 block text-sm font-medium text-foreground">
                              {{ __("Command") }}
                            </label>
                            <label class="flex-1 block text-sm font-medium text-foreground">
                              {{ __("Run on servers") }}
                            </label>
                          </div>

                          <div
                            v-for="(command, index) in form.account_link_after_success_commands"
                            :key="index"
                            class="flex flex-col pb-2"
                          >
                            <div class="flex space-x-4">
                              <button
                                type="button"
                                class="focus:outline-none group"
                                @click="removeAccountLinkCommand(index)"
                              >
                                <Icon
                                  class="w-5 h-5 text-muted-foreground group-hover:text-destructive"
                                  name="trash"
                                />
                              </button>

                              <div class="grid grid-cols-2 gap-2 w-full">
                                <div class="flex-1">
                                  <XInput
                                    :id="`link_command_${index}`"
                                    v-model="command.command"
                                    :label="null"
                                    :placeholder="__('Enter Command') + ` #${index + 1}`"
                                    type="text"
                                    :error="form.errors[`account_link_after_success_commands.${index}.command`]"
                                    input-class="h-[2.65rem]"
                                  />
                                </div>
                                <div class="flex-1">
                                  <Multiselect
                                    v-model="command.servers"
                                    :options="serversWithWebquery"
                                    :custom-label="serversWithWebqueryCustomLabel"
                                    track-by="id"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :clear-on-select="false"
                                    :searchable="false"
                                    :placeholder="__('Leave empty to run on all servers') + '...'"
                                  />
                                  <p
                                    v-if="form.errors[`account_link_after_success_commands.${index}.servers`]"
                                    class="text-sm text-destructive mt-0.5"
                                  >
                                    {{ form.errors[`account_link_after_success_commands.${index}.servers`] }}
                                  </p>
                                </div>

                                <div class="flex-1 flex col-span-2 space-x-4">
                                  <XSwitch
                                    :id="`link.is_player_online_required_${index}`"
                                    v-model="command.config.is_player_online_required"
                                    :label="__('Require player to be online')"
                                    :help="__('This command should only run if player is online on running server. If not online it will be queued to run when player comes online.')"
                                    :name="`link.is_player_online_required_${index}`"
                                    :error="form.errors[`account_link_after_success_commands.${index}.config.is_player_online_required`]"
                                  />

                                  <XSwitch
                                    :id="`link.is_run_only_first_link_${index}`"
                                    v-model="command.config.is_run_only_first_link"
                                    :name="`link.is_run_only_first_link_${index}`"
                                    :label="__('Run on first link only')"
                                    :help="__('This command should only run if given player is getting linked for first time. (Eg: This wont run if player get unlinked and then linked again)')"
                                    :error="form.errors[`account_link_after_success_commands.${index}.config.is_run_only_first_link`]"
                                  />
                                </div>
                              </div>
                            </div>
                          </div>

                          <div
                            v-if="form.account_link_after_success_commands.length <= 0"
                            class="text-muted-foreground text-sm italic text-center pt-2"
                          >
                            {{ __("No commands on account link.") }}
                          </div>

                          <p
                            v-if="form.errors.account_link_after_success_commands"
                            class="text-sm text-destructive text-center"
                          >
                            {{ form.errors.account_link_after_success_commands }}
                          </p>

                          <div class="flex justify-center pt-2">
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              @click="addAccountLinkCommand"
                            >
                              {{ __("Add New Link Command") }}
                            </Button>
                          </div>
                        </div>
                      </div>

                      <!-- Account Unlink Success Commands -->
                      <div
                        v-show="form.enable_account_link"
                        class="space-y-3"
                      >
                        <div class="flex items-center justify-between">
                          <legend class="text-sm text-foreground font-semibold">
                            {{ __("Account Unlink Success Commands") }}
                            <span class="text-xs font-light text-muted-foreground">{{ __("(runs when a player is unlinked from account)") }}</span>
                          </legend>
                        </div>

                        <div class="bg-muted/30 rounded-lg p-4 space-y-4">
                          <div class="flex flex-col items-end">
                            <h3 class="text-foreground text-sm font-semibold">
                              {{ __("Available Placeholders") }}
                            </h3>
                            <ol class="text-sm text-right text-muted-foreground">
                              <li>
                                <code class="text-sm bg-muted p-1 rounded-md">{PLAYER_USERNAME}</code> - {{ __("Username of the player which is unlinked.") }}
                              </li>
                              <li>
                                <code class="text-sm bg-muted p-1 rounded-md">{PLAYER_UUID}</code> - {{ __("Unique Id of the player which is unlinked.") }}
                              </li>
                            </ol>
                          </div>

                          <div class="flex space-x-4">
                            <div class="w-5" />
                            <label class="flex-1 block text-sm font-medium text-foreground">
                              {{ __("Command") }}
                            </label>
                            <label class="flex-1 block text-sm font-medium text-foreground">
                              {{ __("Run on servers") }}
                            </label>
                          </div>

                          <div
                            v-for="(command, index) in form.account_unlink_after_success_commands"
                            :key="index"
                            class="flex flex-col pb-2"
                          >
                            <div class="flex space-x-4">
                              <button
                                type="button"
                                class="focus:outline-none group"
                                @click="removeAccountUnlinkCommand(index)"
                              >
                                <Icon
                                  class="w-5 h-5 text-muted-foreground group-hover:text-destructive"
                                  name="trash"
                                />
                              </button>

                              <div class="grid grid-cols-2 gap-2 w-full">
                                <div class="flex-1">
                                  <XInput
                                    :id="`unlink_command_${index}`"
                                    v-model="command.command"
                                    :label="null"
                                    :placeholder="__('Enter Command') + ` #${index + 1}`"
                                    type="text"
                                    :error="form.errors[`account_unlink_after_success_commands.${index}.command`]"
                                    input-class="h-[2.65rem]"
                                  />
                                </div>
                                <div class="flex-1">
                                  <Multiselect
                                    v-model="command.servers"
                                    :options="serversWithWebquery"
                                    :custom-label="serversWithWebqueryCustomLabel"
                                    track-by="id"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :clear-on-select="false"
                                    :searchable="false"
                                    :placeholder="__('Leave empty to run on all servers') + '...'"
                                  />
                                  <p
                                    v-if="form.errors[`account_unlink_after_success_commands.${index}.servers`]"
                                    class="text-sm text-destructive mt-0.5"
                                  >
                                    {{ form.errors[`account_unlink_after_success_commands.${index}.servers`] }}
                                  </p>
                                </div>

                                <div class="flex-1 flex col-span-2 space-x-4">
                                  <XSwitch
                                    :id="`unlink.is_player_online_required_${index}`"
                                    v-model="command.config.is_player_online_required"
                                    :label="__('Require player to be online')"
                                    :help="__('This command should only run if player is online on running server. If not online it will be queued to run when player comes online.')"
                                    :name="`unlink.is_player_online_required_${index}`"
                                    :error="form.errors[`account_unlink_after_success_commands.${index}.config.is_player_online_required`]"
                                  />

                                  <XSwitch
                                    :id="`unlink.is_run_only_first_unlink_${index}`"
                                    v-model="command.config.is_run_only_first_unlink"
                                    :name="`unlink.is_run_only_first_unlink_${index}`"
                                    :label="__('Run on first unlink only')"
                                    :help="__('This command should only run if given player is getting unlinked for first time. (Eg: This wont run if player was linked and unlinked before)')"
                                    :error="form.errors[`account_unlink_after_success_commands.${index}.config.is_run_only_first_unlink`]"
                                  />
                                </div>
                              </div>
                            </div>
                          </div>

                          <div
                            v-if="form.account_unlink_after_success_commands.length <= 0"
                            class="text-muted-foreground text-sm italic text-center pt-2"
                          >
                            {{ __("No commands on account unlink.") }}
                          </div>

                          <p
                            v-if="form.errors.account_unlink_after_success_commands"
                            class="text-sm text-destructive text-center"
                          >
                            {{ form.errors.account_unlink_after_success_commands }}
                          </p>

                          <div class="flex justify-center pt-2">
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              @click="addAccountUnlinkCommand"
                            >
                              {{ __("Add New Unlink Command") }}
                            </Button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Player Rank Sync -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="grid grid-cols-6 gap-4">
                      <div class="col-span-6">
                        <XSwitch
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
                        class="col-span-6"
                      >
                        <XSelect
                          id="sync_player_ranks_from_server_id"
                          v-model="form.sync_player_ranks_from_server_id"
                          name="sync_player_ranks_from_server_id"
                          :error="form.errors.sync_player_ranks_from_server_id"
                          :label="__('Rank Sync From Server')"
                          :placeholder="__('Choose a server from which we should sync the rank..')"
                          :disable-null="false"
                          :select-list="serversForRankSync"
                        />
                      </div>
                    </div>
                  </fieldset>
                </div>

                <!-- Player Password Reset -->
                <div class="col-span-6">
                  <fieldset>
                    <div class="space-y-6">
                      <div class="grid grid-cols-6 gap-4">
                        <div class="col-span-6 sm:col-span-3">
                          <XSwitch
                            id="enable_player_password_reset"
                            v-model="form.enable_player_password_reset"
                            :label="__('Enable Player Password Reset')"
                            :help="__('Enable this to allow user to reset their linked players password from website.')"
                            name="enable_player_password_reset"
                            :error="form.errors.enable_player_password_reset"
                          />
                        </div>

                        <div
                          v-show="form.enable_player_password_reset"
                          class="col-span-6 sm:col-span-3"
                        >
                          <XInput
                            id="player_password_reset_cooldown_in_seconds"
                            v-model="form.player_password_reset_cooldown_in_seconds"
                            :label="__('Password Reset Cooldown (in seconds)')"
                            :help="__('Number of seconds player have to wait before resetting password again.')"
                            type="number"
                            name="player_password_reset_cooldown_in_seconds"
                            :error="form.errors.player_password_reset_cooldown_in_seconds"
                            help-error-flex="flex-col"
                          />
                        </div>
                      </div>

                      <!-- Player Password Reset Commands -->
                      <div
                        v-show="form.enable_player_password_reset"
                        class="space-y-3"
                      >
                        <div class="flex items-center justify-between">
                          <legend class="text-sm text-foreground font-semibold">
                            {{ __("Player Password Reset Commands") }}
                            <span class="text-xs font-light text-muted-foreground">{{ __("(run when user try to reset his linked player password)") }}</span>
                          </legend>
                        </div>

                        <div class="bg-muted/30 rounded-lg p-4 space-y-4">
                          <div class="flex flex-col items-end">
                            <h3 class="text-foreground text-sm font-semibold">
                              {{ __("Available Placeholders") }}
                            </h3>
                            <ol class="text-sm text-right text-muted-foreground">
                              <li>
                                <code class="text-sm bg-muted p-1 rounded-md">{PLAYER_USERNAME}</code> - {{ __("Username of the player whose password is being reset.") }}
                              </li>
                              <li>
                                <code class="text-sm bg-muted p-1 rounded-md">{PLAYER_UUID}</code> - {{ __("Unique Id of the player whose password is being reset.") }}
                              </li>
                              <li>
                                <code class="text-sm bg-muted p-1 rounded-md">{PASSWORD}</code> - {{ __("New validated password entered by user.") }}
                              </li>
                            </ol>
                          </div>

                          <div class="flex space-x-4">
                            <div class="w-5" />
                            <label class="flex-1 block text-sm font-medium text-foreground">
                              {{ __("Command") }}
                            </label>
                            <label class="flex-1 block text-sm font-medium text-foreground">
                              {{ __("Run on servers") }}
                            </label>
                          </div>

                          <div
                            v-for="(command, index) in form.player_password_reset_commands"
                            :key="index"
                            class="flex flex-col pb-2"
                          >
                            <div class="flex space-x-4">
                              <button
                                type="button"
                                class="focus:outline-none group"
                                @click="removePlayerPasswordResetCommand(index)"
                              >
                                <Icon
                                  class="w-5 h-5 text-muted-foreground group-hover:text-destructive"
                                  name="trash"
                                />
                              </button>

                              <div class="grid grid-cols-2 gap-2 w-full">
                                <div class="flex-1">
                                  <XInput
                                    :id="`password_command_${index}`"
                                    v-model="command.command"
                                    :label="null"
                                    :placeholder="__('Enter Command') + ` #${index + 1}`"
                                    type="text"
                                    :error="form.errors[`player_password_reset_commands.${index}.command`]"
                                    input-class="h-[2.65rem]"
                                  />
                                </div>
                                <div class="flex-1">
                                  <Multiselect
                                    v-model="command.servers"
                                    :options="serversWithWebquery"
                                    :custom-label="serversWithWebqueryCustomLabel"
                                    track-by="id"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :clear-on-select="false"
                                    :searchable="false"
                                    :placeholder="__('Leave empty to run on all servers') + '...'"
                                  />
                                  <p
                                    v-if="form.errors[`player_password_reset_commands.${index}.servers`]"
                                    class="text-sm text-destructive mt-0.5"
                                  >
                                    {{ form.errors[`player_password_reset_commands.${index}.servers`] }}
                                  </p>
                                </div>

                                <div class="flex-1 flex col-span-2">
                                  <XSwitch
                                    :id="`password.is_player_online_required_${index}`"
                                    v-model="command.config.is_player_online_required"
                                    :label="__('Require player to be online')"
                                    :help="__('This command should only run if player is online on running server. If not online it will be queued to run when player comes online.')"
                                    :name="`password.is_player_online_required_${index}`"
                                    :error="form.errors[`player_password_reset_commands.${index}.config.is_player_online_required`]"
                                  />
                                </div>
                              </div>
                            </div>
                          </div>

                          <div
                            v-if="form.errors.player_password_reset_commands && form.player_password_reset_commands.length <= 0"
                            class="text-destructive text-sm italic text-center pt-2"
                          >
                            {{ form.errors.player_password_reset_commands }}
                          </div>

                          <div
                            v-if="form.player_password_reset_commands.length <= 0"
                            class="text-muted-foreground text-sm italic text-center pt-2"
                          >
                            {{ __("No commands added. Please add at least one command, as required by your authentication plugin, for password reset functionality.") }}
                            <br>
                            {{ __("Eg: nlogin changepass {PLAYER_USERNAME} {PASSWORD}") }}
                          </div>

                          <div class="flex justify-center pt-2">
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              @click="addPlayerPasswordResetCommand"
                            >
                              {{ __("Add Password Reset Command") }}
                            </Button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                type="submit"
                :disabled="form.processing"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin -ml-1 mr-2 h-4 w-4"
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
                {{ __("Save Plugin Settings") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
