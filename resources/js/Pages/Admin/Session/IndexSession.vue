<template>
  <app-layout>
    <app-head :title="__('Active User Sessions')" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-400">
          {{ __("Active Sessions") }}
        </h1>
      </div>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 dark:border-none dark:text-gray-600 sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-cool-gray-800 text-gray-500 dark:text-gray-200">
                  <tr>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Country") }}
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("User") }}
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase whitespace-nowrap tracking-wider"
                    >
                      {{ __("IP Address") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Device") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Platform") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Browser") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      {{ __("Last Activity") }}
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-cool-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr
                    v-for="session in sessions.data"
                    :key="session.id"
                  >
                    <td class="px-3 py-4 whitespace-nowrap">
                      <div class="flex items-center justify-center">
                        <div
                          v-tippy
                          class="flex-shrink-0 h-10 w-10 focus:outline-none"
                          :content="session.country.name"
                        >
                          <img
                            class="h-10 w-10"
                            :src="session.country.photo_path"
                            alt=""
                          >
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal">
                      <inertia-link
                        v-if="session.user"
                        :href="route('user.public.get', session.user.username)"
                        class="flex items-center"
                      >
                        <div class="flex-shrink-0 h-10 w-10 mr-2">
                          <img
                            class="h-10 w-10 rounded-full"
                            :src="session.user.profile_photo_url"
                            alt="Avatar"
                          >
                        </div>
                        <div class="flex-col">
                          <div
                            class="text-sm font-semibold text-gray-900 dark:text-gray-300 whitespace-nowrap truncate"
                            :style="[session.user.roles[0].color ? {color: session.user.roles[0].color} : null]"
                          >
                            {{ session.user.name }}
                          </div>
                          <div class="text-sm text-gray-500">
                            @{{ session.user.username }}
                          </div>
                        </div>
                      </inertia-link>
                      <div
                        v-else
                        class="flex items-center italic text-sm text-gray-500 dark:text-gray-400"
                      >
                        {{ __("Anonymous") }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          <a
                            class="hover:underline"
                            target="_blank"
                            :href="`https://whois.domaintools.com/${session.ip_address}`"
                          >{{ session.ip_address }}</a>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ session.agent.device }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ session.agent.platform }} {{ session.agent.platform_version }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ session.agent.browser }} {{ session.agent.browser_version }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ session.last_active }}
                        </div>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="sessions.data.length === 0">
                    <td
                      class="border-t px-6 py-4 text-center dark:text-gray-400"
                      colspan="7"
                    >
                      {{ __("No active sessions found.") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="sessions.links" />
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';

export default {

    components: {
        AppLayout,
        Pagination,
    },
    props: {
        sessions: Object
    },

    data() {
        return {
            customPageBeingDeleted: null
        };
    },
};
</script>
