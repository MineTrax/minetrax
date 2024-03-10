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

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          {{ isCreateOperation ? __('Add Bungee Server') : __('Edit Bungee Server: :name', {name: server.name}) }}
        </h1>
        <inertia-link
          :href="route('admin.server.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150"
        >
          <span>{{ __("Cancel") }}</span>
        </inertia-link>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
              <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-400">
                {{ __("Overview") }}
              </h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-500">
                {{ __("Minetrax only support adding one bungee server. This server will be used to show online players and server status. All sensitive information will be encrypted.") }}
                <br><br>
                {{ __("Please note Proxy servers don't need Minetrax.jar plugin. Only install them on actual servers like spigot, bukkit etc.") }}
              </p>
            </div>
          </div>
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
                        :help="__('Eg: My Bungee Server')"
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

                    <div class="col-span-6 sm:col-span-3">
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

                    <div class="col-span-6 sm:col-span-3">
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

                    <!-- <div class="col-span-6 sm:col-span-2">
                      <x-input
                        id="webquery_port"
                        v-model="form.webquery_port"
                        :label="__('Webquery Port')"
                        :error="form.errors.webquery_port"
                        autocomplete="webquery_port"
                        type="text"
                        name="webquery_port"
                        :help="__('Eg: 25585')"
                        help-error-flex="flex-col"
                      />
                    </div> -->

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
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-cool-gray-800 sm:px-6 flex justify-end">
                  <loading-button
                    :loading="form.processing"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-blue-500 hover:bg-light-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 disabled:opacity-50"
                    type="submit"
                  >
                    {{ isCreateOperation ? __("Add Bungee Server") : __("Edit Bungee Server") }}
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
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: {
        AdminLayout,
        XSelect,
        LoadingButton,
        XInput
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
                hostname: this.server?.hostname
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
