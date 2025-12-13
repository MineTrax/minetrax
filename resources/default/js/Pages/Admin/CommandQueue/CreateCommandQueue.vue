<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import { Tabs, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import Multiselect from 'vue-multiselect';
import XDatePicker from '@/Components/Form/XDatePicker.vue';
import axios from 'axios';
import { ref } from 'vue';
import { GlobeAltIcon, UserIcon } from '@heroicons/vue/24/outline';

const { __ } = useTranslations();

const props = defineProps({
    servers: {
        type: Array,
    },
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Commands'),
        url: route('admin.command-queue.index'),
        current: false,
    },
    {
        text: __('Run Command'),
        current: true,
    }
];

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

<template>
  <AdminLayout>
    <app-head :title="__('Run Command')" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="submitRunCommandForm">
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <!-- Scope Selection -->
                <div class="col-span-6">
                  <label class="block text-sm font-medium text-foreground mb-3">
                    {{ __("Scope") }}
                  </label>
                  <Tabs
                    v-model="form.scope"
                    class="w-full"
                  >
                    <TabsList class="grid w-full grid-cols-2 h-auto gap-3 bg-transparent p-0">
                      <TabsTrigger
                        value="global"
                        class="relative flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 data-[state=inactive]:border-border data-[state=inactive]:bg-muted/30 data-[state=inactive]:text-muted-foreground data-[state=inactive]:shadow-none hover:data-[state=inactive]:border-primary/50 hover:data-[state=inactive]:bg-muted/50 data-[state=active]:border-primary data-[state=active]:bg-primary/10 data-[state=active]:text-primary data-[state=active]:shadow-none [&>span]:w-full"
                      >
                        <div
                          class="flex items-center justify-center w-10 h-10 rounded-full transition-colors mx-auto"
                          :class="form.scope === 'global' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                        >
                          <GlobeAltIcon class="w-5 h-5" />
                        </div>
                        <div class="text-center">
                          <div class="font-semibold" :class="form.scope === 'global' ? 'text-primary' : 'text-foreground'">
                            {{ __("Global") }}
                          </div>
                          <div class="text-xs text-muted-foreground mt-0.5 whitespace-normal">
                            {{ __("Run generic command") }}
                          </div>
                        </div>
                        <div
                          v-if="form.scope === 'global'"
                          class="absolute top-2 right-2 w-2 h-2 rounded-full bg-primary"
                        />
                      </TabsTrigger>

                      <TabsTrigger
                        value="player"
                        class="relative flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 data-[state=inactive]:border-border data-[state=inactive]:bg-muted/30 data-[state=inactive]:text-muted-foreground data-[state=inactive]:shadow-none hover:data-[state=inactive]:border-primary/50 hover:data-[state=inactive]:bg-muted/50 data-[state=active]:border-primary data-[state=active]:bg-primary/10 data-[state=active]:text-primary data-[state=active]:shadow-none [&>span]:w-full"
                      >
                        <div
                          class="flex items-center justify-center w-10 h-10 rounded-full transition-colors mx-auto"
                          :class="form.scope === 'player' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                        >
                          <UserIcon class="w-5 h-5" />
                        </div>
                        <div class="text-center">
                          <div class="font-semibold" :class="form.scope === 'player' ? 'text-primary' : 'text-foreground'">
                            {{ __("Player") }}
                          </div>
                          <div class="text-xs text-muted-foreground mt-0.5 whitespace-normal">
                            {{ __("Run against players") }}
                          </div>
                        </div>
                        <div
                          v-if="form.scope === 'player'"
                          class="absolute top-2 right-2 w-2 h-2 rounded-full bg-primary"
                        />
                      </TabsTrigger>
                    </TabsList>
                  </Tabs>
                </div>

                <!-- Servers Selection -->
                <div class="col-span-6">
                  <label
                    for="servers"
                    class="block text-sm font-medium text-foreground mb-2"
                  >
                    {{ __("Servers to run on") }}
                  </label>
                  <Multiselect
                    id="servers"
                    v-model="form.servers"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :options="servers"
                    :custom-label="serversCustomLabel"
                    track-by="id"
                    :multiple="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :searchable="false"
                    :placeholder="__('Leave empty to run on all servers')+'...'"
                  />
                  <p
                    v-if="form.errors.servers"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors.servers }}
                  </p>
                </div>

                <!-- Command Input -->
                <div class="col-span-6">
                  <div
                    v-if="form.scope === 'player'"
                    class="flex flex-col items-end mb-3 p-3 bg-muted rounded-md"
                  >
                    <h3 class="text-sm font-semibold text-foreground">
                      {{ __("Available Placeholders") }}
                    </h3>
                    <ul class="text-sm text-right text-muted-foreground mt-1 space-y-1">
                      <li>
                        <code class="text-xs bg-background px-1.5 py-0.5 rounded font-mono">{PLAYER_USERNAME}</code>
                        <span class="ml-1">- {{ __("Username of the player.") }}</span>
                      </li>
                      <li>
                        <code class="text-xs bg-background px-1.5 py-0.5 rounded font-mono">{PLAYER_UUID}</code>
                        <span class="ml-1">- {{ __("Unique Id of the player.") }}</span>
                      </li>
                    </ul>
                  </div>
                  <XInput
                    id="command"
                    v-model="form.command"
                    :label="__('Command to run')"
                    :error="form.errors.command"
                    type="text"
                    name="command"
                    :placeholder="form.scope == 'player' ? __('Eg: give {PLAYER_USERNAME} diamond 64') : __('Eg: broadcast Hello world!')"
                  />
                </div>

                <!-- Execute At -->
                <div class="col-span-6">
                  <XDatePicker
                    id="execute_at"
                    v-model="form.execute_at"
                    :label="__('Run at')"
                    :placeholder="__('Leave empty to run immediately without any delay')+'...'"
                    :error="form.errors.execute_at"
                    type="datetime"
                    format="YYYY-MM-DD hh:mm:ss A"
                    value-type="date"
                  />
                </div>

                <!-- Player Scope Selection -->
                <div
                  v-if="form.scope === 'player'"
                  class="col-span-6"
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

                <!-- Custom Player Selection -->
                <div
                  v-if="form.players.scope === 'custom' && form.scope === 'player'"
                  class="col-span-6"
                >
                  <label
                    for="players_id"
                    class="block text-sm font-medium text-foreground mb-2"
                  >
                    {{ __("Select players") }}
                  </label>
                  <Multiselect
                    id="players_id"
                    v-model="form.players.id"
                    class="block w-full border-input rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
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
                    :placeholder="__('Type to search players')+'...'"
                    :no-options="__('Type at least 2 characters to search')"
                    @search-change="searchPlayers"
                  />
                  <p
                    v-if="form.errors['players.id']"
                    class="text-xs text-destructive mt-2"
                  >
                    {{ form.errors['players.id'] }}
                  </p>
                </div>

                <!-- Require Online -->
                <div
                  v-if="form.scope === 'player'"
                  class="col-span-6"
                >
                  <XSwitch
                    id="players.is_player_online_required"
                    v-model="form.players.is_player_online_required"
                    :label="__('Require player to be online')"
                    :help="__('This command should only run if player is online on running server. If not online it will be queued to run when player comes online.')"
                    name="players.is_player_online_required"
                    :error="form.errors['players.is_player_online_required']"
                  />
                </div>
              </div>
            </div>

            <!-- Form Footer -->
            <div class="px-4 py-4 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.command-queue.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
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
                {{ form.execute_at ? __("Schedule Command") : __("Run Command") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
