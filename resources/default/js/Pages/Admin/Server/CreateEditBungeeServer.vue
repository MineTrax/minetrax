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

    <div class="py-12 px-10 max-w-5xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ isCreateOperation ? __('Add Bungee/Velocity Server') : __('Edit Bungee/Velocity Server: :name', {name: server.name}) }}
        </h1>
        <inertia-link
          :href="route('admin.server.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="">
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="postForm">
              <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white dark:bg-cool-gray-800 sm:p-6">
                  <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="name"
                        v-model="form.name"
                        :label="__('Server Name')"
                        :error="form.errors.name"
                        autocomplete="name"
                        type="text"
                        name="name"
                        :help="__('Publicly visible name of the server (e.g., My Bungee Server etc.).')"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="server_identifier"
                        v-model="form.settings.server_identifier"
                        :label="__('Server Identifier')"
                        :help="__('Unique identifier for the server, used for identification in proxy configurations and ban management plugins (e.g., proxy, bungee, network1, etc.).')"
                        :error="form.errors['settings.server_identifier']"
                        type="text"
                        name="server_identifier"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="hostname"
                        v-model="form.hostname"
                        :label="__('Hostname')"
                        :error="form.errors.hostname"
                        autocomplete="hostname"
                        type="text"
                        name="hostname"
                        :help="__('Eg: play-my-bungee-server.com')"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-input
                        id="order"
                        v-model="form.order"
                        :label="__('Weight')"
                        :error="form.errors.order"
                        :help="__('Higher the weight, higher the priority. Eg: 1,3,10 etc. Can be left empty.')"
                        type="number"
                        name="order"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                      <x-input
                        id="ip_address"
                        v-model="form.ip_address"
                        :label="__('IP Address')"
                        :error="form.errors.ip_address"
                        autocomplete="ip_address"
                        type="text"
                        name="ip_address"
                        :help="__('Eg: 78.46.130.197')"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                      <x-input
                        id="join_port"
                        v-model="form.join_port"
                        :label="__('Join Port')"
                        :error="form.errors.join_port"
                        autocomplete="join_port"
                        type="text"
                        name="join_port"
                        :help="__('Eg: 25565')"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                      <x-input
                        id="query_port"
                        v-model="form.query_port"
                        :label="__('Query Port')"
                        :error="form.errors.query_port"
                        autocomplete="query_port"
                        type="text"
                        name="query_port"
                        :help="__('Eg: 25575')"
                        help-error-flex="flex-col"
                      />
                    </div>

                    <div class="col-span-6 sm:col-span-6">
                      <div class="grid grid-cols-2 gap-6">
                        <x-input
                          id="webquery_port"
                          v-model="form.webquery_port"
                          :label="__('Webquery Port')"
                          :error="form.errors.webquery_port"
                          type="text"
                          name="webquery_port"
                          help-error-flex="flex-col"
                        />
                        <div class="text-xs text-gray-400 flex items-center">
                          {{ __("WebQuery port is a new port which MineTrax plugin will open for secure connection between server and web. Enter a port value which is available and can be open. Eg: 25569") }}
                        </div>
                      </div>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-select
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


                    <div class="flex items-center col-span-6 sm:col-span-6">
                      <x-checkbox
                        id="is_server_intel_enabled"
                        v-model="form.is_server_intel_enabled"
                        :label="__('Enable Server Intel / Analytics')"
                        :help="__('If enabled, server analytics data (performance metric, join activity etc) will be captured for this server via plugin.')"
                        name="is_server_intel_enabled"
                      />
                    </div>


                    <div class="flex items-center col-span-6 sm:col-span-6">
                      <x-checkbox
                        id="is_skin_change_via_web_allowed"
                        v-model="form.settings.is_skin_change_via_web_allowed"
                        :label="__('Enable Skin Change via Web (SkinsRestorer)')"
                        :help="__('Allow user to change their linked players skin via web for this server. This will require SkinsRestorer plugin to be installed on the server.')"
                        name="is_skin_change_via_web_allowed"
                      />
                    </div>

                    <div class="flex items-center col-span-6 sm:col-span-6">
                      <x-checkbox
                        id="is_banwarden_enabled"
                        v-model="form.settings.is_banwarden_enabled"
                        :label="__('Enable BanWarden')"
                        :help="__('BanWarden allows you to manage all your punishments (bans, mutes, kicks etc) from the web. This requires a ban plugin to be installed on the server Eg: LiteBans, LibertyBans etc.')"
                        name="is_banwarden_enabled"
                      />
                    </div>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ isCreateOperation ? __("Add Server") : __("Edit Server") }}
                  </loading-button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import LoadingButton from '@/Components/LoadingButton.vue';
import XInput from '@/Components/Form/XInput.vue';
import XSelect from '@/Components/Form/XSelect.vue';
import XCheckbox from '@/Components/Form/XCheckbox.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XSelect,
        LoadingButton,
        XInput,
        XCheckbox,
    },
    props: {
        server: {
            type: [Object],
            required: false
        },
        versionsArray: Array,
    },
    data() {
        return {
            isCreateOperation: !this.server,
            form: useForm({
                name: this.server?.name,
                ip_address: this.server?.ip_address,
                join_port: this.server?.join_port,
                query_port: this.server?.query_port,
                webquery_port: this.server?.webquery_port,
                minecraft_version: this.server?.minecraft_version,
                hostname: this.server?.hostname,
                is_server_intel_enabled: this.server?.is_server_intel_enabled ?? true,
                settings: {
                    server_identifier: this.server?.settings?.server_identifier ?? null,
                    is_skin_change_via_web_allowed: this.server?.settings?.is_skin_change_via_web_allowed ?? true,
                    is_banwarden_enabled: !this.server ? true : this.server?.settings?.is_banwarden_enabled ?? false,
                },
                order: this.server?.order,
            }),
        };
    },

    methods: {
        postForm() {
            if (this.isCreateOperation) {
                this.form.post(route('admin.server-bungee.store'), {
                    preserveScroll: true
                });
            } else {
                this.form.put(route('admin.server.update.bungee', this.server.id), {
                    preserveScroll: true
                });
            }
        },
    }
};
</script>
