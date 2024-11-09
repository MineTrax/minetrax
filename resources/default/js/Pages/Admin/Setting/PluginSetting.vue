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
                            class="flex-col col-span-6 space-y-1 sm:col-span-6"
                          >
                            <legend class="text-sm text-gray-700 font-bold dark:font-semibold dark:text-gray-300">
                              {{ __("Account Link Success Commands") }}
                              <span class="text-xs font-light text-gray-400">{{ __("(runs when a player is linked to account)") }}</span>
                            </legend>

                            <div class="w-full space-y-2">
                              <div class="flex flex-col items-end">
                                <h3 class="text-gray-700 dark:text-gray-300 text-sm font-semibold">
                                  {{ __("Available Placeholders") }}
                                </h3>
                                <ol class="text-sm text-right text-gray-600 dark:text-gray-400">
                                  <li>
                                    <code class="text-sm bg-gray-100 dark:bg-cool-gray-700 dark:text-gray-300 p-1 rounded-md">{PLAYER_USERNAME}</code> - {{ __("Username of the player which is linked.") }}
                                  </li>
                                  <li>
                                    <code class="text-sm bg-gray-100 dark:bg-cool-gray-700 dark:text-gray-300 p-1 rounded-md">{PLAYER_UUID}</code> - {{ __("Unique Id of the player which is linked.") }}
                                  </li>
                                </ol>
                              </div>

                              <div class="flex space-x-4">
                                <div class="w-5" />
                                <label
                                  class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >{{
                                  __("Command") }}
                                </label>
                                <label
                                  class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >{{
                                  __("Run on servers") }}
                                </label>
                              </div>

                              <div
                                v-for="(
                                  command, index
                                ) in form.account_link_after_success_commands"
                                :key="index"
                                class="flex flex-col pb-2"
                              >
                                <div class="flex space-x-4">
                                  <button
                                    type="button"
                                    class="focus:outline-none group"
                                    @click="
                                      removeAccountLinkCommand(index)
                                    "
                                  >
                                    <Icon
                                      class="w-5 h-5 text-gray-300 group-hover:text-red-500"
                                      name="trash"
                                    />
                                  </button>

                                  <div class="grid grid-cols-2 gap-2 w-full">
                                    <div class="flex-1">
                                      <input
                                        v-model="command.command"
                                        class="block p-2.5 dark:bg-cool-gray-900 dark:text-gray-300 w-full dark:border-gray-700 border-gray-200 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                                        :placeholder="`Enter Command #${index + 1}`"
                                        type="text"
                                      >
                                      <span
                                        v-if="
                                          form.errors[
                                            `account_link_after_success_commands.${index}.command`
                                          ]
                                        "
                                        class="text-red-500 text-sm"
                                      >
                                        {{
                                          form.errors[
                                            `account_link_after_success_commands.${index}.command`
                                          ]
                                        }}
                                      </span>
                                    </div>
                                    <div class="flex-1">
                                      <Multiselect
                                        v-model="command.servers"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                                        :options="serversWithWebquery"
                                        :custom-label="serversWithWebqueryCustomLabel"
                                        track-by="id"
                                        :multiple="true"
                                        :close-on-select="false"
                                        :clear-on-select="false"
                                        :searchable="false"
                                        :placeholder="__('Leave empty to run on all servers')+'...'"
                                      />
                                      <p class="text-sm text-red-500 mt-0.5">
                                        {{
                                          form.errors[
                                            `account_link_after_success_commands.${index}.servers`
                                          ]
                                        }}
                                      </p>
                                    </div>

                                    <div class="flex-1 flex col-span-2 space-x-2">
                                      <x-checkbox
                                        :id="`link.is_player_online_required_${index}`"
                                        v-model="command.config.is_player_online_required"
                                        :label="__('Require player to be online')"
                                        :help="__('This command should only run if player is online on running server. If not online it will be queued to run when player comes online.')"
                                        :name="`link.is_player_online_required_${index}`"
                                        :error="form.errors[
                                          `account_link_after_success_commands.${index}.config.is_player_online_required`
                                        ]"
                                      />

                                      <x-checkbox
                                        :id="`link.is_run_only_first_link_${index}`"
                                        v-model="command.config.is_run_only_first_link"
                                        :name="`link.is_run_only_first_link_${index}`"
                                        :label="__('Run on first link only')"
                                        :help="__('This command should only run if given player is getting linked for first time. (Eg: This wont run if player get unlinked and then linked again)')"
                                        :error="form.errors[
                                          `account_link_after_success_commands.${index}.config.is_run_only_first_link`
                                        ]"
                                      />
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div
                                v-if="form.account_link_after_success_commands.length <= 0"
                                class="text-gray-500 text-sm italic text-center pt-2"
                              >
                                {{ __("No commands on account link.") }}
                              </div>

                              <p
                                v-if="form.errors.account_link_after_success_commands"
                                class="text-sm text-red-400 text-center"
                              >
                                {{ form.errors.account_link_after_success_commands }}
                              </p>

                              <div class="flex justify-center pt-5">
                                <button
                                  type="button"
                                  class="p-2 w-1/3 text-sm text-light-blue-500 rounded border border-light-blue-500 focus:outline-none hover:text-light-blue-300 hover:border-light-blue-300 transition ease-in-out duration-150"
                                  @click="addAccountLinkCommand"
                                >
                                  {{
                                    __("Add New Link Command")
                                  }}
                                </button>
                              </div>
                            </div>
                          </div>

                          <div
                            v-show="form.enable_account_link"
                            class="flex-col col-span-6 space-y-1 sm:col-span-6"
                          >
                            <legend class="text-sm text-gray-700 font-bold dark:font-semibold dark:text-gray-300">
                              {{ __("Account Unlink Success Commands") }}
                              <span class="text-xs font-light text-gray-400">{{ __("(runs when a player is unlinked from account)") }}</span>
                            </legend>

                            <div class="w-full space-y-2">
                              <div class="flex flex-col items-end">
                                <h3 class="text-gray-700 dark:text-gray-300 text-sm font-semibold">
                                  {{ __("Available Placeholders") }}
                                </h3>
                                <ol class="text-sm text-right text-gray-600 dark:text-gray-400">
                                  <li>
                                    <code class="text-sm bg-gray-100 dark:bg-cool-gray-700 dark:text-gray-300 p-1 rounded-md">{PLAYER_USERNAME}</code> - {{ __("Username of the player which is unlinked.") }}
                                  </li>
                                  <li>
                                    <code class="text-sm bg-gray-100 dark:bg-cool-gray-700 dark:text-gray-300 p-1 rounded-md">{PLAYER_UUID}</code> - {{ __("Unique Id of the player which is unlinked.") }}
                                  </li>
                                </ol>
                              </div>

                              <div class="flex space-x-4">
                                <div class="w-5" />
                                <label
                                  class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >{{
                                  __("Command") }}
                                </label>
                                <label
                                  class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >{{
                                  __("Run on servers") }}
                                </label>
                              </div>

                              <div
                                v-for="(
                                  command, index
                                ) in form.account_unlink_after_success_commands"
                                :key="index"
                                class="flex flex-col pb-2"
                              >
                                <div class="flex space-x-4">
                                  <button
                                    type="button"
                                    class="focus:outline-none group"
                                    @click="
                                      removeAccountUnlinkCommand(index)
                                    "
                                  >
                                    <Icon
                                      class="w-5 h-5 text-gray-300 group-hover:text-red-500"
                                      name="trash"
                                    />
                                  </button>

                                  <div class="grid grid-cols-2 gap-2 w-full">
                                    <div class="flex-1">
                                      <input
                                        v-model="command.command"
                                        class="block p-2.5 dark:bg-cool-gray-900 dark:text-gray-300 w-full dark:border-gray-700 border-gray-200 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                                        :placeholder="`Enter Command #${index + 1}`"
                                        type="text"
                                      >
                                      <span
                                        v-if="
                                          form.errors[
                                            `account_unlink_after_success_commands.${index}.command`
                                          ]
                                        "
                                        class="text-red-500 text-sm"
                                      >
                                        {{
                                          form.errors[
                                            `account_unlink_after_success_commands.${index}.command`
                                          ]
                                        }}
                                      </span>
                                    </div>
                                    <div class="flex-1">
                                      <Multiselect
                                        v-model="command.servers"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                                        :options="serversWithWebquery"
                                        :custom-label="serversWithWebqueryCustomLabel"
                                        track-by="id"
                                        :multiple="true"
                                        :close-on-select="false"
                                        :clear-on-select="false"
                                        :searchable="false"
                                        :placeholder="__('Leave empty to run on all servers')+'...'"
                                      />
                                      <p class="text-sm text-red-500 mt-0.5">
                                        {{
                                          form.errors[
                                            `account_unlink_after_success_commands.${index}.servers`
                                          ]
                                        }}
                                      </p>
                                    </div>

                                    <div class="flex-1 flex col-span-2 space-x-2">
                                      <x-checkbox
                                        :id="`unlink.is_player_online_required_${index}`"
                                        v-model="command.config.is_player_online_required"
                                        :label="__('Require player to be online')"
                                        :help="__('This command should only run if player is online on running server. If not online it will be queued to run when player comes online.')"
                                        :name="`unlink.is_player_online_required_${index}`"
                                        :error="form.errors[
                                          `account_unlink_after_success_commands.${index}.config.is_player_online_required`
                                        ]"
                                      />

                                      <x-checkbox
                                        :id="`unlink.is_run_only_first_unlink_${index}`"
                                        v-model="command.config.is_run_only_first_unlink"
                                        :name="`unlink.is_run_only_first_unlink_${index}`"
                                        :label="__('Run on first unlink only')"
                                        :help="__('This command should only run if given player is getting unlinked for first time. (Eg: This wont run if player was linked and unlinked before)')"
                                        :error="form.errors[
                                          `account_unlink_after_success_commands.${index}.config.is_run_only_first_unlink`
                                        ]"
                                      />
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div
                                v-if="form.account_unlink_after_success_commands.length <= 0"
                                class="text-gray-500 text-sm italic text-center pt-2"
                              >
                                {{ __("No commands on account unlink.") }}
                              </div>

                              <p
                                v-if="form.errors.account_unlink_after_success_commands"
                                class="text-sm text-red-400 text-center"
                              >
                                {{ form.errors.account_unlink_after_success_commands }}
                              </p>

                              <div class="flex justify-center pt-5">
                                <button
                                  type="button"
                                  class="p-2 w-1/3 text-sm text-light-blue-500 rounded border border-light-blue-500 focus:outline-none hover:text-light-blue-300 hover:border-light-blue-300 transition ease-in-out duration-150"
                                  @click="addAccountUnlinkCommand"
                                >
                                  {{
                                    __("Add New Unlink Command")
                                  }}
                                </button>
                              </div>
                            </div>
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
                              :select-list="serversForRankSync"
                            />
                          </div>


                          <div class="flex items-center col-span-6 sm:col-span-3">
                            <x-checkbox
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
                            <x-input
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

                          <div
                            v-show="form.enable_player_password_reset"
                            class="flex-col col-span-6 space-y-1 sm:col-span-6"
                          >
                            <legend class="text-sm text-gray-700 font-bold dark:font-semibold dark:text-gray-300">
                              {{ __("Player Password Reset Commands") }}
                              <span class="text-xs font-light text-gray-400">{{ __("(run when user try to reset his linked player password)") }}</span>
                            </legend>

                            <div class="w-full space-y-2">
                              <div class="flex flex-col items-end">
                                <h3 class="text-gray-700 dark:text-gray-300 text-sm font-semibold">
                                  {{ __("Available Placeholders") }}
                                </h3>
                                <ol class="text-sm text-right text-gray-600 dark:text-gray-400">
                                  <li>
                                    <code class="text-sm bg-gray-100 dark:bg-cool-gray-700 dark:text-gray-300 p-1 rounded-md">{PLAYER_USERNAME}</code> - {{ __("Username of the player whose password is being reset.") }}
                                  </li>
                                  <li>
                                    <code class="text-sm bg-gray-100 dark:bg-cool-gray-700 dark:text-gray-300 p-1 rounded-md">{PLAYER_UUID}</code> - {{ __("Unique Id of the player whose password is being reset.") }}
                                  </li>
                                  <li>
                                    <code class="text-sm bg-gray-100 dark:bg-cool-gray-700 dark:text-gray-300 p-1 rounded-md">{PASSWORD}</code> - {{ __("New validated password entered by user.") }}
                                  </li>
                                </ol>
                              </div>

                              <div class="flex space-x-4">
                                <div class="w-5" />
                                <label
                                  class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >{{
                                  __("Command") }}
                                </label>
                                <label
                                  class="flex-1 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                >{{
                                  __("Run on servers") }}
                                </label>
                              </div>

                              <div
                                v-for="(
                                  command, index
                                ) in form.player_password_reset_commands"
                                :key="index"
                                class="flex flex-col pb-2"
                              >
                                <div class="flex space-x-4">
                                  <button
                                    type="button"
                                    class="focus:outline-none group"
                                    @click="
                                      removePlayerPasswordResetCommand(index)
                                    "
                                  >
                                    <Icon
                                      class="w-5 h-5 text-gray-300 group-hover:text-red-500"
                                      name="trash"
                                    />
                                  </button>

                                  <div class="grid grid-cols-2 gap-2 w-full">
                                    <div class="flex-1">
                                      <input
                                        v-model="command.command"
                                        class="block p-2.5 dark:bg-cool-gray-900 dark:text-gray-300 w-full dark:border-gray-700 border-gray-200 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                                        :placeholder="`Enter Command #${index + 1}`"
                                        type="text"
                                      >
                                      <span
                                        v-if="
                                          form.errors[
                                            `player_password_reset_commands.${index}.command`
                                          ]
                                        "
                                        class="text-red-500 text-sm"
                                      >
                                        {{
                                          form.errors[
                                            `player_password_reset_commands.${index}.command`
                                          ]
                                        }}
                                      </span>
                                    </div>
                                    <div class="flex-1">
                                      <Multiselect
                                        v-model="command.servers"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm"
                                        :options="serversWithWebquery"
                                        :custom-label="serversWithWebqueryCustomLabel"
                                        track-by="id"
                                        :multiple="true"
                                        :close-on-select="false"
                                        :clear-on-select="false"
                                        :searchable="false"
                                        :placeholder="__('Leave empty to run on all servers')+'...'"
                                      />
                                      <p class="text-sm text-red-500 mt-0.5">
                                        {{
                                          form.errors[
                                            `player_password_reset_commands.${index}.servers`
                                          ]
                                        }}
                                      </p>
                                    </div>

                                    <div class="flex-1 flex col-span-2 space-x-2">
                                      <x-checkbox
                                        :id="`link.is_player_online_required_${index}`"
                                        v-model="command.config.is_player_online_required"
                                        :label="__('Require player to be online')"
                                        :help="__('This command should only run if player is online on running server. If not online it will be queued to run when player comes online.')"
                                        :name="`link.is_player_online_required_${index}`"
                                        :error="form.errors[
                                          `player_password_reset_commands.${index}.config.is_player_online_required`
                                        ]"
                                      />
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div
                                v-if="form.errors.player_password_reset_commands && form.player_password_reset_commands.length <= 0"
                                class="text-red-500 text-sm italic text-center pt-2"
                              >
                                {{ form.errors.player_password_reset_commands }}
                              </div>

                              <div
                                v-if="form.player_password_reset_commands.length <= 0"
                                class="text-gray-500 text-sm italic text-center pt-2"
                              >
                                {{ __("No commands added. Please add at least one command, as required by your authentication plugin, for password reset functionality.") }}
                                <br>
                                {{ __("Eg: nlogin changepass {PLAYER_UUID} {PASSWORD}") }}
                              </div>

                              <p
                                v-if="form.errors.account_link_after_success_commands"
                                class="text-sm text-red-400 text-center"
                              >
                                {{ form.errors.account_link_after_success_commands }}
                              </p>

                              <div class="flex justify-center pt-5">
                                <button
                                  type="button"
                                  class="p-2 w-1/3 text-sm text-light-blue-500 rounded border border-light-blue-500 focus:outline-none hover:text-light-blue-300 hover:border-light-blue-300 transition ease-in-out duration-150"
                                  @click="addPlayerPasswordResetCommand"
                                >
                                  {{
                                    __("Add Password Reset Command")
                                  }}
                                </button>
                              </div>
                            </div>
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

<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import CopyToClipboard from '@/Components/CopyToClipboard.vue';
import XInput from '@/Components/Form/XInput.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import { useTranslations } from '@/Composables/useTranslations';
import Icon from '@/Components/Icon.vue';
import Multiselect from 'vue-multiselect';
const { __ } = useTranslations();

// Props
const props = defineProps({
    settings: Object,
    settingsAccountLinkAfterSuccessCommands: Array,
    settingsAccountUnlinkAfterSuccessCommands: Array,
    settingsPlayerPasswordResetCommands: Array,
    serversForRankSync: Object,
    serversWithWebquery: Object,
});

// useForm setup
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

function serversWithWebqueryCustomLabel({name, hostname}) {
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

// Method to save plugin settings
const savePluginSetting = () => {
    form.post(route('admin.setting.plugin.update'), {
        preserveScroll: true,
        onSuccess: () => {
            this.$inertia.replace(route('admin.setting.plugin.show'), {
                preserveState: false,
                preserveScroll: true,
            });
        }
    });
};
</script>
