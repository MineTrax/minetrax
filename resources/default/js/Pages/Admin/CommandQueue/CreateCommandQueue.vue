<template>
  <AdminLayout>
    <app-head title="Run Command" />

    <div class="max-w-6xl px-10 py-12 mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="text-3xl font-bold text-secondary-500 dark:text-secondary-300">
          {{ __("Run Command") }}
        </h1>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="mt-5 md:mt-0 md:col-span-2">
          <form @submit.prevent="submitRunCommandForm">
            <div class="shadow sm:rounded-md">
              <div
                class="px-4 py-5 bg-white dark:bg-surface-800 sm:p-6"
              >
                <div class="grid grid-cols-6 gap-6">
                  <div class="col-span-6 sm:col-span-6">
                    <RadioGroup
                      v-model="form.scope"
                      class="flex justify-between gap-2"
                    >
                      <RadioGroupLabel
                        class="flex items-center flex-none w-1/12 font-semibold text-secondary-700 dark:text-secondary-300"
                      >
                        {{ __("Scope") }}
                      </RadioGroupLabel>
                      <RadioGroupOption
                        v-slot="{ checked }"
                        class="flex-1 text-sm text-center uppercase"
                        value="global"
                      >
                        <div
                          class="p-3 rounded shadow-sm cursor-pointer"
                          :class="
                            checked
                              ? 'dark:bg-surface-900 dark:text-secondary-200 font-bold bg-primary-400 text-white'
                              : 'dark:bg-surface-600 dark:text-secondary-300 bg-surface-200 text-secondary-700'
                          "
                        >
                          <div class="flex items-center justify-center">
                            <input
                              :checked="checked"
                              type="radio"
                              disabled
                              class="w-4 h-4 bg-surface-100 border-secondary-300 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-secondary-800 focus:ring-2 dark:bg-surface-700 dark:border-secondary-600"
                            >
                            <label
                              class="ms-2"
                            >{{ __("Global") }}
                            </label>
                          </div>
                          <div>
                            <span
                              :class="checked ? 'text-secondary-200 dark:text-secondary-400' : 'text-secondary-400'"
                              class="text-xs"
                            >
                              {{ __("Run generic command") }}
                            </span>
                          </div>
                        </div>
                      </RadioGroupOption>
                      <RadioGroupOption
                        v-slot="{ checked }"
                        class="flex-1 text-sm text-center uppercase"
                        value="player"
                      >
                        <div
                          class="p-3 rounded shadow-sm cursor-pointer"
                          :class="
                            checked
                              ? 'dark:bg-surface-900 dark:text-secondary-200 font-bold bg-primary-400 text-white'
                              : 'dark:bg-surface-600 dark:text-secondary-300 bg-surface-200 text-secondary-700'
                          "
                        >
                          <div class="flex items-center justify-center">
                            <input
                              :checked="checked"
                              type="radio"
                              disabled
                              class="w-4 h-4 bg-surface-100 border-secondary-300 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-secondary-800 focus:ring-2 dark:bg-surface-700 dark:border-secondary-600"
                            >
                            <label
                              class="ms-2"
                            >{{ __("Player") }}
                            </label>
                          </div>
                          <div>
                            <span
                              :class="checked ? 'text-secondary-200 dark:text-secondary-400' : 'text-secondary-400'"
                              class="text-xs"
                            >
                              {{ __("Run against players") }}
                            </span>
                          </div>
                        </div>
                      </RadioGroupOption>
                    </RadioGroup>
                  </div>

                  <div class="col-span-6 sm:col-span-6">
                    <p
                      class="mb-2 text-secondary-700 dark:text-secondary-300 font-semibold"
                    >
                      {{ __("Servers to run on") }}
                    </p>
                    <Multiselect
                      v-model="form.servers"
                      class="block w-full border-secondary-900 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                      :options="servers"
                      :custom-label="serversCustomLabel"
                      track-by="id"
                      :multiple="true"
                      :close-on-select="false"
                      :clear-on-select="false"
                      :searchable="false"
                      :placeholder="__('Leave empty to run on all servers')+'...'"
                    />
                    <p class="text-sm text-error-500 mt-0.5">
                      {{ form.errors.servers }}
                    </p>
                  </div>

                  <div class="col-span-6 sm:col-span-6">
                    <div
                      v-if="form.scope === 'player'"
                      class="flex flex-col items-end mb-2"
                    >
                      <h3 class="text-secondary-700 dark:text-secondary-300 text-sm font-semibold">
                        {{ __("Available Placeholders") }}
                      </h3>
                      <ol class="text-sm text-right text-secondary-600 dark:text-secondary-400">
                        <li>
                          <code class="text-sm bg-surface-100 dark:bg-surface-700 dark:text-secondary-300 p-1 rounded-md">{PLAYER_USERNAME}</code> - {{ __("Username of the player.") }}
                        </li>
                        <li>
                          <code class="text-sm bg-surface-100 dark:bg-surface-700 dark:text-secondary-300 p-1 rounded-md">{PLAYER_UUID}</code> - {{ __("Unique Id of the player.") }}
                        </li>
                      </ol>
                    </div>
                    <x-input
                      id="command"
                      v-model="form.command"
                      :label="
                        __('Enter command to run...')
                      "
                      :error="form.errors.command"
                      type="text"
                      name="command"
                      :help="form.scope == 'player' ? __('Eg: give {PLAYER_USERNAME} diamond 64') : __('Eg: broadcast Hello world!')"
                      help-error-flex="flex-row"
                    />
                  </div>

                  <div class="col-span-6 sm:col-span-6 relative">
                    <DatePicker
                      id="execute_at"
                      v-model:value="form.execute_at"
                      :placeholder="__('Leave empty to run immediately without any delay')+'...'"
                      class="w-full"
                      value-type="date"
                      type="datetime"
                      format="YYYY-MM-DD hh:mm:ss A"
                      input-class="border-secondary-300 h-14 p-3 text-sm pt-7 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 rounded-md block w-full dark:bg-surface-900 dark:text-secondary-300 dark:border-secondary-900"
                    />
                    <label
                      for="execute_at"
                      class="absolute -top-2.5 left-0 px-3 py-5 text-xs text-secondary-500 h-full pointer-events-none transform origin-left transition-all duration-100 ease-in-out"
                    >{{ __("Run at") }}</label>
                    <jet-input-error
                      :message="form.errors.execute_at"
                      class="mt-2"
                    />
                  </div>

                  <div
                    v-if="form.scope === 'player'"
                    class="col-span-6 sm:col-span-6"
                  >
                    <XSelect
                      id="players.scope"
                      v-model="form.players.scope"
                      :error="form.errors['players.scope']"
                      name="players.scope"
                      :label="__('Players to run on')"
                      :select-list="playerRunScopeList"
                    />
                  </div>

                  <div
                    v-if="form.players.scope === 'custom' && form.scope === 'player'"
                    class="col-span-6 sm:col-span-6"
                  >
                    <p
                      class="mb-2 text-secondary-700 dark:text-secondary-300 font-semibold"
                    >
                      {{ __("Select players") }}
                    </p>
                    <Multiselect
                      v-model="form.players.id"
                      class="block w-full border-secondary-900 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                      :options="players"
                      :custom-label="playersCustomLabel"
                      track-by="id"
                      :multiple="true"
                      :close-on-select="false"
                      :clear-on-select="false"
                      :searchable="true"
                      :hide-selected="true"
                      :loading="isLoadingPlayers"
                      :internal-search="false"
                      @search-change="searchPlayers"
                      :placeholder="__('Type to search players')+'...'"
                      :no-options="__('Type at least 2 characters to search')"
                    />
                    <p class="text-sm text-error-500 mt-0.5">
                      {{ form.errors['players.id'] }}
                    </p>
                  </div>

                  <div
                    v-if="form.scope === 'player'"
                    class="col-span-6 sm:col-span-6"
                  >
                    <XCheckbox
                      id="players.is_player_online_required"
                      v-model="form.players.is_player_online_required"
                      :label="__('Require player to be online')"
                      :help="__('This command should only run if player is online on running server. If not online it will be queued to run when player comes online.')"
                      name="players.is_player_online_required"
                      :error="form.errors['players.is_player_online_required']"
                    />
                  </div>

                  <!-- Form end -->
                </div>
              </div>
              <div
                class="flex justify-end px-4 py-3 bg-surface-50 dark:bg-surface-800 sm:px-6"
              >
                <loading-button
                  :loading="form.processing"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-success-500 hover:bg-success-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500 disabled:opacity-50"
                  type="submit"
                >
                  {{ form.execute_at ? __("Schedule Command") : __("Run Command") }}
                </loading-button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import LoadingButton from '@/Components/LoadingButton.vue';
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import { useForm } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import DatePicker from 'vue-datepicker-next';
import JetInputError from '@/Jetstream/InputError.vue';
import { useTranslations } from '@/Composables/useTranslations';
import axios from 'axios';
import { ref } from 'vue';
const { __ } = useTranslations();

