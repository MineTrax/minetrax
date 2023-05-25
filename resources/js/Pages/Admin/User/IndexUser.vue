<template>
  <AdminLayout>
    <app-head
      :title="__('Users Administration')"
    />

    <div class="px-10 py-12 mx-auto max-w-7xl">
      <div class="flex justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("Users") }}
        </h1>
      </div>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow dark:border-none sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="text-gray-500 bg-gray-50 dark:bg-cool-gray-800 dark:text-gray-300">
                  <tr>
                    <th
                      scope="col"
                      class="px-3 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("#") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Flag") }}
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-3 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Name / @Username") }}
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-3 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Role") }}
                    </th>
                    <th
                      scope="col"
                      class="w-6 px-3 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Email") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("DoB") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Joined At") }}
                    </th>
                    <th
                      scope="col"
                      class="px-3 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Flags") }}
                    </th>
                    <th
                      scope="col"
                      class="relative px-3 py-3"
                    >
                      <span class="sr-only">{{ __("Actions") }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-cool-gray-800 dark:divide-gray-700">
                  <tr
                    v-for="user in users.data"
                    :key="user.id"
                  >
                    <td class="px-3 py-4 text-sm font-semibold text-gray-800 whitespace-nowrap dark:text-gray-400">
                      {{ user.id }}
                    </td>
                    <td class="">
                      <img
                        v-tippy
                        class="w-10 h-10 mx-auto"
                        :src="user.country.photo_path"
                        alt="Avatar"
                        :title="user.country.name"
                      >
                    </td>
                    <td class="w-1/2 px-3 py-4 whitespace-normal">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 mr-2">
                          <img
                            class="w-10 h-10 rounded-full"
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
                              <user-displayname
                                :user="user"
                                icon-class="w-4 h-4"
                                text-class="text-sm"
                              />
                            </inertia-link>
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            @{{ user.username }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="w-1/2 px-3 py-4 whitespace-normal">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ user.roles[0].display_name }}
                        </div>
                      </div>
                    </td>
                    <td class="w-1/2 px-3 py-4 whitespace-normal">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          {{ user.email }}
                        </div>
                      </div>
                    </td>
                    <td class="w-1/2 px-3 py-4">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                          <span v-if="user.dob">{{ user.dob_string_with_year }}</span>
                          <span
                            v-else
                            class="italic text-gray-500 dark:text-gray-400"
                          >{{ __("None") }}</span>
                        </div>
                      </div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                      <span
                        v-tippy
                        class="focus:outline-none"
                        :content="formatToDayDateString(user.created_at)"
                      >
                        {{ formatTimeAgoToNow(user.created_at) }}
                      </span>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                      <span
                        v-if="user.muted_at"
                        class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-orange-100 text-orange-800 dark:bg-orange-700 dark:bg-opacity-25 dark:text-orange-400"
                      >{{ __("Muted") }}</span>
                      <span
                        v-if="user.banned_at"
                        class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 dark:bg-red-700 dark:bg-opacity-25 dark:text-red-400"
                      >{{ __("Banned") }}</span>

                      <span
                        v-if="!user.muted_at && !user.banned_at"
                        class="text-xs italic"
                      >{{ __("None") }}</span>
                    </td>
                    <td class="px-3 py-4 text-sm font-medium text-right whitespace-nowrap dark:text-gray-300">
                      <inertia-link
                        as="a"
                        :href="route('user.public.get', user.username)"
                        class="text-blue-500 hover:text-blue-800"
                      >
                        {{ __("View") }}
                      </inertia-link>
                      /
                      <inertia-link
                        as="a"
                        :href="route('admin.impersonate.take', user.id)"
                        class="text-orange-500 hover:text-orange-800"
                      >
                        {{ __("Impersonate") }}
                      </inertia-link>
                      /
                      <inertia-link
                        v-if="can('update users')"
                        as="a"
                        :href="route('admin.user.edit', user.id)"
                        class="text-yellow-500 hover:text-yellow-800"
                      >
                        {{ __("Edit") }}
                      </inertia-link>
                      /
                      <inertia-link
                        v-if="can('delete users')"
                        v-confirm="{message: 'Are you sure you want to delete this User permanently?'}"
                        as="button"
                        method="DELETE"
                        :href="route('admin.user.delete', user.id)"
                        class="text-red-600 hover:text-red-900 focus:outline-none"
                      >
                        {{ __("Delete") }}
                      </inertia-link>
                    </td>
                  </tr>

                  <tr v-if="users.data.length === 0">
                    <td
                      class="px-3 py-4 text-center border-t"
                      colspan="7"
                    >
                      {{ __("No users found.") }}
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
  </AdminLayout>
</template>

<script>
import Pagination from '@/Components/Pagination.vue';
import UserDisplayname from '@/Components/UserDisplayname.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {useAuthorizable} from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';

export default {

    components: {
        AdminLayout,
        Pagination,
        UserDisplayname
    },
    props: {
        users: Object
    },
    setup() {
        const {can} = useAuthorizable();
        const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {can, formatTimeAgoToNow, formatToDayDateString};
    },
};
</script>
