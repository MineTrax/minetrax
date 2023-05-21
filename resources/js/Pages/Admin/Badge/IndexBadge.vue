<template>
  <app-layout>
    <app-head :title="__('Badges Administration')" />

    <div class="px-10 py-12 mx-auto max-w-7xl">
      <div class="flex justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-300">
          {{ __("User Badges") }}
        </h1>
        <div class="flex">
          <inertia-link
            v-if="can('create badges')"
            :href="route('admin.badge.create')"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray"
          >
            <span>{{ __("Create") }}</span>
            <span class="hidden md:inline">&nbsp;{{ __("Badge") }}</span>
          </inertia-link>
        </div>
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
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Name") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Sort Order") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Sticky") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Users") }}
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase"
                    >
                      {{ __("Created") }}
                    </th>
                    <th
                      scope="col"
                      class="relative px-6 py-3"
                    >
                      <span class="sr-only">{{ __("Edit") }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-cool-gray-800 dark:divide-gray-700">
                  <tr
                    v-for="(badge, index) in badges.data"
                    :key="index"
                  >
                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                      {{ (index)+badges.from }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10">
                          <img
                            class="w-10 h-10"
                            :src="badge.photo_url"
                            alt=""
                          >
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                            {{ badge.name }}
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ badge.shortname }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900 dark:text-gray-300">
                        {{ badge.sort_order ?? __('none') }}
                      </div>
                    </td>
                    <td class="py-4 text-sm text-center text-gray-500 align-middle px-9 whitespace-nowrap">
                      <icon
                        v-if="badge.is_sticky"
                        class="text-green-500"
                        name="check-circle"
                      />
                      <icon
                        v-else
                        class="text-red-500"
                        name="cross-circle"
                      />
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 align-middle whitespace-nowrap dark:text-gray-400">
                      {{ badge.users_count }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                      {{ formatTimeAgoToNow(badge.created_at) }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap dark:text-gray-400">
                      <inertia-link
                        v-if="can('update badges')"
                        as="a"
                        :href="route('admin.badge.edit', badge.id)"
                        class="text-yellow-600 hover:text-yellow-900"
                      >
                        {{ __("Edit") }}
                      </inertia-link>
                      /
                      <inertia-link
                        v-if="can('delete badges')"
                        v-confirm="{message: 'Are you sure you want to delete this Badge?'}"
                        as="button"
                        method="DELETE"
                        :href="route('admin.badge.delete', badge.id)"
                        class="text-red-600 hover:text-red-900 focus:outline-none"
                      >
                        {{ __("Delete") }}
                      </inertia-link>
                    </td>
                  </tr>

                  <tr v-if="badges.data.length === 0">
                    <td
                      class="px-6 py-4 text-center border-t dark:border-gray-700 dark:text-gray-300"
                      colspan="7"
                    >
                      {{ __("No badges found.") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <pagination :links="badges.links" />
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Icon from '@/Components/Icon.vue';
import {useForm} from '@inertiajs/vue3';

export default {
    components: {
        AppLayout,
        Pagination,
        Icon
    },
    props: {
        badges: Object
    },

    data() {
        return {
            deleteBadgeForm: useForm({}),
        };
    },
};
</script>