defineProps({
    servers: {
        type: Array,
    },
});

const playerRunScopeList = {
    all: 'All - for every player in the web',
    linked: 'Linked Only - for every player who are linked to a user account',
    unlinked: 'Unlinked Only - for every player who are not linked to a user account',
    custom: 'Custom - select players from dropdown',
};

const players = ref([]);
const isLoadingPlayers = ref(false);
let searchTimeout = null;

const form = useForm({
    scope: 'global',
    command: '',
    execute_at: null,
    servers: [],
    players: {
        scope: 'all',
        is_player_online_required: false,
        id: [],
    }
});

function serversCustomLabel({name, hostname}) {
    return `${name} - ${hostname}`;
}

function playersCustomLabel({username, uuid}) {
    return `${username} - ${uuid}`;
}

const searchPlayers = (query) => {
    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    if (!query || query.length < 2) {
        players.value = [];
        return;
    }

    // Debounce the search to avoid too many API calls
    searchTimeout = setTimeout(async () => {
        isLoadingPlayers.value = true;

        try {
            const response = await axios.get('/stats', {
                params: {
                    'filter[q]': query
                }
            });

            players.value = response.data.data || response.data || [];
        } catch (error) {
            console.error('Error searching players:', error);
            players.value = [];
        } finally {
            isLoadingPlayers.value = false;
        }
    }, 300); // 300ms debounce
};

const submitRunCommandForm = () => {
    form.post(route('admin.command-queue.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('command', 'servers', 'players', 'execute_at');
        },
    });
};
</script>
