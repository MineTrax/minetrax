<template>
  <div v-if="enabled">
    <div class="p-3 sm:px-5 bg-white dark:bg-cool-gray-800 rounded shadow">
      <div class="flex justify-between">
        <h3 class="font-extrabold text-gray-800 dark:text-gray-200">
          Server In-Game Chat
        </h3>

        <!-- Server Selector Dropdown-->
        <select
          v-if="serverList && serverList.length > 1"
          id="serverSelector"
          v-model="serverId"
          aria-label="serverSelector"
          name="serverSelector"
          class="text-xs border-gray-300 focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50 rounded-md shadow-sm"
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
            <div class="animate-pulse flex space-x-4">
              <div class="flex-1 space-y-1 py-1">
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-3/4" />
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-5/6" />
              </div>
            </div>
          </div>
          <div class="w-full">
            <div class="animate-pulse flex space-x-4">
              <div class="flex-1 space-y-1 py-1">
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-3/4" />
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-5/6" />
              </div>
            </div>
          </div>
          <div class="w-full">
            <div class="animate-pulse flex space-x-4">
              <div class="flex-1 space-y-1 py-1">
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-3/4" />
                <div class="h-4 bg-gray-300 dark:bg-cool-gray-700 rounded w-5/6" />
              </div>
            </div>
          </div>
        </div>

        <!-- Server Chat View Section -->
        <div
          v-show="!loading"
          id="chat-container"
          class="flex-col-reverse md:flex-row flex bg-cool-gray-900 dark:bg-cool-gray-900 rounded mt-1 p-1 text-white relative justify-between"
        >
          <button
            v-show="!shouldDisplayPlayerList"
            class="text-green-400 font-semibold absolute mt-1 mr-2 right-0 top-0"
            type="button"
            @click="shouldDisplayPlayerList = !shouldDisplayPlayerList"
          >
            [+]
          </button>

          <div
            id="chatbox"
            class="flex  flex-col max-h-96 overflow-auto hide-scrollbar text-sm"
          >
            <p
              v-for="chat in chatLogs"
              :key="chat.id"
              v-tippy
              :title="format(new Date(chat.created_at), 'E, do MMM yyyy, h:mm aaa')"
              class="focus:outline-none"
              v-html="chat.data"
            />

            <div
              v-if="!chatLogs || chatLogs.length <= 0"
              class="flex italic text-sm w-full h-full items-center justify-center text-gray-500"
            >
              No chat recorded yet!
            </div>
          </div>

          <div
            v-show="!playersListLoading && shouldDisplayPlayerList"
            id="player-list"
            class="text-sm sticky flex justify-end bg-cool-gray-800 bg-opacity-100 rounded max-h-96 min-w-max overflow-auto hide-scrollbar"
          >
            <div class="flex flex-col w-full space-y-1">
              <div class="flex bg-cool-gray-600 bg-opacity-25 p-2 items-center justify-center relative">
                <h3 class="font-bold mr-5 ml-4">
                  Players ({{ playersList.length }})
                </h3>
                <button
                  class="text-red-400 font-semibold absolute mr-2 right-0"
                  type="button"
                  @click="shouldDisplayPlayerList = !shouldDisplayPlayerList"
                >
                  [-]
                </button>
              </div>

              <div
                v-for="player in playersList"
                class="flex justify-between mx-1"
              >
                <div class="flex space-x-1 truncate">
                  <img
                    class="h-5"
                    :src="route('player.avatar.get',{uuid: player.id, size:50})"
                    alt="Player Avatar"
                  >
                  <inertia-link
                    v-if="player.is_in_db"
                    :href="route('player.show', player.id)"
                  >
                    <span
                      class="truncate font-semibold mr-1"
                      :class="{ 'text-yellow-300': player.is_op }"
                    >{{ player.username }}</span>
                  </inertia-link>
                  <span
                    v-else
                    class="truncate mr-1"
                    :class="{ 'text-yellow-300': player.is_op }"
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
                    v-if="$page.props.user && $page.props.user.is_staff"
                    class="cursor-pointer text-gray-400 hover:text-gray-200"
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
          v-if="$page.props.user"
          class="mt-1"
        >
          <form @submit.prevent="postSendMessage">
            <input
              v-if="!loading"
              ref="inputbox"
              v-model="message"
              :disabled="sending"
              aria-label="Shout"
              class="mt-1 focus:ring-gray-300 dark:focus:ring-gray-700 block w-full sm:text-sm rounded-md border-none disabled:opacity-50 bg-gray-100 dark:bg-cool-gray-900 dark:text-gray-200 focus:bg-white"
              type="text"
              placeholder="Say something.."
            >
            <span
              v-if="error"
              class="text-red-400 text-xs"
            >{{ error }}</span>
            <span
              v-if="!loading && can('send server_custom_commands')"
              class="flex mt-2 justify-end text-gray-500 dark:text-gray-400 text-xs"
            >Start with / to send a console command</span>
          </form>
        </div>
        <div
          v-else
          class="text-sm text-gray-600 dark:text-gray-400 text-center mt-2"
        >
          <inertia-link
            class="font-semibold text-light-blue-500"
            :href="route('login')"
          >
            Login
          </inertia-link>
          or
          <inertia-link
            class="font-semibold text-light-blue-500"
            :href="route('register')"
          >
            Register
          </inertia-link>
          to chat with In-Game Players
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
          <span class="underline text-gray-800">Manage Player</span>
          <img
            class="h-24 rounded"
            :src="route('player.avatar.get',{uuid: actionModelCurrentPlayer.id})"
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
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50"
            type="button"
            @click.native="sendCommandToServer('kill')"
          >
            Kill
          </loading-button>
          <loading-button
            v-if="can('mute players')"
            :loading="adminPlayerActionLoading"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-400 hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-200 disabled:opacity-50"
            type="button"
            @click.native="sendCommandToServer('mute')"
          >
            Mute / Unmute
          </loading-button>
          <loading-button
            v-if="can('kick players')"
            :loading="adminPlayerActionLoading"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50"
            type="button"
            @click.native="sendCommandToServer('kick')"
          >
            Kick
          </loading-button>
          <loading-button
            v-if="can('ban players')"
            :loading="adminPlayerActionLoading"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 disabled:opacity-50"
            type="button"
            @click.native="sendCommandToServer('ban')"
          >
            Ban
          </loading-button>
        </div>

        <div
          v-if="adminPlayerActionError"
          class="flex text-red-500 border border-red-500 bg-red-100 p-1 mt-2 justify-center text-sm"
        >
          {{ adminPlayerActionError }}
        </div>
      </template>

      <template #footer>
        <jet-secondary-button @click.native="closeAdminPlayerActionModel">
          Cancel
        </jet-secondary-button>
      </template>
    </jet-dialog-modal>
  </div>
