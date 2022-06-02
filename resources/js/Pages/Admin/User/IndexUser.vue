<template>
  <app-layout>
    <app-head title="Users Administration" />

    <div class="py-12 px-10 max-w-7xl mx-auto">
      <div class="flex justify-between mb-8">
        <h1 class="font-bold text-3xl text-gray-500 dark:text-gray-300">
          Users
        </h1>
      </div>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 dark:border-none sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-cool-gray-800 text-gray-500 dark:text-gray-300">
                  <tr>
                    <th
                      scope="col"
                      class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      #
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Name / @Username
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Role
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Email
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      DoB
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Joined At
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Muted
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Banned
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Verified
                    </th>
                    <th
                      scope="col"
                      class="relative px-6 py-3"
                    >
                      <span class="sr-only">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-cool-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr
                    v-for="user in users.data"
                    :key="user.id"
                  >
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-400 font-semibold">
                      {{ user.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-normal w-1/2">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 mr-2">
                          <img
                            class="h-10 w-10 rounded-full"
                            :src="user.profile_photo_url"
                            alt="Avatar"
                          >
                        </div>
                        <div class="flex-col">
                          <div
                            class="text-sm font-semibold text-gray-900 dark:text-gray-300"
                            :style="[user.roles[0].color ? {color: user.roles[0].color} : null]"
                          >
                            <inertia-link :href="route('user.public.get', user.username)">
                              {{ user.name }}
                            </inertia-link>
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            @{{ user.username }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal w-1/2">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ user.roles[0].display_name }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-normal w-1/2">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ user.email }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 w-1/2">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          <span v-if="user.dob">{{ user.dob_string_with_year }}</span>
                          <span
                            v-else
                            class="italic text-gray-500 dark:text-gray-400"
                          >None</span>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <span
                        v-tippy
                        class="focus:outline-none"
                        :content="formatDate(new Date(user.created_at), 'E, do MMM yyyy, h:mm aaa')"
                      >
                        {{ formatDistanceToNowStrict(new Date(user.created_at), { addSuffix: true }) }}
                      </span>
                    </td>
                    <td class="px-9 py-4 text-sm text-gray-500 dark:text-gray-400">
                      <icon
                        v-if="user.muted_at"
                        class="text-green-500 focus:outline-none"
                        name="check-circle"
                      />
                      <icon
                        v-else
                        class="text-red-500"
                        name="cross-circle"
                      />
                    </td>
                    <td class="px-9 py-4 whitespace-nowrap text-sm text-gray-500 align-middle text-center">
                      <icon
                        v-if="user.banned_at"
                        class="text-green-500"
                        name="check-circle"
                      />
                      <icon
                        v-else
                        class="text-red-500"
                        name="cross-circle"
                      />
                    </td>
                    <td class="px-9 py-4 whitespace-nowrap text-sm text-gray-500 align-middle text-center">
                      <icon
                        v-if="user.verified_at"
                        class="text-green-500"
                        name="check-circle"
                      />
                      <icon
                        v-else
                        class="text-red-500"
                        name="cross-circle"
                      />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium dark:text-gray-300">
                      <inertia-link
                        as="a"
                        :href="route('user.public.get', user.username)"
                        class="text-blue-500 hover:text-blue-800"
                      >
                        View
                      </inertia-link>
                      /
                      <inertia-link
                        as="a"
                        :href="route('admin.impersonate.take', user.id)"
                        class="text-orange-500 hover:text-orange-800"
                      >
                        Impersonate
                      </inertia-link>
                      /
                      <inertia-link
                        v-if="can('update users')"
                        as="a"
                        :href="route('admin.user.edit', user.id)"
                        class="text-yellow-500 hover:text-yellow-800"
                      >
                        Edit
                      </inertia-link>
                    </td>
                  </tr>

                  <tr v-if="users.data.length === 0">
                    <td
                      class="border-t px-6 py-4 text-center"
                      colspan="7"
                    >
                      No users found.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="users.links" />
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import JetSectionBorder from '@/Jetstream/SectionBorder';
import Pagination from '@/Components/Pagination';
import { formatDistanceToNowStrict, format } from 'date-fns';
import Icon from '@/Components/Icon';
import JetConfirmationModal from '@/Jetstream/ConfirmationModal';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import JetDangerButton from '@/Jetstream/DangerButton';

export default {

    components: {
        Icon,
        AppLayout,
        JetSectionBorder,
        Pagination,
        JetConfirmationModal,
        JetSecondaryButton,
        JetDangerButton
    },
    props: {
        users: Object
    },

    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict,
            formatDate: format,
            customPageBeingDeleted: null
        };
    },
};
</script>
