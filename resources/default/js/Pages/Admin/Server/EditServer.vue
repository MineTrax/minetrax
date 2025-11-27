<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';

const { __ } = useTranslations();

const props = defineProps({
    versionsArray: Array,
    server: {
        type: Object,
        default: null
    }
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Servers'),
        url: route('admin.server.index'),
        current: false,
    },
    {
        text: __('Edit Server'),
        current: true,
    }
];

const typeArray = {
    '0': 'Paper',
    '1': 'Spigot',
    '2': 'Forge',
    '3': 'Bukkit',
    '4': 'Vanilla',
};

const form = useForm({
    name: props.server.name,
    join_port: props.server.join_port,
    query_port: props.server.query_port,
    webquery_port: props.server.webquery_port,
    minecraft_version: props.server.minecraft_version,
    type: String(props.server.type),
    hostname: props.server.hostname,
    ip_address: props.server.ip_address,
    is_server_intel_enabled: props.server.is_server_intel_enabled,
    is_player_intel_enabled: props.server.is_player_intel_enabled,
    is_ingame_chat_enabled: props.server.is_ingame_chat_enabled,
    settings: {
        server_identifier: props.server?.settings?.server_identifier ?? null,
        is_skin_change_via_web_allowed: props.server?.settings?.is_skin_change_via_web_allowed ?? false,
        is_banwarden_enabled: props.server?.settings?.is_banwarden_enabled ?? false,
    },
    order: props.server.order,
});

function updateServer() {
    form.put(route('admin.server.update', props.server.id), {
        preserveScroll: false
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Server: :name', { name: server.name })" />

    <div class="px-10 py-8 mx-auto max-w-5xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateServer">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Server Name')"
                    :help="__('Publicly visible name of the server (e.g., Survival, Skyblock, Lifesteal, Practice, etc.).')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="server_identifier"
                    v-model="form.settings.server_identifier"
                    :label="__('Server Identifier')"
                    :help="__('Unique identifier for the server, used for identification in proxy configurations and ban management plugins (e.g., s1, server1, lifesteal, survival1, proxy, etc.).')"
                    :error="form.errors['settings.server_identifier']"
                    type="text"
                    name="server_identifier"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="hostname"
                    v-model="form.hostname"
                    :label="__('Hostname')"
                    :error="form.errors.hostname"
                    :help="__('Publicly visible join address of the server. Eg: play.example.com')"
                    type="text"
                    name="hostname"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="order"
                    v-model="form.order"
                    :label="__('Weight')"
                    :error="form.errors.order"
                    :help="__('Higher the weight, higher the priority. Eg: 1,3,10 etc. Can be left empty.')"
                    type="number"
                    name="order"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="ip_address"
                    v-model="form.ip_address"
                    :label="__('IP Address')"
                    :error="form.errors.ip_address"
                    autocomplete="ip_address"
                    type="text"
                    name="ip_address"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="join_port"
                    v-model="form.join_port"
                    :label="__('Join Port')"
                    :error="form.errors.join_port"
                    type="text"
                    name="join_port"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="query_port"
                    v-model="form.query_port"
                    :label="__('Query Port')"
                    :error="form.errors.query_port"
                    type="text"
                    name="query_port"
                  />
                </div>

                <div class="col-span-6 sm:col-span-6">
                  <div class="grid grid-cols-2 gap-6">
                    <XInput
                      id="webquery_port"
                      v-model="form.webquery_port"
                      :label="__('Webquery Port')"
                      :error="form.errors.webquery_port"
                      type="text"
                      name="webquery_port"
                    />
                    <div class="text-xs text-muted-foreground flex items-center">
                      {{ __("WebQuery port is a new port which MineTrax plugin will open for secure connection between server and web. Enter a port value which is available and can be open. Eg: 25569") }}
                    </div>
                  </div>
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XSelect
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
                  <XSelect
                    id="minecraft_version"
                    v-model="form.minecraft_version"
                    name="minecraft_version"
                    :error="form.errors.minecraft_version"
                    :label="__('Version')"
                    :select-list="versionsArray"
                  />
                </div>

                <div class="col-span-6 sm:col-span-6 space-y-4">
                  <XSwitch
                    id="is_server_intel_enabled"
                    v-model="form.is_server_intel_enabled"
                    :label="__('Enable Server Intel / Analytics')"
                    :help="__('If enabled, server analytics data (performance metric, join activity etc) will be captured for this server via plugin.')"
                    name="is_server_intel_enabled"
                  />

                  <XSwitch
                    id="is_player_intel_enabled"
                    v-model="form.is_player_intel_enabled"
                    :label="__('Enable Player Intel / Analytics')"
                    :help="__('If enabled, player intel & statistics data will be captured for this server via plugin.')"
                    name="is_player_intel_enabled"
                  />

                  <XSwitch
                    id="is_ingame_chat_enabled"
                    v-model="form.is_ingame_chat_enabled"
                    :label="__('Enable In-Game Chat')"
                    :help="__('Enable in-game chat for this server, which allow users to view & chat to in-game players from website.')"
                    name="is_ingame_chat_enabled"
                  />

                  <XSwitch
                    id="is_skin_change_via_web_allowed"
                    v-model="form.settings.is_skin_change_via_web_allowed"
                    :label="__('Enable Skin Change via Web (SkinsRestorer)')"
                    :help="__('Allow user to change their linked players skin via web for this server. This will require SkinsRestorer plugin to be installed on the server.')"
                    name="is_skin_change_via_web_allowed"
                  />

                  <XSwitch
                    id="is_banwarden_enabled"
                    v-model="form.settings.is_banwarden_enabled"
                    :label="__('Enable BanWarden')"
                    :help="__('BanWarden allows you to manage all your punishments (bans, mutes, kicks etc) from the web. This requires a ban plugin to be installed on the server Eg: LiteBans, LibertyBans etc.')"
                    name="is_banwarden_enabled"
                  />
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.server.index')">
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
                {{ __("Update Server") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