</template>

<script>

import Icon from '@/Components/Icon';
import JetDialogModal from '@/Jetstream/DialogModal';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import LoadingButton from '@/Components/LoadingButton';
import {formatDistanceToNowStrict, format} from 'date-fns';

export default {
    components: {Icon, JetDialogModal, JetSecondaryButton, LoadingButton},
    props: {
        defaultServerId: Number,
        serverList: Array
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
            Echo.leaveChannel('chatlogs.' + oldId);
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

    destroyed() {
        Echo.leave('chatlogs.' + this.serverId);
        clearInterval(this.playerListQueryInterval);
    },

    methods: {
        postSendMessage() {
            this.sending = true;
            this.error = null;
            axios.post(route('chatlog.send', this.serverId), {
                message: this.message
            }).then(data => {
                // console.log(data)
            }).catch(e => {
                if (e.response.status === 422)
                    this.error = Object.values(e.response.data.errors)[0][0];
                else if (e.response.status === 403)
                    this.error = e.response.data.message;
                else
                    this.error = e.response.statusText;
            }).finally(e => {
                this.message = '';
                this.sending = false;
                this.$nextTick(() => {
                    this.$refs.inputbox.focus();
                });
            });
        },

        getChatListForServer(sId) {
            this.loading = true;
            this.chatLogs = [];
            axios.get(route('chatlog.index', sId)).then(data => {
                this.chatLogs = data.data.reverse();
            }).finally(e => {
                this.loading = false;
                this.$nextTick(() => {
                    let scrollHeight = this.$el.querySelector('#chatbox').scrollHeight;
                    this.$el.querySelector('#chatbox').scrollTo(0, scrollHeight);
                });
            });

            Echo.channel('chatlogs.' + sId).listen('ServerChatlogCreated', data => {
                this.chatLogs.push(data.data);

                this.$nextTick(() => {
                    let scrollHeight = this.$el.querySelector('#chatbox').scrollHeight;
                    this.$el.querySelector('#chatbox').scrollTo(0, scrollHeight);
                });
            });
        },

        getPlayerListForServer(sId) {
            axios.get(route('server.webquery.get', sId)).then(data => {
                this.playersList = data.data;
            }).catch(e => {
                this.shouldDisplayPlayerList = false;
            }).finally(e => {
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
                reason = prompt('Give any reason if muting? Note: ALWAYS LEAVE IT BLANK IF UN-MUTING');
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
                reason = prompt('Any reason for kicking this player?');
                if (reason === null) break;
                formJson.type = 'kick';
                formJson.params = this.actionModelCurrentPlayer.username + ' ' + reason;
                formJson.context = 'player';
                break;
            case 'ban':
                reason = prompt('Any reason for banning this player?');
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
            axios.post(route('admin.server.command', this.serverId), formJson).then(data => {
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
