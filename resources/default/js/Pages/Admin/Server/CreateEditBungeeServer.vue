<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XSwitch from '@/Components/Form/XSwitch.vue';
import { computed } from 'vue';

const { __ } = useTranslations();

const props = defineProps({
    server: {
        type: [Object],
        required: false
    },
    versionsArray: Array,
});

const isCreateOperation = computed(() => !props.server);

const breadcrumbItems = computed(() => [
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
        text: isCreateOperation.value ? __('Add Proxy Server') : __('Edit Proxy Server'),
        current: true,
    }
]);

const form = useForm({
    name: props.server?.name,
    ip_address: props.server?.ip_address,
    join_port: props.server?.join_port,
    query_port: props.server?.query_port,
    webquery_port: props.server?.webquery_port,
    minecraft_version: props.server?.minecraft_version,
    hostname: props.server?.hostname,
    is_server_intel_enabled: props.server?.is_server_intel_enabled ?? true,
    settings: {
        server_identifier: props.server?.settings?.server_identifier ?? null,
        is_skin_change_via_web_allowed: props.server?.settings?.is_skin_change_via_web_allowed ?? true,
        is_banwarden_enabled: !props.server ? true : props.server?.settings?.is_banwarden_enabled ?? false,
    },
    order: props.server?.order,
});

function postForm() {
    if (isCreateOperation.value) {
        form.post(route('admin.server-bungee.store'), {
            preserveScroll: true
        });
    } else {
        form.put(route('admin.server.update.bungee', props.server.id), {
            preserveScroll: true
        });
    }
}
</script>

<template>
  <AdminLayout>
    <app-head
      v-if="isCreateOperation"
      :title="__('Add Bungee Server')"
    />
    <app-head
      v-else
      :title="__('Edit Bungee Server: :name', {name: server.name})"
    />

    <div class="px-10 py-8 mx-auto max-w-5xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="postForm">
          <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Server Name')"
                    :error="form.errors.name"
                    autocomplete="name"
                    type="text"
                    name="name"
                    :help="__('Publicly visible name of the server (e.g., My Bungee Server etc.).')"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="server_identifier"
                    v-model="form.settings.server_identifier"
                    :label="__('Server Identifier')"
                    :help="__('Unique identifier for the server, used for identification in proxy configurations and ban management plugins (e.g., proxy, bungee, network1, etc.).')"
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
                    autocomplete="hostname"
                    type="text"
                    name="hostname"
                    :help="__('Eg: play-my-bungee-server.com')"
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
                    :help="__('Eg: 78.46.130.197')"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="join_port"
                    v-model="form.join_port"
                    :label="__('Join Port')"
                    :error="form.errors.join_port"
                    autocomplete="join_port"
                    type="text"
                    name="join_port"
                    :help="__('Eg: 25565')"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="query_port"
                    v-model="form.query_port"
                    :label="__('Query Port')"
                    :error="form.errors.query_port"
                    autocomplete="query_port"
                    type="text"
                    name="query_port"
                    :help="__('Eg: 25575')"
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
                    id="minecraft_version"
                    v-model="form.minecraft_version"
                    name="minecraft_version"
                    :error="form.errors.minecraft_version"
                    :label="__('Server Version')"
                    :select-list="versionsArray"
                    :placeholder="__('Select version..')"
                    :disable-null="true"
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
                {{ isCreateOperation ? __("Add Server") : __("Update Server") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
