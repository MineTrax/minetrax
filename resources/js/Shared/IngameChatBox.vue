<template>
  <div v-if="enabled">
    <div class="p-3 bg-white rounded shadow sm:px-5 dark:bg-cool-gray-800">
      <div class="flex justify-between">
        <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
          {{ __("Server In-Game Chat") }}
        </h3>

        <!-- Server Selector Dropdown-->
        <select
          v-if="serverList && serverList.length > 1"
          id="serverSelector"
          v-model="serverId"
          aria-label="serverSelector"
          name="serverSelector"
          class="text-xs border-gray-300 rounded-md shadow-sm focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
        >
          <option
            v-for="server in serverList"
            :key="server.id"
            :value="server.id"
          >
            {{ server.name }}
          </option>
        </select>
      </div>

      <div class="flex flex-col">
        <!--Loading-->
        <div
          v-if="loading"
          class="space-y-2"
        >
          <div class="w-full">
            <div class="flex space-x-4 animate-pulse">
              <div class="flex-1 py-1 space-y-1">
                <div class="w-3/4 h-4 bg-gray-300 rounded dark:bg-cool-gray-700" />
                <div class="w-5/6 h-4 bg-gray-300 rounded dark:bg-cool-gray-700" />
              </div>
            </div>
          </div>
          <div class="w-full">
            <div class="flex space-x-4 animate-pulse">
              <div class="flex-1 py-1 space-y-1">
                <div class="w-3/4 h-4 bg-gray-300 rounded dark:bg-cool-gray-700" />
                <div class="w-5/6 h-4 bg-gray-300 rounded dark:bg-cool-gray-700" />
              </div>
            </div>
          </div>
          <div class="w-full">
            <div class="flex space-x-4 animate-pulse">
              <div class="flex-1 py-1 space-y-1">
                <div class="w-3/4 h-4 bg-gray-300 rounded dark:bg-cool-gray-700" />
                <div class="w-5/6 h-4 bg-gray-300 rounded dark:bg-cool-gray-700" />
              </div>
            </div>
          </div>
        </div>

        <!-- Server Chat View Section -->
        <div
          v-show="!loading"
          id="chat-container"
          class="relative min-h-[5rem] flex flex-col-reverse justify-between p-1 mt-1 text-white bg-gray-200 rounded md:flex-row dark:bg-cool-gray-900"
        >
          <button
            v-show="!shouldDisplayPlayerList"
            class="absolute top-0 right-0 mt-1 mr-2 font-semibold text-green-400 z-10"
            type="button"
            @click="shouldDisplayPlayerList = !shouldDisplayPlayerList"
          >
            [+]
          </button>

          <div
            id="chatbox"
            class="flex flex-col overflow-auto text-sm max-h-96 hide-scrollbar invert dark:invert-0"
          >
            <p
              v-for="chat in chatLogs"
              :key="chat.id"
              v-tippy
              :title="formatToDayDateString(chat.created_at)"
              class="focus:outline-none"
              v-html="chat.data"
            />

            <div
              v-if="!chatLogs || chatLogs.length <= 0"
              class="flex items-center justify-center w-full h-full text-sm italic text-gray-500"
            >
              {{ __("No chat recorded yet!") }}
            </div>
          </div>

          <div
            v-show="!playersListLoading && shouldDisplayPlayerList"
            id="player-list"
            class="sticky flex justify-end overflow-auto text-sm bg-white bg-opacity-100 rounded dark:bg-cool-gray-800 max-h-96 min-w-max hide-scrollbar"
          >
            <div class="flex flex-col w-full space-y-1">
              <div class="relative flex items-center justify-center p-2 bg-gray-100 dark:bg-opacity-25 dark:bg-cool-gray-600">
                <h3 class="ml-4 mr-5 font-bold text-gray-700 dark:text-gray-200">
                  {{ __("Players") }}&nbsp;({{ playersList.length }})
                </h3>
                <button
                  class="absolute right-0 mr-2 font-semibold text-red-500 dark:text-red-400 z-10"
                  type="button"
                  @click="shouldDisplayPlayerList = !shouldDisplayPlayerList"
                >
                  [-]
                </button>
              </div>

              <div
                v-if="!playersList || playersList.length <= 0"
                class="text-sm text-center italic text-gray-500 dark:text-gray-400 pb-2 pt-1"
              >
                {{ __("No players.") }}
              </div>

              <div
                v-for="player in playersList"
                :key="player.id"
                class="flex justify-between mx-1"
              >
                <div class="flex space-x-1 truncate">
                  <img
                    class="h-5"
                    :src="route('player.avatar.get',{uuid: player.id, username: player.username, size:50})"
                    alt="Player Avatar"
                  >
                  <inertia-link
                    v-if="player.is_in_db"
                    :href="route('player.show', player.id)"
                  >
                    <span
                      class="mr-1 font-semibold truncate text-gray-800 dark:text-white"
                      :class="{ 'text-orange-500 dark:text-yellow-300': player.is_op }"
                    >{{ player.username }}</span>
                  </inertia-link>
                  <span
                    v-else
                    class="mr-1 truncate text-gray-800 dark:text-white"
                    :class="{ 'text-orange-500 dark:text-yellow-300': player.is_op }"
                  >
                    {{ player.username }}
                  </span>
                </div>
                <div
                  id="right"
                  class="flex items-center justify-center space-x-1"
                >
                  <img
                    v-tippy
                    class="h-5"
                    :title="player.country.name"
                    :src="player.country.photo_path"
                    :alt="player.country.name"
                  >
                  <a
                    v-if="$page.props.auth.user && $page.props.auth.user.is_staff"
                    class="text-gray-400 cursor-pointer hover:text-gray-200"
                    href="#"
                    @click.prevent="openAdminPlayerActionModel(player)"
                  >
                    <icon
                      name="cog"
                      class="w-4 h-4"
                    />
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Server chat inbox box -->
        <div
          v-if="$page.props.auth.user"
          class="mt-1"
        >
          <form @submit.prevent="postSendMessage">
            <input
              v-if="!loading"
              ref="inputbox"
              v-model="message"
              :disabled="sending || !isWebQuerySuccess"
              aria-label="Shout"
              class="block w-full mt-1 bg-gray-100 border-none rounded-md focus:ring-gray-300 dark:focus:ring-gray-700 sm:text-sm disabled:opacity-50 dark:bg-cool-gray-900 dark:text-gray-200 focus:bg-white dark:focus:bg-gray-900"
              type="text"
              :placeholder="isWebQuerySuccess ? __('Say something..'): __('Server webquery is offline')"
            >
            <span
              v-if="error"
              class="text-xs text-red-400"
            >{{ error }}</span>
            <span
              v-if="!loading && can('send server_custom_commands')"
              class="flex justify-end mt-2 text-xs text-gray-500 dark:text-gray-400"
            >{{ __("Start with / to send a console command") }}</span>
          </form>
        </div>
        <div
          v-else
          class="mt-2 text-sm text-center text-gray-600 dark:text-gray-400"
        >
          <inertia-link
            class="font-semibold text-light-blue-500"
            :href="route('login')"
          >
            {{ __("Login") }}
          </inertia-link>
          <template v-if="$page.props.hasRegistrationFeature">
            {{ " " + __("or") }}
            <inertia-link
              class="font-semibold text-light-blue-500"
              :href="route('register')"
            >
              {{ __("Register") }}
            </inertia-link>
          </template>
          {{ __("to chat with In-Game Players") }}
        </div>
      </div>
    </div>

    <jet-dialog-modal
      :show="showAdminPlayerActionModel"
      @close="closeAdminPlayerActionModel"
    >
      <template #title>
        <div
          v-if="actionModelCurrentPlayer"
          class="flex flex-col items-center font-bold"
        >
          <span class="text-gray-800 underline">{{ __("Manage Player") }}</span>
          <img
            class="h-24 rounded"
            :src="route('player.avatar.get',{uuid: actionModelCurrentPlayer.id, username: actionModelCurrentPlayer.username})"
            alt="Player Avatar"
          >
          <span class="text-light-blue-600">{{ actionModelCurrentPlayer.username }}</span>
          <span class="text-xs text-gray-600">{{ actionModelCurrentPlayer.id }}</span>
        </div>
      </template>

      <template #content>
        <div class="flex justify-center space-x-2">
          <loading-button
            v-if="can('kill players')"
            :loading="adminPlayerActionLoading"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-gray-600 border border-transparent rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50"
            type="button"
            @click="sendCommandToServer('kill')"
          >
            {{ __("Kill") }}
          </loading-button>
          <loading-button
            v-if="can('mute players')"
            :loading="adminPlayerActionLoading"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-yellow-400 border border-transparent rounded-md shadow-sm hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-200 disabled:opacity-50"
            type="button"
            @click="sendCommandToServer('mute')"
          >
            {{ __("Mute") }} / {{ __("UnMute") }}
          </loading-button>
          <loading-button
            v-if="can('kick players')"
            :loading="adminPlayerActionLoading"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50"
            type="button"
            @click="sendCommandToServer('kick')"
          >
            {{ __("Kick") }}
          </loading-button>
          <loading-button
            v-if="can('ban players')"
            :loading="adminPlayerActionLoading"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-pink-600 border border-transparent rounded-md shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 disabled:opacity-50"
            type="button"
            @click="sendCommandToServer('ban')"
          >
            {{ __("Ban") }}
          </loading-button>
        </div>

        <div
          v-if="adminPlayerActionError"
          class="flex justify-center p-1 mt-2 text-sm text-red-500 bg-red-100 border border-red-500"
        >
          {{ adminPlayerActionError }}
        </div>
      </template>

      <template #footer>
        <jet-secondary-button @click="closeAdminPlayerActionModel">
          {{ __("Cancel") }}
        </jet-secondary-button>
      </template>
    </jet-dialog-modal>
  </div>
</template>

<script>

import Icon from '@/Components/Icon.vue';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import {format} from 'date-fns';
import {USE_WEBSOCKETS} from '@/constants';
import {useAuthorizable} from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';

export default {
    components: {Icon, JetDialogModal, JetSecondaryButton, LoadingButton},
    props: {
        defaultServerId: Number,
        serverList: Array
    },

    setup() {
        const {can} = useAuthorizable();
        const {formatToDayDateString} = useHelpers();
        return {can, formatToDayDateString};
    },

    data() {
        return {
            serverId: this.defaultServerId,
            chatLogs: [],
            message: '',
            loading: true,
            error: null,
            sending: false,
            playersList: [],
            playersListLoading: true,
            shouldDisplayPlayerList: true,
            playerListQueryInterval: null,
            showAdminPlayerActionModel: false,
            actionModelCurrentPlayer: null,
            adminPlayerActionLoading: false,
            adminPlayerActionError: null,
            format: format,
            isWebQuerySuccess: false,
            chatListQueryInterval: null,
        };
    },

    computed: {
        enabled() {
            if (!this.$page.props.generalSettings.enable_ingamechat) return false;

            return !!(this.defaultServerId);
        }
    },

    watch: {
        serverId: function (newId, oldId) {
            if (USE_WEBSOCKETS) {
                Echo.leaveChannel('chatlogs.' + oldId);
            } else {
                clearInterval(this.chatListQueryInterval);
            }
            this.serverId = newId;
            this.getChatListForServer(newId);

            clearInterval(this.playerListQueryInterval);
            this.getPlayerListForServer(newId);
            this.playerListQueryInterval = setInterval(() => this.getPlayerListForServer(newId), 10000);
        }
    },

    created() {
        if (!this.enabled) return;

        this.getChatListForServer(this.serverId);

        this.getPlayerListForServer(this.serverId);
        this.playerListQueryInterval = setInterval(() => this.getPlayerListForServer(this.serverId), 10000);
    },

    unmounted() {
        if (USE_WEBSOCKETS) {
            Echo.leave('chatlogs.' + this.serverId);
        } else {
            clearInterval(this.chatListQueryInterval);
        }
        clearInterval(this.playerListQueryInterval);
    },

    methods: {
        postSendMessage() {
            this.sending = true;
            this.error = null;
            axios.post(route('chatlog.send', this.serverId), {
                message: this.message
            }).then(() => {
                // console.log(data)
            }).catch(e => {
                if (e.response.status === 422)
                    this.error = Object.values(e.response.data.errors)[0][0];
                else if (e.response.status === 403)
                    this.error = e.response.data.message;
                else
                    this.error = e.response.statusText;
            }).finally(() => {
                this.message = '';
                this.sending = false;
                this.$nextTick(() => {
                    this.$refs.inputbox.focus();
                    this.pollServerForNewChat(this.serverId);
                });
            });
        },

        getChatListForServer(sId) {
            this.loading = true;
            this.chatLogs = [];
            axios.get(route('chatlog.index', sId)).then(data => {
                this.chatLogs = data.data.reverse();
            }).finally(() => {
                this.loading = false;
                this.$nextTick(() => {
                    let scrollHeight = this.$el.querySelector('#chatbox').scrollHeight;
                    this.$el.querySelector('#chatbox').scrollTo(0, scrollHeight);
                });
            });

            if (USE_WEBSOCKETS) {
                Echo.channel('chatlogs.' + sId).listen('ServerChatlogCreated', data => {
                    this.chatLogs.push(data.data);

                    this.$nextTick(() => {
                        let scrollHeight = this.$el.querySelector('#chatbox').scrollHeight;
                        this.$el.querySelector('#chatbox').scrollTo(0, scrollHeight);
                    });
                });
            } else {
                this.chatListQueryInterval = setInterval(() => this.pollServerForNewChat(this.serverId), 6000);
            }
        },

        pollServerForNewChat(sId) {
            if (USE_WEBSOCKETS) return;

            let afterId = this.chatLogs.length > 0 ? this.chatLogs[this.chatLogs.length - 1].id : 0;
            axios.get(route('chatlog.index', {server: sId, after: afterId})).then(data => {
                const newChat = data.data.reverse();
                if (newChat.length > 0) {
                    this.chatLogs = this.chatLogs.concat(newChat);
                    this.$nextTick(() => {
                        let scrollHeight = this.$el.querySelector('#chatbox').scrollHeight;
                        this.$el.querySelector('#chatbox').scrollTo(0, scrollHeight);
                    });
                }
            });
        },

        getPlayerListForServer(sId) {
            axios.get(route('server.webquery.get', sId)).then(data => {
                this.playersList = data.data;
                this.isWebQuerySuccess = true;
            }).catch(() => {
                this.shouldDisplayPlayerList = false;
                this.isWebQuerySuccess = false;
            }).finally(() => {
                this.playersListLoading = false;
            });
        },

        openAdminPlayerActionModel(player) {
            this.showAdminPlayerActionModel = true;
            this.actionModelCurrentPlayer = player;
        },

        closeAdminPlayerActionModel() {
            this.showAdminPlayerActionModel = false;
            this.actionModelCurrentPlayer = null;
        },

        sendCommandToServer(type) {
            this.adminPlayerActionLoading = true;
            let reason = null;
            let formJson = {
                type: null,
                params: null,
                context: null
            };

            switch (type) {
            case 'kill':
                formJson.type = 'kill';
                formJson.params = this.actionModelCurrentPlayer.username;
                formJson.context = 'player';
                break;
            case 'mute':
                reason = prompt(this.__('Give any reason if muting? Note: ALWAYS LEAVE IT BLANK IF UN-MUTING'));
                if (reason === null) break;
                formJson.type = 'mute';
                if (reason) {
                    formJson.params = this.actionModelCurrentPlayer.username + ' ' + reason;
                } else {
                    formJson.params = this.actionModelCurrentPlayer.username;
                }
                formJson.context = 'player';
                break;
            case 'kick':
                reason = prompt(this.__('Any reason for kicking this player?'));
                if (reason === null) break;
                formJson.type = 'kick';
                formJson.params = this.actionModelCurrentPlayer.username + ' ' + reason;
                formJson.context = 'player';
                break;
            case 'ban':
                reason = prompt(this.__('Any reason for banning this player?'));
                if (reason === null) break;
                formJson.type = 'ban';
                formJson.params = this.actionModelCurrentPlayer.username + ' ' + reason;
                formJson.context = 'player';
                break;
            case 'broadcast':
                break;
            case 'custom':
                break;
            default:
                break;
            }

            if (formJson.type === null) {
                this.adminPlayerActionLoading = false;
                return;
            }
            axios.post(route('admin.server.command', this.serverId), formJson).then(() => {
                this.closeAdminPlayerActionModel();
            }).catch(e => {
                this.adminPlayerActionError = e.message;
            }).finally(() => {
                this.adminPlayerActionLoading = false;
            });
        }
    }
};
</script>
